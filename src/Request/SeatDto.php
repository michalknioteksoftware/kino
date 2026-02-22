<?php

declare(strict_types=1);

namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class SeatDto
{
    #[Assert\NotNull(message: 'Seat row is required.')]
    #[Assert\Type(type: 'integer', message: 'Seat row must be an integer.')]
    #[Assert\Range(min: 1, minMessage: 'Row must be at least 1.')]
    public mixed $row = null;

    #[Assert\NotNull(message: 'Seat column is required.')]
    #[Assert\Type(type: 'integer', message: 'Seat column must be an integer.')]
    #[Assert\Range(min: 1, minMessage: 'Column must be at least 1.')]
    public mixed $column = null;
}
