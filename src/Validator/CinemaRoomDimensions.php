<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class CinemaRoomDimensions extends Constraint
{
    public string $messageRows = 'Cannot set rows to {{ value }}: reservation(s) exist at row(s) beyond this limit.';
    public string $messageColumns = 'Cannot set columns to {{ value }}: reservation(s) exist at column(s) beyond this limit.';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
