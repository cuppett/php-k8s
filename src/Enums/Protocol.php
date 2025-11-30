<?php

namespace RenokiCo\PhpK8s\Enums;

/**
 * Network protocol.
 *
 * Supported protocols for ports and services.
 */
enum Protocol: string
{
    case TCP = 'TCP';
    case UDP = 'UDP';
    case SCTP = 'SCTP';

    /**
     * Check if this is a connection-oriented protocol.
     */
    public function isConnectionOriented(): bool
    {
        return match ($this) {
            self::TCP, self::SCTP => true,
            self::UDP => false,
        };
    }
}
