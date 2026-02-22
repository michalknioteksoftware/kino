<?php

declare(strict_types=1);

namespace App\Entity;

use App\Validator\CinemaRoomDimensions;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'cinema_room')]
#[ORM\HasLifecycleCallbacks]
#[CinemaRoomDimensions]
class CinemaRoom
{
    /** @var \Doctrine\Common\Collections\Collection<int, CinemaRoomReservation> */
    #[ORM\OneToMany(targetEntity: CinemaRoomReservation::class, mappedBy: 'cinemaRoom', cascade: ['persist', 'remove'])]
    private \Doctrine\Common\Collections\Collection $reservations;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(name: 'seat_rows', type: Types::INTEGER)]
    #[Assert\Range(min: 1, max: 255)]
    private int $rows;

    #[ORM\Column(name: 'seat_columns', type: Types::INTEGER)]
    #[Assert\Range(min: 1, max: 255)]
    private int $columns;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $movie = '';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $creation = null;

    #[ORM\Column(name: 'updated', type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updated = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Assert\NotNull(message: 'Movie date/time is required.')]
    private ?\DateTimeImmutable $movieDatetime = null;

    public function __construct()
    {
        $this->reservations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRows(): int
    {
        return $this->rows;
    }

    public function setRows(int $rows): static
    {
        $this->rows = $rows;
        return $this;
    }

    public function getColumns(): int
    {
        return $this->columns;
    }

    public function setColumns(int $columns): static
    {
        $this->columns = $columns;
        return $this;
    }

    public function getMovie(): string
    {
        return $this->movie;
    }

    public function setMovie(string $movie): static
    {
        $this->movie = $movie;
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

    public function getUpdated(): ?\DateTimeImmutable
    {
        return $this->updated;
    }

    public function setUpdated(\DateTimeImmutable $updated): static
    {
        $this->updated = $updated;
        return $this;
    }

    public function getMovieDatetime(): ?\DateTimeImmutable
    {
        return $this->movieDatetime;
    }

    public function setMovieDatetime(?\DateTimeImmutable $movieDatetime): static
    {
        $this->movieDatetime = $movieDatetime;
        return $this;
    }

    /** @return \Doctrine\Common\Collections\Collection<int, CinemaRoomReservation> */
    public function getReservations(): \Doctrine\Common\Collections\Collection
    {
        return $this->reservations;
    }

    public function addReservation(CinemaRoomReservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setCinemaRoom($this);
        }
        return $this;
    }

    public function removeReservation(CinemaRoomReservation $reservation): static
    {
        $this->reservations->removeElement($reservation);
        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $now = new \DateTimeImmutable();
        if ($this->creation === null) {
            $this->creation = $now;
        }
        if ($this->updated === null) {
            $this->updated = $now;
        }
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updated = new \DateTimeImmutable();
    }
}
