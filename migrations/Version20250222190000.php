<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250222190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Enforce reserved_by_name cannot be empty (CHECK constraint)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE cinema_room_reservations SET reserved_by_name = 'John Doe' WHERE reserved_by_name = '' OR TRIM(reserved_by_name) = ''");
        $this->addSql('ALTER TABLE cinema_room_reservations ADD CONSTRAINT chk_reserved_by_name_not_empty CHECK (TRIM(reserved_by_name) != \'\')');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cinema_room_reservations DROP CONSTRAINT chk_reserved_by_name_not_empty');
    }
}
