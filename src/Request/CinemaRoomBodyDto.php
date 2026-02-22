<?php

declare(strict_types=1);

namespace App\Request;

use App\Validator\ValidDateTimeString;

/**
 * DTO for validating cinema room request body (e.g. movieDatetime string) before applying to entity.
 */
final class CinemaRoomBodyDto
{
    #[ValidDateTimeString]
    public ?string $movieDatetime = null;
}
