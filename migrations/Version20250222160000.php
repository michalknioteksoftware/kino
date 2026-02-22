<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250222160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename cinema_room rows/columns to seat_rows/seat_columns (MySQL reserved words fix)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE cinema_room
                CHANGE COLUMN `rows` seat_rows INT NOT NULL,
                CHANGE COLUMN `columns` seat_columns INT NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE cinema_room
                CHANGE COLUMN seat_rows `rows` INT NOT NULL,
                CHANGE COLUMN seat_columns `columns` INT NOT NULL
        SQL);
    }
}
