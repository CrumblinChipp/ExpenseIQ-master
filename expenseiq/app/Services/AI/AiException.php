<?php

namespace App\Services\AI;

use RuntimeException;
use Throwable;

class AiException extends RuntimeException
{
    /**
     * @param string $reason One of: config, connection, rate_limited, http_error,
     *                       blocked, empty, invalid_json
     */
    public function __construct(
        string $message,
        public readonly string $reason = 'error',
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function isRateLimited(): bool
    {
        return $this->reason === 'rate_limited';
    }
}