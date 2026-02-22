<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\CinemaRoom;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/public/cinema-rooms', name: 'api_public_cinema_room_', format: 'json')]
final class PublicCinemaRoomController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * List all cinema rooms with reserved seats (no JWT required).
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $rooms = $this->em->getRepository(CinemaRoom::class)->findBy([], ['id' => 'ASC']);
        return $this->json([
            'data' => array_map([$this, 'roomWithReservedSeatsToArray'], $rooms),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function roomWithReservedSeatsToArray(CinemaRoom $room): array
    {
        $reservedSeats = [];
        foreach ($room->getReservations() as $reservation) {
            $row = $reservation->getRow();
            $column = $reservation->getColumn();
            if ($row !== null && $column !== null) {
                $reservedSeats[] = [
                    'row' => $row,
                    'column' => $column,
                    'reservedByName' => $reservation->getReservedByName(),
                ];
            }
        }

        return [
            'id' => $room->getId(),
            'rows' => $room->getRows(),
            'columns' => $room->getColumns(),
            'movie' => $room->getMovie(),
            'movieDatetime' => $room->getMovieDatetime()?->format(\DateTimeInterface::ATOM),
            'reservedSeats' => $reservedSeats,
        ];
    }
}
