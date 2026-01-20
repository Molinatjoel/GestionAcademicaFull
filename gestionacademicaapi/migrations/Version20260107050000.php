<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260107050000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add tipo, curso, creador to chat';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE chat ADD tipo VARCHAR(20) DEFAULT 'general' NOT NULL");
        $this->addSql('ALTER TABLE chat ADD id_curso INT DEFAULT NULL');
        $this->addSql('ALTER TABLE chat ADD id_creador INT DEFAULT NULL');
        $this->addSql('ALTER TABLE chat ADD CONSTRAINT FK_659DF2AA24BE01DC FOREIGN KEY (id_curso) REFERENCES curso (id_curso) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE chat ADD CONSTRAINT FK_659DF2AA4B09C2F5 FOREIGN KEY (id_creador) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_659DF2AA24BE01DC ON chat (id_curso)');
        $this->addSql('CREATE INDEX IDX_659DF2AA4B09C2F5 ON chat (id_creador)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chat DROP CONSTRAINT FK_659DF2AA24BE01DC');
        $this->addSql('ALTER TABLE chat DROP CONSTRAINT FK_659DF2AA4B09C2F5');
        $this->addSql('DROP INDEX IDX_659DF2AA24BE01DC');
        $this->addSql('DROP INDEX IDX_659DF2AA4B09C2F5');
        $this->addSql('ALTER TABLE chat DROP tipo');
        $this->addSql('ALTER TABLE chat DROP id_curso');
        $this->addSql('ALTER TABLE chat DROP id_creador');
    }
}
