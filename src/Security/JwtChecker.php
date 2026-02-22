<?php

declare(strict_types=1);

namespace App\Security;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;

final class JwtChecker
{
    private const ALGORITHM = 'HS256';

    public function __construct(
        private readonly string $secret,
    ) {
    }

    /**
     * Validate Bearer token and return decoded payload.
     *
     * @return array<string, mixed> Decoded payload (e.g. iat, exp, sub)
     * @throws JwtInvalidException When token is missing, invalid or expired
     */
    public function validateBearer(string $authorizationHeader): array
    {
        if (!preg_match('/^\s*Bearer\s+(\S+)\s*$/i', $authorizationHeader, $m)) {
            throw JwtInvalidException::missingOrMalformed();
        }
        return $this->validateToken($m[1]);
    }

    /**
     * Validate raw token string and return decoded payload.
     *
     * @return array<string, mixed>
     * @throws JwtInvalidException
     */
    public function validateToken(string $token): array
    {
        $key = $this->normalizeSecret($this->secret);
        try {
            $decoded = JWT::decode($token, new Key($key, self::ALGORITHM));
            return (array) $decoded;
        } catch (ExpiredException) {
            throw JwtInvalidException::expired();
        } catch (SignatureInvalidException | BeforeValidException | \InvalidArgumentException $e) {
            throw JwtInvalidException::invalid($e->getMessage());
        }
    }

    /**
     * HS256 requires key >= 32 bytes; derive from secret if shorter.
     */
    private function normalizeSecret(string $secret): string
    {
        if (strlen($secret) < 32) {
            return hash('sha256', $secret, true);
        }
        return $secret;
    }
}
