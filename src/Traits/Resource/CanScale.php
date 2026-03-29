<?php

namespace RenokiCo\PhpK8s\Traits\Resource;

use RenokiCo\PhpK8s\Kinds\K8sScale;

trait CanScale
{
    /**
     * Scale the current resource to a specific number of replicas.
     *
     * @return K8sScale
     */
    public function scale(int $replicas)
    {
        $scaler = $this->scaler();

        $scaler->setReplicas($replicas)->update();

        return $scaler;
    }
}
