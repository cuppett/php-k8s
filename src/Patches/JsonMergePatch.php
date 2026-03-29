<?php

namespace RenokiCo\PhpK8s\Patches;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

/**
 * JSON Merge Patch implementation following RFC 7396.
 *
 * @see https://tools.ietf.org/html/rfc7396
 */
class JsonMergePatch implements Arrayable, Jsonable
{
    /**
     * Create a new JSON Merge Patch instance.
     */
    public function __construct(protected array $patch = []) {}

    /**
     * Set a value in the patch.
     *
     * @return $this
     */
    public function set(string $key, mixed $value): static
    {
        data_set($this->patch, $key, $value);

        return $this;
    }

    /**
     * Remove a value from the patch by setting it to null.
     *
     * @return $this
     */
    public function remove(string $key): static
    {
        data_set($this->patch, $key, null);

        return $this;
    }

    /**
     * Merge another patch into this one.
     *
     * @return $this
     */
    public function merge(array|JsonMergePatch|Arrayable $patch): static
    {
        if ($patch instanceof Arrayable) {
            $patch = $patch->toArray();
        } elseif ($patch instanceof JsonMergePatch) {
            $patch = $patch->getPatch();
        }

        $this->patch = array_merge_recursive($this->patch, $patch);

        return $this;
    }

    /**
     * Clear the patch data.
     *
     * @return $this
     */
    public function clear(): static
    {
        $this->patch = [];

        return $this;
    }

    /**
     * Get the patch data.
     */
    public function getPatch(): array
    {
        return $this->patch;
    }

    /**
     * Check if the patch is empty.
     */
    public function isEmpty(): bool
    {
        return empty($this->patch);
    }

    /**
     * Create a new instance from an array.
     *
     * @return static
     */
    public static function fromArray(array $patch): self
    {
        return new static($patch);
    }

    /**
     * Get the instance as an array.
     */
    #[\Override]
    public function toArray(): array
    {
        return $this->patch;
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     *
     * @throws \JsonException
     */
    #[\Override]
    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options | JSON_THROW_ON_ERROR);
    }
}
