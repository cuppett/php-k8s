<?php

namespace RenokiCo\PhpK8s\Instances;

use RenokiCo\PhpK8s\Kinds\K8sResource;

class ResourceObject extends ResourceMetric
{
    /**
     * The resource metric type.
     */
    protected static string $type = 'Object';

    /**
     * Attach a resource to the object.
     *
     * @return $this
     */
    public function setResource(K8sResource $resource): static
    {
        return $this->setAttribute('object.describedObject', [
            'apiVersion' => $resource->getApiVersion(),
            'kind' => $resource::getKind(),
            'name' => $resource->getName(),
        ]);
    }

    /**
     * Set average utilization for the metric.
     *
     * @return $this
     */
    #[\Override]
    public function averageUtilization(int|string $utilization = 50): static
    {
        return $this->setAttribute('object.target.type', 'Utilization')
            ->setAttribute('object.target.averageUtilization', $utilization);
    }

    /**
     * Get the average utilization.
     */
    #[\Override]
    public function getAverageUtilization(): string|int|float
    {
        return $this->getAttribute('object.target.averageUtilization', 0);
    }

    /**
     * Set average value for the metric.
     *
     * @return $this
     */
    #[\Override]
    public function averageValue(string|int|float $value): static
    {
        return $this->setAttribute('object.target.type', 'AverageValue')
            ->setAttribute('object.target.averageValue', $value);
    }

    /**
     * Get the average value size.
     */
    #[\Override]
    public function getAverageValue(): string|int|float
    {
        return $this->getAttribute('object.target.averageValue');
    }

    /**
     * Set the specific value for the metric.
     *
     * @return $this
     */
    #[\Override]
    public function value(string|int|float $value): static
    {
        return $this->setAttribute('object.target.type', 'Value')
            ->setAttribute('object.target.value', $value);
    }

    /**
     * Get the value size.
     */
    #[\Override]
    public function getValue(): string|int|float
    {
        return $this->getAttribute('object.target.value');
    }

    /**
     * Get the resource target type.
     */
    #[\Override]
    public function getType(): string
    {
        return $this->getAttribute('object.target.type', 'Utilization');
    }

    /**
     * Set the resource metric name.
     *
     * @return $this
     */
    #[\Override]
    public function setName(string $name): static
    {
        return $this->setAttribute('object.metric.name', $name);
    }

    /**
     * Get the resource metric name.
     */
    #[\Override]
    public function getName(): string
    {
        return $this->getAttribute('object.metric.name');
    }
}
