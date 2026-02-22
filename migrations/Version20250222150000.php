<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250222150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make cinema_room.movie_datetime NOT NULL (required)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE cinema_room SET movie_datetime = creation WHERE movie_datetime IS NULL');
        $this->addSql(<<<'SQL'
            ALTER TABLE cinema_room
            MODIFY movie_datetime DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE cinema_room
            MODIFY movie_datetime DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
    }
}
