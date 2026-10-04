<?php

namespace App\Exceptions;

use RuntimeException;

class BiteshipApiException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $status,
        public readonly array $body = [],
    ) {
        parent::__construct($message, $status);
    }

    public function errorCode(): ?int
    {
        $code = $this->body['code'] ?? null;

        return is_numeric($code) ? (int) $code : null;
    }
}