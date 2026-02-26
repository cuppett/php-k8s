<?php

namespace RenokiCo\PhpK8s\Traits\Resource;

use RenokiCo\PhpK8s\Kinds\K8sResource;

trait HasOwnerReferences
{
    /**
     * Get the owner references.
     */
    public function getOwnerReferences(): array
    {
        return $this->getAttribute('metadata.ownerReferences', []);
    }

    /**
     * Set the owner references.
     */
    public function setOwnerReferences(array $refs): self
    {
        return $this->setAttribute('metadata.ownerReferences', $refs);
    }

    /**
     * Add an owner reference.
     */
    public function addOwnerReference(K8sResource $resource, bool $controller = false, bool $blockOwnerDeletion = false): self
    {
        $uid = $resource->getAttribute('metadata.uid');

        if (! $uid) {
            throw new \InvalidArgumentException('Resource must have a UID (must be synced with cluster)');
        }

        $refs = $this->getOwnerReferences();

        // Check if already exists (idempotent).
        foreach ($refs as $ref) {
            if (($ref['uid'] ?? null) === $uid) {
                return $this;
            }
        }

        $newRef = [
            'apiVersion' => $resource->getApiVersion(),
            'kind' => $resource->getKind(),
            'name' => $resource->getName(),
            'uid' => $uid,
        ];

        if ($controller) {
            $newRef['controller'] = true;
        }

        if ($blockOwnerDeletion) {
            $newRef['blockOwnerDeletion'] = true;
        }

        $refs[] = $newRef;

        return $this->setOwnerReferences($refs);
    }

    /**
     * Remove an owner reference.
     */
    public function removeOwnerReference(K8sResource $resource): self
    {
        $uid = $resource->getAttribute('metadata.uid');

        $refs = array_values(
            array_filter($this->getOwnerReferences(), fn ($ref) => ($ref['uid'] ?? null) !== $uid)
        );

        return $this->setOwnerReferences($refs);
    }

    /**
     * Check if an owner reference exists.
     */
    public function hasOwnerReference(K8sResource $resource): bool
    {
        $uid = $resource->getAttribute('metadata.uid');

        foreach ($this->getOwnerReferences() as $ref) {
            if (($ref['uid'] ?? null) === $uid) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the controller owner reference.
     */
    public function getControllerOwner(): ?array
    {
        foreach ($this->getOwnerReferences() as $ref) {
            if (($ref['controller'] ?? false) === true) {
                return $ref;
            }
        }

        return null;
    }
}
