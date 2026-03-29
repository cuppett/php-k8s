<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;

class K8sPriorityClass extends K8sResource implements InteractsWithK8sCluster
{
    /**
     * The resource Kind parameter.
     */
    protected static ?string $kind = 'PriorityClass';

    /**
     * The default version for the resource.
     */
    protected static string $defaultVersion = 'scheduling.k8s.io/v1';

    /**
     * Whether the resource has a namespace.
     */
    protected static bool $namespaceable = false;

    /**
     * Set the priority value.
     *
     * @return $this
     */
    public function setValue(int $value)
    {
        return $this->setAttribute('value', $value);
    }

    /**
     * Get the priority value.
     *
     * @return int|null
     */
    public function getValue()
    {
        return $this->getAttribute('value');
    }

    /**
     * Set whether this is a global default priority class.
     *
     * @return $this
     */
    public function setGlobalDefault(bool $globalDefault)
    {
        return $this->setAttribute('globalDefault', $globalDefault);
    }

    /**
     * Check if this is a global default priority class.
     */
    public function isGlobalDefault(): bool
    {
        return $this->getAttribute('globalDefault', false);
    }

    /**
     * Set the description.
     *
     * @return $this
     */
    public function setDescription(string $description)
    {
        return $this->setAttribute('description', $description);
    }

    /**
     * Get the description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->getAttribute('description');
    }

    /**
     * Set the preemption policy.
     *
     * @return $this
     */
    public function setPreemptionPolicy(string $policy)
    {
        return $this->setAttribute('preemptionPolicy', $policy);
    }

    /**
     * Get the preemption policy.
     *
     * @return string|null
     */
    public function getPreemptionPolicy()
    {
        return $this->getAttribute('preemptionPolicy');
    }
}
