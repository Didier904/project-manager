<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251217153733 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE message (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, content CLOB NOT NULL, created_at DATETIME NOT NULL, sender_id INTEGER NOT NULL, CONSTRAINT FK_B6BD307FF624B39D FOREIGN KEY (sender_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_B6BD307FF624B39D ON message (sender_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__client AS SELECT id, nom, email, telephone, societe, created_at, user_id FROM client');
        $this->addSql('DROP TABLE client');
        $this->addSql('CREATE TABLE client (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, telephone VARCHAR(50) NOT NULL, societe VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, user_id INTEGER DEFAULT NULL, CONSTRAINT FK_C7440455A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO client (id, nom, email, telephone, societe, created_at, user_id) SELECT id, nom, email, telephone, societe, created_at, user_id FROM __temp__client');
        $this->addSql('DROP TABLE __temp__client');
        $this->addSql('CREATE INDEX IDX_C7440455A76ED395 ON client (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE message');
        $this->addSql('CREATE TEMPORARY TABLE __temp__client AS SELECT id, nom, email, telephone, societe, created_at, user_id FROM client');
        $this->addSql('DROP TABLE client');
        $this->addSql('CREATE TABLE client (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, telephone VARCHAR(50) NOT NULL, societe VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, user_id INTEGER DEFAULT NULL, CONSTRAINT FK_C7440455A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO client (id, nom, email, telephone, societe, created_at, user_id) SELECT id, nom, email, telephone, societe, created_at, user_id FROM __temp__client');
        $this->addSql('DROP TABLE __temp__client');
        $this->addSql('CREATE INDEX IDX_C7440455A76ED395 ON client (user_id)');
    }
}
