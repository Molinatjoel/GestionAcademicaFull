<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260107040435 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE asignatura (id_asignatura SERIAL NOT NULL, nombre_asignatura VARCHAR(80) NOT NULL, descripcion VARCHAR(120) NOT NULL, PRIMARY KEY(id_asignatura))');
        $this->addSql('CREATE TABLE calificacion (id_calificacion SERIAL NOT NULL, id_matricula INT DEFAULT NULL, id_curso_asignatura INT DEFAULT NULL, nota NUMERIC(4, 2) NOT NULL, observacion VARCHAR(150) NOT NULL, fecha_registro TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id_calificacion))');
        $this->addSql('CREATE INDEX IDX_8A3AF21895EAA4A2 ON calificacion (id_matricula)');
        $this->addSql('CREATE INDEX IDX_8A3AF218E6154057 ON calificacion (id_curso_asignatura)');
        $this->addSql('CREATE TABLE chat (id_chat SERIAL NOT NULL, titulo VARCHAR(100) NOT NULL, fecha_creacion TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id_chat))');
        $this->addSql('CREATE TABLE chat_user (id_chat_user SERIAL NOT NULL, id_chat INT DEFAULT NULL, id_user INT DEFAULT NULL, fecha_union TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id_chat_user))');
        $this->addSql('CREATE INDEX IDX_2B0F4B08EEBDEEA8 ON chat_user (id_chat)');
        $this->addSql('CREATE INDEX IDX_2B0F4B086B3CA4B ON chat_user (id_user)');
        $this->addSql('CREATE TABLE curso (id_curso SERIAL NOT NULL, id_docente_titular INT DEFAULT NULL, nombre_curso VARCHAR(60) NOT NULL, nivel VARCHAR(30) NOT NULL, fecha_creacion TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, estado BOOLEAN NOT NULL, PRIMARY KEY(id_curso))');
        $this->addSql('CREATE INDEX IDX_CA3B40ECACF30F5C ON curso (id_docente_titular)');
        $this->addSql('CREATE TABLE curso_asignatura (id_curso_asignatura SERIAL NOT NULL, id_curso INT DEFAULT NULL, id_asignatura INT DEFAULT NULL, id_docente INT DEFAULT NULL, fecha_creacion TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id_curso_asignatura))');
        $this->addSql('CREATE INDEX IDX_59F158624BE01DC ON curso_asignatura (id_curso)');
        $this->addSql('CREATE INDEX IDX_59F158637C95619 ON curso_asignatura (id_asignatura)');
        $this->addSql('CREATE INDEX IDX_59F1586230266D4 ON curso_asignatura (id_docente)');
        $this->addSql('CREATE TABLE datos_familiares (id_datos_familiares SERIAL NOT NULL, id_estudiante INT DEFAULT NULL, id_representante_user INT DEFAULT NULL, nombre_padre VARCHAR(80) NOT NULL, telefono_padre VARCHAR(20) NOT NULL, nombre_madre VARCHAR(80) NOT NULL, telefono_madre VARCHAR(20) NOT NULL, direccion_familiar VARCHAR(120) NOT NULL, parentesco_representante VARCHAR(50) NOT NULL, nombre_representante VARCHAR(80) NOT NULL, ocupacion_representante VARCHAR(80) NOT NULL, telefono_representante VARCHAR(20) NOT NULL, PRIMARY KEY(id_datos_familiares))');
        $this->addSql('CREATE INDEX IDX_B48049209EB5BF7A ON datos_familiares (id_estudiante)');
        $this->addSql('CREATE INDEX IDX_B480492044308FB5 ON datos_familiares (id_representante_user)');
        $this->addSql('CREATE TABLE matricula (id_matricula SERIAL NOT NULL, id_estudiante INT DEFAULT NULL, id_curso INT DEFAULT NULL, id_periodo INT DEFAULT NULL, fecha_matricula DATE NOT NULL, estado BOOLEAN NOT NULL, PRIMARY KEY(id_matricula))');
        $this->addSql('CREATE INDEX IDX_15DF18859EB5BF7A ON matricula (id_estudiante)');
        $this->addSql('CREATE INDEX IDX_15DF188524BE01DC ON matricula (id_curso)');
        $this->addSql('CREATE INDEX IDX_15DF1885AD8B6D9D ON matricula (id_periodo)');
        $this->addSql('CREATE TABLE mensaje (id_mensaje SERIAL NOT NULL, id_chat INT DEFAULT NULL, id_emisor INT DEFAULT NULL, contenido VARCHAR(500) NOT NULL, fecha_envio TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id_mensaje))');
        $this->addSql('CREATE INDEX IDX_9B631D01EEBDEEA8 ON mensaje (id_chat)');
        $this->addSql('CREATE INDEX IDX_9B631D01E29930A3 ON mensaje (id_emisor)');
        $this->addSql('CREATE TABLE periodo_lectivo (id_periodo SERIAL NOT NULL, descripcion VARCHAR(100) NOT NULL, fecha_inicio DATE NOT NULL, fecha_fin DATE NOT NULL, estado BOOLEAN NOT NULL, PRIMARY KEY(id_periodo))');
        $this->addSql('CREATE TABLE reporte (id_reporte SERIAL NOT NULL, id_curso INT DEFAULT NULL, id_docente INT DEFAULT NULL, id_periodo INT DEFAULT NULL, titulo VARCHAR(100) NOT NULL, descripcion TEXT NOT NULL, tipo VARCHAR(40) NOT NULL, fecha_creacion TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id_reporte))');
        $this->addSql('CREATE INDEX IDX_5CB121424BE01DC ON reporte (id_curso)');
        $this->addSql('CREATE INDEX IDX_5CB1214230266D4 ON reporte (id_docente)');
        $this->addSql('CREATE INDEX IDX_5CB1214AD8B6D9D ON reporte (id_periodo)');
        $this->addSql('CREATE TABLE rol (id_rol SERIAL NOT NULL, nombre_rol VARCHAR(50) NOT NULL, descripcion VARCHAR(120) NOT NULL, PRIMARY KEY(id_rol))');
        $this->addSql('CREATE TABLE "user" (id SERIAL NOT NULL, nombres VARCHAR(80) NOT NULL, apellidos VARCHAR(80) NOT NULL, fecha_nacimiento DATE NOT NULL, direccion VARCHAR(120) NOT NULL, correo VARCHAR(120) NOT NULL, password VARCHAR(255) NOT NULL, telefono VARCHAR(10) NOT NULL, estado BOOLEAN NOT NULL, fecha_creacion TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, fecha_actualizacion TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D64977040BC9 ON "user" (correo)');
        $this->addSql('CREATE TABLE user_rol (id_user_rol SERIAL NOT NULL, id_user INT DEFAULT NULL, id_rol INT DEFAULT NULL, PRIMARY KEY(id_user_rol))');
        $this->addSql('CREATE INDEX IDX_E5435EBC6B3CA4B ON user_rol (id_user)');
        $this->addSql('CREATE INDEX IDX_E5435EBC90F1D76D ON user_rol (id_rol)');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE calificacion ADD CONSTRAINT FK_8A3AF21895EAA4A2 FOREIGN KEY (id_matricula) REFERENCES matricula (id_matricula) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE calificacion ADD CONSTRAINT FK_8A3AF218E6154057 FOREIGN KEY (id_curso_asignatura) REFERENCES curso_asignatura (id_curso_asignatura) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE chat_user ADD CONSTRAINT FK_2B0F4B08EEBDEEA8 FOREIGN KEY (id_chat) REFERENCES chat (id_chat) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE chat_user ADD CONSTRAINT FK_2B0F4B086B3CA4B FOREIGN KEY (id_user) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE curso ADD CONSTRAINT FK_CA3B40ECACF30F5C FOREIGN KEY (id_docente_titular) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE curso_asignatura ADD CONSTRAINT FK_59F158624BE01DC FOREIGN KEY (id_curso) REFERENCES curso (id_curso) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE curso_asignatura ADD CONSTRAINT FK_59F158637C95619 FOREIGN KEY (id_asignatura) REFERENCES asignatura (id_asignatura) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE curso_asignatura ADD CONSTRAINT FK_59F1586230266D4 FOREIGN KEY (id_docente) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE datos_familiares ADD CONSTRAINT FK_B48049209EB5BF7A FOREIGN KEY (id_estudiante) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE datos_familiares ADD CONSTRAINT FK_B480492044308FB5 FOREIGN KEY (id_representante_user) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE matricula ADD CONSTRAINT FK_15DF18859EB5BF7A FOREIGN KEY (id_estudiante) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE matricula ADD CONSTRAINT FK_15DF188524BE01DC FOREIGN KEY (id_curso) REFERENCES curso (id_curso) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE matricula ADD CONSTRAINT FK_15DF1885AD8B6D9D FOREIGN KEY (id_periodo) REFERENCES periodo_lectivo (id_periodo) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mensaje ADD CONSTRAINT FK_9B631D01EEBDEEA8 FOREIGN KEY (id_chat) REFERENCES chat (id_chat) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mensaje ADD CONSTRAINT FK_9B631D01E29930A3 FOREIGN KEY (id_emisor) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reporte ADD CONSTRAINT FK_5CB121424BE01DC FOREIGN KEY (id_curso) REFERENCES curso (id_curso) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reporte ADD CONSTRAINT FK_5CB1214230266D4 FOREIGN KEY (id_docente) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reporte ADD CONSTRAINT FK_5CB1214AD8B6D9D FOREIGN KEY (id_periodo) REFERENCES periodo_lectivo (id_periodo) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_rol ADD CONSTRAINT FK_E5435EBC6B3CA4B FOREIGN KEY (id_user) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_rol ADD CONSTRAINT FK_E5435EBC90F1D76D FOREIGN KEY (id_rol) REFERENCES rol (id_rol) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE calificacion DROP CONSTRAINT FK_8A3AF21895EAA4A2');
        $this->addSql('ALTER TABLE calificacion DROP CONSTRAINT FK_8A3AF218E6154057');
        $this->addSql('ALTER TABLE chat_user DROP CONSTRAINT FK_2B0F4B08EEBDEEA8');
        $this->addSql('ALTER TABLE chat_user DROP CONSTRAINT FK_2B0F4B086B3CA4B');
        $this->addSql('ALTER TABLE curso DROP CONSTRAINT FK_CA3B40ECACF30F5C');
        $this->addSql('ALTER TABLE curso_asignatura DROP CONSTRAINT FK_59F158624BE01DC');
        $this->addSql('ALTER TABLE curso_asignatura DROP CONSTRAINT FK_59F158637C95619');
        $this->addSql('ALTER TABLE curso_asignatura DROP CONSTRAINT FK_59F1586230266D4');
        $this->addSql('ALTER TABLE datos_familiares DROP CONSTRAINT FK_B48049209EB5BF7A');
        $this->addSql('ALTER TABLE datos_familiares DROP CONSTRAINT FK_B480492044308FB5');
        $this->addSql('ALTER TABLE matricula DROP CONSTRAINT FK_15DF18859EB5BF7A');
        $this->addSql('ALTER TABLE matricula DROP CONSTRAINT FK_15DF188524BE01DC');
        $this->addSql('ALTER TABLE matricula DROP CONSTRAINT FK_15DF1885AD8B6D9D');
        $this->addSql('ALTER TABLE mensaje DROP CONSTRAINT FK_9B631D01EEBDEEA8');
        $this->addSql('ALTER TABLE mensaje DROP CONSTRAINT FK_9B631D01E29930A3');
        $this->addSql('ALTER TABLE reporte DROP CONSTRAINT FK_5CB121424BE01DC');
        $this->addSql('ALTER TABLE reporte DROP CONSTRAINT FK_5CB1214230266D4');
        $this->addSql('ALTER TABLE reporte DROP CONSTRAINT FK_5CB1214AD8B6D9D');
        $this->addSql('ALTER TABLE user_rol DROP CONSTRAINT FK_E5435EBC6B3CA4B');
        $this->addSql('ALTER TABLE user_rol DROP CONSTRAINT FK_E5435EBC90F1D76D');
        $this->addSql('DROP TABLE asignatura');
        $this->addSql('DROP TABLE calificacion');
        $this->addSql('DROP TABLE chat');
        $this->addSql('DROP TABLE chat_user');
        $this->addSql('DROP TABLE curso');
        $this->addSql('DROP TABLE curso_asignatura');
        $this->addSql('DROP TABLE datos_familiares');
        $this->addSql('DROP TABLE matricula');
        $this->addSql('DROP TABLE mensaje');
        $this->addSql('DROP TABLE periodo_lectivo');
        $this->addSql('DROP TABLE reporte');
        $this->addSql('DROP TABLE rol');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE user_rol');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
