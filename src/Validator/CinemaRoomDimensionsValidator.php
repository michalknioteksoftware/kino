<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\CinemaRoom;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class CinemaRoomDimensionsValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof CinemaRoomDimensions) {
            throw new UnexpectedTypeException($constraint, CinemaRoomDimensions::class);
        }
        if (!$value instanceof CinemaRoom) {
            return;
        }

        $reservations = $value->getReservations();
        if ($reservations->isEmpty()) {
            return;
        }

        $maxRow = 0;
        $maxColumn = 0;
        foreach ($reservations as $reservation) {
            $r = $reservation->getRow();
            $c = $reservation->getColumn();
            if ($r !== null && $r > $maxRow) {
                $maxRow = $r;
            }
            if ($c !== null && $c > $maxColumn) {
                $maxColumn = $c;
            }
        }

        $newRows = $value->getRows();
        $newColumns = $value->getColumns();

        if ($maxRow > $newRows) {
            $this->context->buildViolation($constraint->messageRows)
                ->setParameter('{{ value }}', (string) $newRows)
                ->atPath('rows')
                ->addViolation();
        }
        if ($maxColumn > $newColumns) {
            $this->context->buildViolation($constraint->messageColumns)
                ->setParameter('{{ value }}', (string) $newColumns)
                ->atPath('columns')
                ->addViolation();
        }
    }
}
