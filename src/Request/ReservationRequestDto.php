<?php

declare(strict_types=1);

namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class ReservationRequestDto
{
    #[Assert\NotBlank(message: 'reservedByName is required.')]
    #[Assert\Type(type: 'string', message: 'reservedByName must be a string.')]
    #[Assert\Length(max: 255, maxMessage: 'reservedByName must be at most {{ limit }} characters.')]
    public ?string $reservedByName = null;

    #[Assert\NotNull(message: 'cinemaRoomId is required.')]
    #[Assert\Type(type: 'integer', message: 'cinemaRoomId must be an integer.')]
    #[Assert\Positive(message: 'cinemaRoomId must be a positive integer.')]
    public mixed $cinemaRoomId = null;

    /** @var list<SeatDto> */
    #[Assert\NotNull(message: 'seats are required.')]
    #[Assert\Type(type: 'array', message: 'seats must be an array.')]
    #[Assert\Count(min: 1, minMessage: 'At least one seat must be requested.')]
    #[Assert\Valid]
    public array $seats = [];
}
