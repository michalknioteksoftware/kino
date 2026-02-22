<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class ValidDateTimeString extends Constraint
{
    public string $message = 'The value "{{ value }}" is not a valid date-time string (e.g. ISO 8601: 2025-12-01T20:00:00+00:00).';

    public function getTargets(): string|array
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
