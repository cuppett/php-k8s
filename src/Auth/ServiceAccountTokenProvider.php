<?php

namespace RenokiCo\PhpK8s\Auth;

use RenokiCo\PhpK8s\Exceptions\AuthenticationException;
use RenokiCo\PhpK8s\KubernetesCluster;

class ServiceAccountTokenProvider extends TokenProvider
{
    protected KubernetesCluster $bootstrapCluster;

    protected string $namespace;

    protected string $serviceAccount;

    protected int $expirationSeconds = 3600; // 1 hour default

    protected array $audiences = [];

    protected ?string $bootstrapToken = null;

    public function __construct(
        KubernetesCluster $bootstrapCluster,
        string $namespace,
        string $serviceAccount
    ) {
        // Store the bootstrap cluster's current authentication
        // to prevent infinite recursion when this provider is set on the same cluster
        $this->bootstrapToken = $bootstrapCluster->getAuthToken();
        $this->bootstrapCluster = $bootstrapCluster;
        $this->namespace = $namespace;
        $this->serviceAccount = $serviceAccount;
    }

    public function withExpirationSeconds(int $seconds): static
    {
        $this->expirationSeconds = $seconds;

        return $this;
    }

    public function withAudiences(array $audiences): static
    {
        $this->audiences = $audiences;

        return $this;
    }

    public function refresh(): void
    {
        $path = "/api/v1/namespaces/{$this->namespace}/serviceaccounts/{$this->serviceAccount}/token";

        $requestBody = [
            'apiVersion' => 'authentication.k8s.io/v1',
            'kind' => 'TokenRequest',
            'spec' => [
                'expirationSeconds' => $this->expirationSeconds,
            ],
        ];

        if (! empty($this->audiences)) {
            $requestBody['spec']['audiences'] = $this->audiences;
        }

        try {
            // Use the bootstrap token directly to avoid infinite recursion
            // when this provider is set on the same cluster instance
            $response = $this->makeBootstrapRequest('POST', $path, json_encode($requestBody));

            $result = json_decode($response->getBody(), true);

            if (! isset($result['status']['token'])) {
                throw new AuthenticationException(
                    'TokenRequest API response missing status.token'
                );
            }

            $this->token = $result['status']['token'];

            if (isset($result['status']['expirationTimestamp'])) {
                $this->expiresAt = new \DateTimeImmutable(
                    $result['status']['expirationTimestamp']
                );
            } else {
                // Fallback: assume the token expires based on expirationSeconds
                $this->expiresAt = (new \DateTimeImmutable)
                    ->modify("+{$this->expirationSeconds} seconds");
            }
        } catch (\Exception $e) {
            throw new AuthenticationException(
                "Failed to request service account token: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Make a request using the bootstrap cluster's original authentication.
     * This bypasses the token provider to prevent infinite recursion.
     */
    protected function makeBootstrapRequest(string $method, string $path, string $payload = '')
    {
        $reflection = new \ReflectionClass($this->bootstrapCluster);

        // Temporarily get the original token and clear the provider
        $tokenProviderProp = $reflection->getProperty('tokenProvider');
        $tokenProviderProp->setAccessible(true);
        $originalProvider = $tokenProviderProp->getValue($this->bootstrapCluster);

        $tokenProp = $reflection->getProperty('token');
        $tokenProp->setAccessible(true);
        $originalToken = $tokenProp->getValue($this->bootstrapCluster);

        try {
            // Clear the provider temporarily and restore the original token
            $tokenProviderProp->setValue($this->bootstrapCluster, null);
            $tokenProp->setValue($this->bootstrapCluster, $this->bootstrapToken);

            // Make the request with the bootstrap authentication
            $response = $this->bootstrapCluster->call($method, $path, $payload);

            return $response;
        } finally {
            // Restore the original state
            $tokenProviderProp->setValue($this->bootstrapCluster, $originalProvider);
            $tokenProp->setValue($this->bootstrapCluster, $originalToken);
        }
    }
}
