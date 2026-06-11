<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260608212710 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ticket_history ADD changed_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ticket_history DROP changed_by');
        $this->addSql('ALTER TABLE ticket_history ADD CONSTRAINT FK_2B762919828AD0A0 FOREIGN KEY (changed_by_id) REFERENCES "user" (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_2B762919828AD0A0 ON ticket_history (changed_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ticket_history DROP CONSTRAINT FK_2B762919828AD0A0');
        $this->addSql('DROP INDEX IDX_2B762919828AD0A0');
        $this->addSql('ALTER TABLE ticket_history ADD changed_by VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE ticket_history DROP changed_by_id');
    }
}
