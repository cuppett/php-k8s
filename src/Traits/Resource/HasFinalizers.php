<?php

namespace RenokiCo\PhpK8s\Traits\Resource;

trait HasFinalizers
{
    /**
     * Get the finalizers.
     */
    public function getFinalizers(): array
    {
        return $this->getAttribute('metadata.finalizers', []);
    }

    /**
     * Set the finalizers.
     */
    public function setFinalizers(array $finalizers): self
    {
        return $this->setAttribute('metadata.finalizers', $finalizers);
    }

    /**
     * Add a finalizer.
     */
    public function addFinalizer(string $finalizer): self
    {
        $finalizers = $this->getFinalizers();

        if (! in_array($finalizer, $finalizers)) {
            $finalizers[] = $finalizer;
            $this->setFinalizers($finalizers);
        }

        return $this;
    }

    /**
     * Remove a finalizer.
     */
    public function removeFinalizer(string $finalizer): self
    {
        $finalizers = array_values(
            array_filter($this->getFinalizers(), fn ($f) => $f !== $finalizer)
        );

        return $this->setFinalizers($finalizers);
    }

    /**
     * Check if a finalizer exists.
     */
    public function hasFinalizer(string $finalizer): bool
    {
        return in_array($finalizer, $this->getFinalizers());
    }
}
