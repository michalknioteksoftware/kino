<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class ValidDateTimeStringValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ValidDateTimeString) {
            throw new UnexpectedTypeException($constraint, ValidDateTimeString::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return;
        }

        $date = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $trimmed);
        if ($date !== false) {
            return;
        }

        try {
            new \DateTimeImmutable($trimmed);
        } catch (\Exception) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $trimmed)
                ->addViolation();
        }
    }
}
