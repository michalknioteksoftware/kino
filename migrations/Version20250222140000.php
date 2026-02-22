<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250222140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add creation (datetime) to cinema_room_reservations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE cinema_room_reservations
            ADD creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '(DC2Type:datetime_immutable)'
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cinema_room_reservations DROP creation');
    }
}
