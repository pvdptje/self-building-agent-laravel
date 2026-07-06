<?php

namespace App\Agent;

use RuntimeException;
use Throwable;

/**
 * The provider rejected the request because the conversation no longer fits
 * its context window. The caller should shrink the history and try again
 * instead of treating this as a dead provider.
 */
class ContextOverflowException extends RuntimeException
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
