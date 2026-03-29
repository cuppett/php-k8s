<?php

namespace RenokiCo\PhpK8s\Instances;

class ResourceMetric extends Instance
{
    /**
     * The resource metric type.
     */
    protected static string $type = 'Resource';

    /**
     * Set the resource type to CPU.
     *
     * @return $this
     */
    public function cpu(): static
    {
        return $this->setMetric('cpu');
    }

    /**
     * Set the resource type to memory.
     *
     * @return $this
     */
    public function memory(): static
    {
        return $this->setMetric('memory');
    }

    /**
     * Set average utilization for the metric.
     *
     * @return $this
     */
    public function averageUtilization(int|string $utilization = 50): static
    {
        return $this->setAttribute('resource.target.type', 'Utilization')
            ->setAttribute('resource.target.averageUtilization', $utilization);
    }

    /**
     * Get the average utilization.
     */
    public function getAverageUtilization(): string|int|float
    {
        return $this->getAttribute('resource.target.averageUtilization', 0);
    }

    /**
     * Set average value for the metric.
     *
     * @return $this
     */
    public function averageValue(string|int|float $value): static
    {
        return $this->setAttribute('resource.target.type', 'AverageValue')
            ->setAttribute('resource.target.averageValue', $value);
    }

    /**
     * Get the average value size.
     */
    public function getAverageValue(): string|int|float
    {
        return $this->getAttribute('resource.target.averageValue');
    }

    /**
     * Set the specific value for the metric.
     *
     * @return $this
     */
    public function value(string|int|float $value): static
    {
        return $this->setAttribute('resource.target.type', 'Value')
            ->setAttribute('resource.target.value', $value);
    }

    /**
     * Get the value size.
     */
    public function getValue(): string|int|float
    {
        return $this->getAttribute('resource.target.value');
    }

    /**
     * Get the resource target type.
     */
    public function getType(): string
    {
        return $this->getAttribute('resource.target.type', 'Utilization');
    }

    /**
     * Alias for ->setName().
     *
     * @return $this
     */
    public function setMetric(string $name): static
    {
        return $this->setName($name);
    }

    /**
     * Set the resource metric name.
     *
     * @return $this
     */
    public function setName(string $name): static
    {
        return $this->setAttribute('resource.name', $name);
    }

    /**
     * Get the resource metric name.
     */
    public function getName(): string
    {
        return $this->getAttribute('resource.name');
    }

    /**
     * Get the instance as an array.
     */
    #[\Override]
    public function toArray(): array
    {
        return array_merge($this->attributes, [
            'type' => static::$type,
        ]);
    }
}
