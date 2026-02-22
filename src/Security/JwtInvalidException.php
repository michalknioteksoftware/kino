<?php

declare(strict_types=1);

namespace App\Security;

final class JwtInvalidException extends \RuntimeException
{
    private function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function missingOrMalformed(): self
    {
        return new self('Missing or invalid Authorization header. Expected: Bearer <token>');
    }

    public static function expired(): self
    {
        return new self('Token has expired');
    }

    public static function invalid(string $detail = ''): self
    {
        return new self('Invalid token.' . ($detail !== '' ? ' ' . $detail : ''));
    }
}
