<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250222180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add reserved_by_name to cinema_room_reservations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cinema_room_reservations ADD reserved_by_name VARCHAR(255) NOT NULL DEFAULT \'\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cinema_room_reservations DROP reserved_by_name');
    }
}
