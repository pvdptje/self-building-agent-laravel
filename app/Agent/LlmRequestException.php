<?php

namespace App\Agent;

use RuntimeException;
use Throwable;

class LlmRequestException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly bool $retryable,
        ?Throwable $previous = null,
        public readonly bool $contextOverflow = false,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
