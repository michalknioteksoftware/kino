<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\CinemaRoom;
use App\Request\SeatDto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

final class ReservationValidator
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * Validates that the reservation is not made after the movie's datetime.
     * Uses $now as the reservation time (default: current time).
     */
    public function validateReservationNotAfterMovieDatetime(CinemaRoom $room, ?\DateTimeImmutable $now = null): ConstraintViolationListInterface
    {
        $violations = new ConstraintViolationList();
        $movieDatetime = $room->getMovieDatetime();
        if ($movieDatetime === null) {
            return $violations;
        }

        $now = $now ?? new \DateTimeImmutable();
        if ($now > $movieDatetime) {
            $violations->add(new ConstraintViolation(
                'Reservations are not allowed after the movie start time (movie: ' . $movieDatetime->format(\DateTimeInterface::ATOM) . ').',
                'Reservations are not allowed after the movie start time.',
                [],
                $room,
                'cinemaRoomId',
                $movieDatetime->format(\DateTimeInterface::ATOM)
            ));
        }

        return $violations;
    }

    /**
     * Validates that all requested seats are within the cinema room's row/column limits.
     *
     * @param list<SeatDto> $seats
     */
    public function validateSeatsWithinRoom(CinemaRoom $room, array $seats): ConstraintViolationListInterface
    {
        $violations = new ConstraintViolationList();
        $maxRows = $room->getRows();
        $maxColumns = $room->getColumns();

        foreach ($seats as $index => $seat) {
            $row = $seat->row !== null ? (int) $seat->row : null;
            $column = $seat->column !== null ? (int) $seat->column : null;
            if ($row === null || $column === null) {
                continue;
            }
            if ($row > $maxRows) {
                $violations->add(new ConstraintViolation(
                    "Row {$row} exceeds room limit of {$maxRows} rows.",
                    "Row {{ value }} exceeds room limit of {{ limit }} rows.",
                    ['{{ value }}' => (string) $row, '{{ limit }}' => (string) $maxRows],
                    $seat,
                    "seats[{$index}].row",
                    $row
                ));
            }
            if ($column > $maxColumns) {
                $violations->add(new ConstraintViolation(
                    "Column {$column} exceeds room limit of {$maxColumns} columns.",
                    "Column {{ value }} exceeds room limit of {{ limit }} columns.",
                    ['{{ value }}' => (string) $column, '{{ limit }}' => (string) $maxColumns],
                    $seat,
                    "seats[{$index}].column",
                    $column
                ));
            }
        }

        return $violations;
    }

    /**
     * Validates that none of the requested seats are already reserved.
     * Also checks for duplicate seats within the same request.
     *
     * @param list<SeatDto> $seats
     */
    public function validateSeatsNotTaken(CinemaRoom $room, array $seats): ConstraintViolationListInterface
    {
        $violations = new ConstraintViolationList();
        $roomId = $room->getId();
        if ($roomId === null) {
            return $violations;
        }

        $requestedPairs = [];
        $seen = [];
        foreach ($seats as $index => $seat) {
            $row = $seat->row !== null ? (int) $seat->row : null;
            $column = $seat->column !== null ? (int) $seat->column : null;
            if ($row === null || $column === null) {
                continue;
            }
            $key = "{$row}-{$column}";
            if (isset($seen[$key])) {
                $violations->add(new ConstraintViolation(
                    "Seat row {$row}, column {$column} is duplicated in the request.",
                    'Seat row {{ row }}, column {{ column }} is duplicated in the request.',
                    ['{{ row }}' => (string) $row, '{{ column }}' => (string) $column],
                    $seat,
                    "seats[{$index}]",
                    $key
                ));
                continue;
            }
            $seen[$key] = true;
            $requestedPairs[] = ['row' => $row, 'column' => $column];
        }

        if ($requestedPairs === []) {
            return $violations;
        }

        $requestedSet = [];
        foreach ($requestedPairs as $p) {
            $requestedSet[$p['row'] . '-' . $p['column']] = true;
        }

        $repo = $this->em->getRepository(\App\Entity\CinemaRoomReservation::class);
        $existing = $repo->createQueryBuilder('r')
            ->select('r.row', 'r.column')
            ->where('r.cinemaRoom = :room')
            ->setParameter('room', $room)
            ->getQuery()
            ->getArrayResult();

        foreach ($existing as $row) {
            $r = (int) $row['row'];
            $c = (int) $row['column'];
            if (isset($requestedSet[$r . '-' . $c])) {
                $violations->add(new ConstraintViolation(
                    "Seat row {$r}, column {$c} is already reserved.",
                    'Seat row {{ row }}, column {{ column }} is already reserved.',
                    ['{{ row }}' => (string) $r, '{{ column }}' => (string) $c],
                    null,
                    'seats',
                    "{$r}-{$c}"
                ));
            }
        }

        return $violations;
    }
}
