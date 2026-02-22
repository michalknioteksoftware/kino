<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250222170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add unique constraint on (cinema_room_id, seat_row, seat_column) for cinema_room_reservations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX cinema_room_seat_unique ON cinema_room_reservations (cinema_room_id, seat_row, seat_column)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX cinema_room_seat_unique ON cinema_room_reservations');
    }
}
