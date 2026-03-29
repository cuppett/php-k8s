<?php

namespace RenokiCo\PhpK8s\Traits\Resource;

trait HasKind
{
    /**
     * The resource Kind parameter.
     */
    protected static ?string $kind = null;

    /**
     * Get the resource kind.
     */
    public static function getKind(): ?string
    {
        return static::$kind;
    }
}
