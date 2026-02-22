<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\CinemaRoom;
use App\Attribute\RequireJwt;
use App\Request\CinemaRoomBodyDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/cinema-rooms', name: 'api_cinema_room_', format: 'json')]
#[RequireJwt]
final class CinemaRoomController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $rooms = $this->em->getRepository(CinemaRoom::class)->findBy([], ['id' => 'ASC']);
        return $this->json([
            'data' => array_map([$this, 'roomToArray'], $rooms),
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): JsonResponse|Response
    {
        $room = $this->em->getRepository(CinemaRoom::class)->find($id);
        if ($room === null) {
            return $this->json(['error' => 'Cinema room not found.'], Response::HTTP_NOT_FOUND);
        }
        return $this->json(['data' => $this->roomToArray($room)]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse|Response
    {
        $body = $request->toArray();
        $bodyErrors = $this->validateBodyDateTime($body);
        if (count($bodyErrors) > 0) {
            return $this->validationErrorResponse($bodyErrors);
        }
        $room = new CinemaRoom();
        $this->applyBodyToRoom($room, $body);
        $errors = $this->validator->validate($room);
        if (count($errors) > 0) {
            return $this->validationErrorResponse($errors);
        }
        $this->em->persist($room);
        $this->em->flush();
        return $this->json(['data' => $this->roomToArray($room)], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', requirements: ['id' => '\d+'], methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse|Response
    {
        $room = $this->em->getRepository(CinemaRoom::class)->find($id);
        if ($room === null) {
            return $this->json(['error' => 'Cinema room not found.'], Response::HTTP_NOT_FOUND);
        }
        $body = $request->toArray();
        $bodyErrors = $this->validateBodyDateTime($body);
        if (count($bodyErrors) > 0) {
            return $this->validationErrorResponse($bodyErrors);
        }
        $this->applyBodyToRoom($room, $body);
        $errors = $this->validator->validate($room);
        if (count($errors) > 0) {
            return $this->validationErrorResponse($errors);
        }
        $this->em->flush();
        return $this->json(['data' => $this->roomToArray($room)]);
    }

    #[Route('/{id}', name: 'delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(int $id): JsonResponse|Response
    {
        $room = $this->em->getRepository(CinemaRoom::class)->find($id);
        if ($room === null) {
            return $this->json(['error' => 'Cinema room not found.'], Response::HTTP_NOT_FOUND);
        }
        if ($room->getReservations()->count() > 0) {
            return $this->json([
                'error' => 'Cannot delete cinema room with existing reservations. Remove or reassign reservations first.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $this->em->remove($room);
        $this->em->flush();
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @return array<string, mixed>
     */
    private function roomToArray(CinemaRoom $room): array
    {
        return [
            'id' => $room->getId(),
            'rows' => $room->getRows(),
            'columns' => $room->getColumns(),
            'movie' => $room->getMovie(),
            'creation' => $room->getCreation()?->format(\DateTimeInterface::ATOM),
            'updated' => $room->getUpdated()?->format(\DateTimeInterface::ATOM),
            'movieDatetime' => $room->getMovieDatetime()?->format(\DateTimeInterface::ATOM),
        ];
    }

    /**
     * @param array<string, mixed> $body
     */
    private function applyBodyToRoom(CinemaRoom $room, array $body): void
    {
        if (isset($body['rows'])) {
            $room->setRows((int) $body['rows']);
        }
        if (isset($body['columns'])) {
            $room->setColumns((int) $body['columns']);
        }
        if (isset($body['movie'])) {
            $room->setMovie((string) $body['movie']);
        }
        if (isset($body['movieDatetime']) && $body['movieDatetime'] !== null) {
            $room->setMovieDatetime(new \DateTimeImmutable((string) $body['movieDatetime']));
        }
    }

    /**
     * @param array<string, mixed> $body
     * @return \Symfony\Component\Validator\ConstraintViolationListInterface
     */
    private function validateBodyDateTime(array $body): \Symfony\Component\Validator\ConstraintViolationListInterface
    {
        $dto = new CinemaRoomBodyDto();
        $dto->movieDatetime = isset($body['movieDatetime']) ? (string) $body['movieDatetime'] : null;
        return $this->validator->validate($dto);
    }

    private function validationErrorResponse(\Symfony\Component\Validator\ConstraintViolationListInterface $errors): JsonResponse
    {
        $messages = [];
        foreach ($errors as $e) {
            $messages[$e->getPropertyPath()][] = $e->getMessage();
        }
        return $this->json([
            'error' => 'Validation failed',
            'messages' => $messages,
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
