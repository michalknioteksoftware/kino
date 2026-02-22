<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250222120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create cinema_room table with id, rows, columns, movie, creation, updated, movie_datetime';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE cinema_room (
                id INT AUTO_INCREMENT NOT NULL,
                `rows` INT NOT NULL,
                `columns` INT NOT NULL,
                movie VARCHAR(255) NOT NULL,
                creation DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                `updated` DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                movie_datetime DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE cinema_room');
    }
}
