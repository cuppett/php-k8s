<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Traits\Resource\HasSpec;

class K8sLease extends K8sResource implements InteractsWithK8sCluster, Watchable
{
    use HasSpec;

    /**
     * The resource kind.
     *
     * @var string
     */
    protected static $kind = 'Lease';

    /**
     * The default version for the resource.
     *
     * @var string
     */
    protected static $defaultVersion = 'coordination.k8s.io/v1';

    /**
     * Whether the resource is namespaceable.
     *
     * @var bool
     */
    protected static $namespaceable = true;

    /**
     * Set the holder identity.
     */
    public function setHolderIdentity(string $holderIdentity): self
    {
        return $this->setSpec('holderIdentity', $holderIdentity);
    }

    /**
     * Get the holder identity.
     */
    public function getHolderIdentity(): ?string
    {
        return $this->getSpec('holderIdentity');
    }

    /**
     * Set the lease duration in seconds.
     */
    public function setLeaseDurationSeconds(int $seconds): self
    {
        return $this->setSpec('leaseDurationSeconds', $seconds);
    }

    /**
     * Get the lease duration in seconds.
     */
    public function getLeaseDurationSeconds(): ?int
    {
        return $this->getSpec('leaseDurationSeconds');
    }

    /**
     * Set the acquire time (MicroTime format).
     */
    public function setAcquireTime(string $time): self
    {
        return $this->setSpec('acquireTime', $time);
    }

    /**
     * Get the acquire time.
     */
    public function getAcquireTime(): ?string
    {
        return $this->getSpec('acquireTime');
    }

    /**
     * Set the renew time (MicroTime format).
     */
    public function setRenewTime(string $time): self
    {
        return $this->setSpec('renewTime', $time);
    }

    /**
     * Get the renew time.
     */
    public function getRenewTime(): ?string
    {
        return $this->getSpec('renewTime');
    }

    /**
     * Get the lease transitions (read-only, managed by API server).
     */
    public function getLeaseTransitions(): ?int
    {
        return $this->getSpec('leaseTransitions');
    }
}
