<?php

namespace RenokiCo\PhpK8s\Test\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Kinds\K8sResource;
use RenokiCo\PhpK8s\Traits\Resource\HasSpec;

class GatewayClass extends K8sResource implements InteractsWithK8sCluster
{
    use HasSpec;

    /**
     * The resource Kind parameter.
     */
    protected static ?string $kind = 'GatewayClass';

    /**
     * The default version for the resource.
     */
    protected static string $defaultVersion = 'gateway.networking.k8s.io/v1';

    /**
     * Wether the resource has a namespace.
     */
    protected static bool $namespaceable = false;

    /**
     * Set the controller name.
     *
     * @return $this
     */
    public function setControllerName(string $controllerName)
    {
        return $this->setSpec('controllerName', $controllerName);
    }

    /**
     * Get the controller name.
     */
    public function getControllerName(): ?string
    {
        return $this->getSpec('controllerName');
    }

    /**
     * Set the parameters reference.
     *
     * @return $this
     */
    public function setParametersRef(array $parametersRef)
    {
        return $this->setSpec('parametersRef', $parametersRef);
    }

    /**
     * Get the parameters reference.
     */
    public function getParametersRef(): ?array
    {
        return $this->getSpec('parametersRef');
    }

    /**
     * Set the description.
     *
     * @return $this
     */
    public function setDescription(string $description)
    {
        return $this->setSpec('description', $description);
    }

    /**
     * Get the description.
     */
    public function getDescription(): ?string
    {
        return $this->getSpec('description');
    }
}
