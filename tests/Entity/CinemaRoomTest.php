<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\CinemaRoom;
use App\Entity\CinemaRoomReservation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class CinemaRoomTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    public function testGettersAndSetters(): void
    {
        $room = new CinemaRoom();
        $this->assertNull($room->getId());

        $room->setRows(5);
        $this->assertSame(5, $room->getRows());

        $room->setColumns(10);
        $this->assertSame(10, $room->getColumns());

        $room->setMovie('My Movie');
        $this->assertSame('My Movie', $room->getMovie());

        $dt = new \DateTimeImmutable('2025-12-01 20:00:00');
        $room->setMovieDatetime($dt);
        $this->assertSame($dt, $room->getMovieDatetime());

        $room->setCreation($dt);
        $this->assertSame($dt, $room->getCreation());

        $room->setUpdated($dt);
        $this->assertSame($dt, $room->getUpdated());
    }

    public function testReservationsCollection(): void
    {
        $room = new CinemaRoom();
        $room->setRows(2)->setColumns(2)->setMovie('X')->setMovieDatetime(new \DateTimeImmutable());

        $this->assertCount(0, $room->getReservations());

        $reservation = new CinemaRoomReservation();
        $reservation->setRow(1)->setColumn(1);
        $room->addReservation($reservation);
        $this->assertCount(1, $room->getReservations());
        $this->assertSame($room, $reservation->getCinemaRoom());

        $room->removeReservation($reservation);
        $this->assertCount(0, $room->getReservations());
    }

    public function testOnPrePersistSetsCreationAndUpdatedWhenNull(): void
    {
        $room = new CinemaRoom();
        $room->setRows(1)->setColumns(1)->setMovie('X')->setMovieDatetime(new \DateTimeImmutable());
        $this->assertNull($room->getCreation());
        $this->assertNull($room->getUpdated());

        $room->onPrePersist();

        $this->assertInstanceOf(\DateTimeImmutable::class, $room->getCreation());
        $this->assertInstanceOf(\DateTimeImmutable::class, $room->getUpdated());
    }

    public function testOnPrePersistDoesNotOverwriteExistingCreationAndUpdated(): void
    {
        $fixed = new \DateTimeImmutable('2020-01-01 12:00:00');
        $room = new CinemaRoom();
        $room->setRows(1)->setColumns(1)->setMovie('X')->setMovieDatetime(new \DateTimeImmutable())
            ->setCreation($fixed)->setUpdated($fixed);

        $room->onPrePersist();

        $this->assertSame($fixed, $room->getCreation());
        $this->assertSame($fixed, $room->getUpdated());
    }

    public function testOnPreUpdateSetsUpdated(): void
    {
        $old = new \DateTimeImmutable('2020-01-01 12:00:00');
        $room = new CinemaRoom();
        $room->setRows(1)->setColumns(1)->setMovie('X')->setMovieDatetime(new \DateTimeImmutable())
            ->setCreation($old)->setUpdated($old);

        $room->onPreUpdate();

        $this->assertSame($old, $room->getCreation());
        $this->assertNotSame($old, $room->getUpdated());
        $this->assertInstanceOf(\DateTimeImmutable::class, $room->getUpdated());
    }

    public function testValidRoomPassesValidation(): void
    {
        $room = new CinemaRoom();
        $room->setRows(5)->setColumns(10)->setMovie('Valid Movie')
            ->setMovieDatetime(new \DateTimeImmutable('2025-12-01 20:00:00'));

        $errors = $this->validator->validate($room);
        $this->assertCount(0, $errors);
    }

    public function testRowsOutOfRangeFailsValidation(): void
    {
        $room = new CinemaRoom();
        $room->setRows(0)->setColumns(10)->setMovie('X')
            ->setMovieDatetime(new \DateTimeImmutable());

        $errors = $this->validator->validate($room);
        $this->assertGreaterThan(0, $errors->count());
        $paths = array_map(fn ($e) => $e->getPropertyPath(), iterator_to_array($errors));
        $this->assertContains('rows', $paths);
    }

    public function testColumnsOutOfRangeFailsValidation(): void
    {
        $room = new CinemaRoom();
        $room->setRows(5)->setColumns(300)->setMovie('X')
            ->setMovieDatetime(new \DateTimeImmutable());

        $errors = $this->validator->validate($room);
        $this->assertGreaterThan(0, $errors->count());
        $paths = array_map(fn ($e) => $e->getPropertyPath(), iterator_to_array($errors));
        $this->assertContains('columns', $paths);
    }

    public function testNullMovieDatetimeFailsValidation(): void
    {
        $room = new CinemaRoom();
        $room->setRows(5)->setColumns(10)->setMovie('X');
        $this->assertNull($room->getMovieDatetime());

        $errors = $this->validator->validate($room);
        $this->assertGreaterThan(0, $errors->count());
        $paths = array_map(fn ($e) => $e->getPropertyPath(), iterator_to_array($errors));
        $this->assertContains('movieDatetime', $paths);
    }

    public function testDimensionsValidatorFailsWhenReservationExceedsNewRows(): void
    {
        $room = new CinemaRoom();
        $room->setRows(10)->setColumns(10)->setMovie('X')
            ->setMovieDatetime(new \DateTimeImmutable());

        $reservation = new CinemaRoomReservation();
        $reservation->setCinemaRoom($room)->setRow(5)->setColumn(5);
        $room->addReservation($reservation);

        $this->assertCount(0, $this->validator->validate($room));

        $room->setRows(3);
        $errors = $this->validator->validate($room);
        $this->assertGreaterThan(0, $errors->count());
        $paths = array_map(fn ($e) => $e->getPropertyPath(), iterator_to_array($errors));
        $this->assertContains('rows', $paths);
    }

    public function testDimensionsValidatorFailsWhenReservationExceedsNewColumns(): void
    {
        $room = new CinemaRoom();
        $room->setRows(10)->setColumns(10)->setMovie('X')
            ->setMovieDatetime(new \DateTimeImmutable());

        $reservation = new CinemaRoomReservation();
        $reservation->setCinemaRoom($room)->setRow(5)->setColumn(8);
        $room->addReservation($reservation);

        $room->setColumns(5);
        $errors = $this->validator->validate($room);
        $this->assertGreaterThan(0, $errors->count());
        $paths = array_map(fn ($e) => $e->getPropertyPath(), iterator_to_array($errors));
        $this->assertContains('columns', $paths);
    }
}
