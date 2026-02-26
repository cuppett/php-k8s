<?php

namespace RenokiCo\PhpK8s\Traits\Resource;

trait HasStatus
{
    /**
     * Get the status parameter with default.
     *
     * @return mixed
     */
    public function getStatus(string $name, mixed $default = null)
    {
        return $this->getAttribute("status.{$name}", $default);
    }

    /**
     * Set a status field.
     */
    public function setStatus(string $name, mixed $value): self
    {
        return $this->setAttribute("status.{$name}", $value);
    }

    /**
     * Set the entire status object.
     */
    public function setStatusData(array $status): self
    {
        return $this->setAttribute('status', $status);
    }

    /**
     * Get the entire status object.
     */
    public function getStatusData(): array
    {
        return $this->getAttribute('status', []);
    }
}
