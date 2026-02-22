<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\CinemaRoom;
use App\Entity\CinemaRoomReservation;
use App\Request\ReservationRequestDto;
use App\Request\SeatDto;
use App\Service\ReservationValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/reservations', name: 'api_reservation_', format: 'json')]
final class ReservationController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ValidatorInterface $validator,
        private readonly ReservationValidator $reservationValidator,
    ) {
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse|Response
    {
        $body = $request->toArray();
        $dto = $this->mapBodyToDto($body);

        $errors = $this->validator->validate($dto);
        if ($errors->count() > 0) {
            return $this->validationErrorResponse($errors);
        }

        $room = $this->em->getRepository(CinemaRoom::class)->find((int) $dto->cinemaRoomId);
        if ($room === null) {
            return $this->json(['error' => 'Cinema room not found.'], Response::HTTP_NOT_FOUND);
        }

        $seatDtos = $dto->seats;
        $errors = $this->reservationValidator->validateReservationNotAfterMovieDatetime($room);
        if ($errors->count() > 0) {
            return $this->validationErrorResponse($errors);
        }

        $errors = $this->reservationValidator->validateSeatsWithinRoom($room, $seatDtos);
        if ($errors->count() > 0) {
            return $this->validationErrorResponse($errors);
        }

        $errors = $this->reservationValidator->validateSeatsNotTaken($room, $seatDtos);
        if ($errors->count() > 0) {
            return $this->validationErrorResponse($errors);
        }

        $this->em->beginTransaction();
        try {
            $reservations = [];
            foreach ($seatDtos as $seat) {
                $row = (int) $seat->row;
                $column = (int) $seat->column;
                $reservation = new CinemaRoomReservation();
                $reservation->setCinemaRoom($room)->setRow($row)->setColumn($column)
                    ->setReservedByName((string) $dto->reservedByName);
                $this->em->persist($reservation);
                $reservations[] = $reservation;
            }
            $this->em->flush();
            $this->em->commit();

            $data = array_map(fn (CinemaRoomReservation $r) => [
                'id' => $r->getId(),
                'row' => $r->getRow(),
                'column' => $r->getColumn(),
                'reservedByName' => $r->getReservedByName(),
            ], $reservations);

            return $this->json([
                'message' => 'Reservations created.',
                'data' => $data,
            ], Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }
    }

    /**
     * @param array<string, mixed> $body
     */
    private function mapBodyToDto(array $body): ReservationRequestDto
    {
        $dto = new ReservationRequestDto();
        $dto->reservedByName = isset($body['reservedByName']) ? trim((string) $body['reservedByName']) : null;
        if ($dto->reservedByName !== null && $dto->reservedByName === '') {
            $dto->reservedByName = null;
        }
        $dto->cinemaRoomId = isset($body['cinemaRoomId']) ? (int) $body['cinemaRoomId'] : null;
        $dto->seats = [];
        foreach ($body['seats'] ?? [] as $seat) {
            $s = new SeatDto();
            $s->row = $seat['row'] ?? null;
            $s->column = $seat['column'] ?? null;
            $dto->seats[] = $s;
        }
        return $dto;
    }

    private function validationErrorResponse(\Symfony\Component\Validator\ConstraintViolationListInterface $errors): JsonResponse
    {
        $messages = [];
        foreach ($errors as $e) {
            $path = $e->getPropertyPath();
            if (!isset($messages[$path])) {
                $messages[$path] = [];
            }
            $messages[$path][] = $e->getMessage();
        }
        return $this->json([
            'error' => 'Validation failed',
            'messages' => $messages,
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
