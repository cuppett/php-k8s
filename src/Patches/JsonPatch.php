<?php

namespace RenokiCo\PhpK8s\Patches;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

/**
 * JSON Patch implementation following RFC 6902.
 *
 * @see https://tools.ietf.org/html/rfc6902
 */
class JsonPatch implements Arrayable, Jsonable
{
    /**
     * The patch operations.
     */
    protected array $operations = [];

    /**
     * Add an operation to add a value at the specified path.
     *
     * @return $this
     */
    public function add(string $path, mixed $value): static
    {
        $this->operations[] = [
            'op' => 'add',
            'path' => $path,
            'value' => $value,
        ];

        return $this;
    }

    /**
     * Add an operation to remove a value at the specified path.
     *
     * @return $this
     */
    public function remove(string $path): static
    {
        $this->operations[] = [
            'op' => 'remove',
            'path' => $path,
        ];

        return $this;
    }

    /**
     * Add an operation to replace a value at the specified path.
     *
     * @return $this
     */
    public function replace(string $path, mixed $value): static
    {
        $this->operations[] = [
            'op' => 'replace',
            'path' => $path,
            'value' => $value,
        ];

        return $this;
    }

    /**
     * Add an operation to move a value from one path to another.
     *
     * @return $this
     */
    public function move(string $from, string $path): static
    {
        $this->operations[] = [
            'op' => 'move',
            'from' => $from,
            'path' => $path,
        ];

        return $this;
    }

    /**
     * Add an operation to copy a value from one path to another.
     *
     * @return $this
     */
    public function copy(string $from, string $path): static
    {
        $this->operations[] = [
            'op' => 'copy',
            'from' => $from,
            'path' => $path,
        ];

        return $this;
    }

    /**
     * Add an operation to test a value at the specified path.
     *
     * @return $this
     */
    public function test(string $path, mixed $value): static
    {
        $this->operations[] = [
            'op' => 'test',
            'path' => $path,
            'value' => $value,
        ];

        return $this;
    }

    /**
     * Clear all operations.
     *
     * @return $this
     */
    public function clear(): static
    {
        $this->operations = [];

        return $this;
    }

    /**
     * Get the operations array.
     */
    public function getOperations(): array
    {
        return $this->operations;
    }

    /**
     * Check if the patch has any operations.
     */
    public function isEmpty(): bool
    {
        return empty($this->operations);
    }

    /**
     * Get the instance as an array.
     */
    #[\Override]
    public function toArray(): array
    {
        return $this->operations;
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
