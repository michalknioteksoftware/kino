<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250222130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create cinema_room_reservations table with FK to cinema_room and seat_row, seat_column';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE cinema_room_reservations (
                id INT AUTO_INCREMENT NOT NULL,
                cinema_room_id INT NOT NULL,
                seat_row INT NOT NULL,
                seat_column INT NOT NULL,
                INDEX IDX_CINEMA_ROOM_RESERVATIONS_CINEMA_ROOM (cinema_room_id),
                PRIMARY KEY(id),
                CONSTRAINT FK_CINEMA_ROOM_RESERVATIONS_CINEMA_ROOM
                    FOREIGN KEY (cinema_room_id) REFERENCES cinema_room (id) ON DELETE CASCADE
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE cinema_room_reservations');
    }
}
