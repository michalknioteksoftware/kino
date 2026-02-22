<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity]
#[ORM\Table(name: 'cinema_room_reservations')]
#[ORM\HasLifecycleCallbacks]
#[Assert\Callback('validateSeatWithinRoom')]
class CinemaRoomReservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: CinemaRoom::class)]
    #[ORM\JoinColumn(name: 'cinema_room_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    private ?CinemaRoom $cinemaRoom = null;

    #[ORM\Column(name: 'seat_row', type: Types::INTEGER)]
    #[Assert\NotNull]
    #[Assert\Range(min: 1, minMessage: 'Row must be at least 1.')]
    private ?int $row = null;

    #[ORM\Column(name: 'seat_column', type: Types::INTEGER)]
    #[Assert\NotNull]
    #[Assert\Range(min: 1, minMessage: 'Column must be at least 1.')]
    private ?int $column = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $creation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCinemaRoom(): ?CinemaRoom
    {
        return $this->cinemaRoom;
    }

    public function setCinemaRoom(?CinemaRoom $cinemaRoom): static
    {
        $this->cinemaRoom = $cinemaRoom;
        return $this;
    }

    public function getRow(): ?int
    {
        return $this->row;
    }

    public function setRow(int $row): static
    {
        $this->row = $row;
        return $this;
    }

    public function getColumn(): ?int
    {
        return $this->column;
    }

    public function setColumn(int $column): static
    {
        $this->column = $column;
        return $this;
    }

    public function getCreation(): ?\DateTimeImmutable
    {
        return $this->creation;
    }

    public function setCreation(\DateTimeImmutable $creation): static
    {
        $this->creation = $creation;
        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        if ($this->creation === null) {
            $this->creation = new \DateTimeImmutable();
        }
    }

    public function validateSeatWithinRoom(ExecutionContextInterface $context): void
    {
        if ($this->cinemaRoom === null || $this->row === null || $this->column === null) {
            return;
        }

        $maxRows = $this->cinemaRoom->getRows();
        $maxColumns = $this->cinemaRoom->getColumns();

        if ($this->row > $maxRows) {
            $context->buildViolation('Row {{ value }} exceeds room limit of {{ limit }} rows.')
                ->setParameter('{{ value }}', (string) $this->row)
                ->setParameter('{{ limit }}', (string) $maxRows)
                ->atPath('row')
                ->addViolation();
        }

        if ($this->column > $maxColumns) {
            $context->buildViolation('Column {{ value }} exceeds room limit of {{ limit }} columns.')
                ->setParameter('{{ value }}', (string) $this->column)
                ->setParameter('{{ limit }}', (string) $maxColumns)
                ->atPath('column')
                ->addViolation();
        }
    }
}
