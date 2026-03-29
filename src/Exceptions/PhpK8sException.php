<?php

namespace RenokiCo\PhpK8s\Exceptions;

use Exception;

class PhpK8sException extends Exception
{
    /**
     * Initialize the exception.
     */
    public function __construct(?string $message = null, int $code = 0, protected ?array $payload = null)
    {
        parent::__construct($message, $code);
    }

    /**
     * Get the payload instance.
     */
    public function getPayload(): ?array
    {
        return $this->payload;
    }
}
