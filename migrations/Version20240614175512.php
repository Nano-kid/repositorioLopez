<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240614175512 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lineas_venta CHANGE descuento descuento DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE lineas_venta ADD CONSTRAINT FK_D112768A7645698E FOREIGN KEY (producto_id) REFERENCES producto (id)');
        $this->addSql('CREATE INDEX IDX_D112768A7645698E ON lineas_venta (producto_id)');
        $this->addSql('ALTER TABLE producto CHANGE descuento descuento DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lineas_venta DROP FOREIGN KEY FK_D112768A7645698E');
        $this->addSql('DROP INDEX IDX_D112768A7645698E ON lineas_venta');
        $this->addSql('ALTER TABLE lineas_venta CHANGE descuento descuento DOUBLE PRECISION DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\' COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE producto CHANGE descuento descuento DOUBLE PRECISION DEFAULT \'NULL\'');
    }
}
