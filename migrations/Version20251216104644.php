<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251216104644 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client ADD COLUMN created_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__client AS SELECT id, nom, email, telephone, societe FROM client');
        $this->addSql('DROP TABLE client');
        $this->addSql('CREATE TABLE client (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, telephone VARCHAR(50) NOT NULL, societe VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO client (id, nom, email, telephone, societe) SELECT id, nom, email, telephone, societe FROM __temp__client');
        $this->addSql('DROP TABLE __temp__client');
    }
}
