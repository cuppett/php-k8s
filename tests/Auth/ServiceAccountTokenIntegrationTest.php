<?php

namespace RenokiCo\PhpK8s\Test\Auth;

use RenokiCo\PhpK8s\Auth\ServiceAccountTokenProvider;
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\KubernetesCluster;
use RenokiCo\PhpK8s\Test\TestCase;

class ServiceAccountTokenIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! getenv('CI') && ! $this->isClusterAvailable()) {
            $this->markTestSkipped('Integration tests require a live Kubernetes cluster');
        }
    }

    private function isClusterAvailable(): bool
    {
        try {
            $this->cluster->getAllNamespaces();

            return true;
        } catch (KubernetesAPIException $e) {
            return false;
        }
    }

    public function test_service_account_token_request()
    {
        // Create test service account
        $sa = $this->cluster->serviceAccount()
            ->setName('test-token-requester')
            ->setNamespace('default')
            ->createOrUpdate();

        try {
            // Test TokenRequest API
            $provider = new ServiceAccountTokenProvider(
                $this->cluster,
                'default',
                'test-token-requester'
            );
            $provider->withExpirationSeconds(600); // 10 minutes

            $token = $provider->getToken();

            // Assertions
            $this->assertNotEmpty($token);
            $this->assertStringStartsWith('eyJ', $token); // JWT format
            $this->assertNotNull($provider->getExpiresAt());

            $expiresAt = $provider->getExpiresAt();
            $now = new \DateTimeImmutable;
            $diff = $expiresAt->getTimestamp() - $now->getTimestamp();

            // Token should expire in ~10 minutes (allow some buffer)
            $this->assertGreaterThan(500, $diff);
            $this->assertLessThan(700, $diff);
        } finally {
            $sa->delete();
        }
    }

    public function test_service_account_token_with_audiences()
    {
        $sa = $this->cluster->serviceAccount()
            ->setName('test-token-audience')
            ->setNamespace('default')
            ->createOrUpdate();

        try {
            $provider = new ServiceAccountTokenProvider(
                $this->cluster,
                'default',
                'test-token-audience'
            );
            $provider->withAudiences(['https://kubernetes.default.svc', 'custom-audience']);

            $token = $provider->getToken();
            $this->assertNotEmpty($token);
        } finally {
            $sa->delete();
        }
    }

    public function test_convenience_method_with_service_account_token()
    {
        $sa = $this->cluster->serviceAccount()
            ->setName('test-convenience-sa')
            ->setNamespace('default')
            ->createOrUpdate();

        try {
            // Use convenience method
            $newCluster = KubernetesCluster::fromUrl('http://127.0.0.1:8080')
                ->withServiceAccountToken('default', 'test-convenience-sa', 1800);

            $token = $newCluster->getAuthToken();
            $this->assertNotEmpty($token);
        } finally {
            $sa->delete();
        }
    }
}
