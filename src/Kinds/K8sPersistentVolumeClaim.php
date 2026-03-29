<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Traits\Resource\HasAccessModes;
use RenokiCo\PhpK8s\Traits\Resource\HasSelector;
use RenokiCo\PhpK8s\Traits\Resource\HasSpec;
use RenokiCo\PhpK8s\Traits\Resource\HasStatus;
use RenokiCo\PhpK8s\Traits\Resource\HasStatusPhase;
use RenokiCo\PhpK8s\Traits\Resource\HasStorageClass;

class K8sPersistentVolumeClaim extends K8sResource implements InteractsWithK8sCluster, Watchable
{
    use HasAccessModes;
    use HasSelector;
    use HasSpec;
    use HasStatus;
    use HasStatusPhase;
    use HasStorageClass;

    /**
     * The resource Kind parameter.
     */
    protected static ?string $kind = 'PersistentVolumeClaim';

    /**
     * Wether the resource has a namespace.
     */
    protected static bool $namespaceable = true;

    /**
     * Set the capacity of the PV.
     *
     * @return $this
     */
    public function setCapacity(int $size, string $measure = 'Gi')
    {
        return $this->setSpec('resources.requests.storage', $size.$measure);
    }

    /**
     * Get the PV storage capacity.
     *
     * @return string|null
     */
    public function getCapacity()
    {
        return $this->getSpec('resources.requests.storage', null);
    }

    /**
     * Check if the PV is available to be used.
     */
    public function isAvailable(): bool
    {
        return $this->getPhase() === 'Available';
    }

    /**
     * Check if the PV is bound.
     */
    public function isBound(): bool
    {
        return $this->getPhase() === 'Bound';
    }
}
