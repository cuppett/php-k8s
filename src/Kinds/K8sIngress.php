<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Traits\Resource\HasSpec;

class K8sIngress extends K8sResource implements InteractsWithK8sCluster, Watchable
{
    use HasSpec;

    /**
     * The resource Kind parameter.
     */
    protected static ?string $kind = 'Ingress';

    /**
     * The default version for the resource.
     */
    protected static string $defaultVersion = 'networking.k8s.io/v1';

    /**
     * Wether the resource has a namespace.
     */
    protected static bool $namespaceable = true;

    /**
     * Set the spec rules.
     *
     * @return $this
     */
    public function setRules(array $rules = [])
    {
        return $this->setSpec('rules', $rules);
    }

    /**
     * Add a new rule to the list.
     *
     * @return $this
     */
    public function addRule(array $rule)
    {
        return $this->addToSpec('rules', $rule);
    }

    /**
     * Batch-add multiple rules to the list.
     *
     * @return $this
     */
    public function addRules(array $rules)
    {
        foreach ($rules as $rule) {
            $this->addRule($rule);
        }

        return $this;
    }

    /**
     * Get the spec rules.
     */
    public function getRules(): array
    {
        return $this->getSpec('rules', []);
    }

    /**
     * Set the spec tls.
     *
     * @return $this
     */
    public function setTls(array $tlsData = [])
    {
        return $this->setSpec('tls', $tlsData);
    }

    /**
     * Get the tls spec.
     */
    public function getTls(): array
    {
        return $this->getSpec('tls', []);
    }
}
