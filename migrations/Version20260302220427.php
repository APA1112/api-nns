<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260302220427 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE service_ftth ALTER ont_mac DROP NOT NULL');
        $this->addSql('ALTER TABLE service_ftth ALTER pon_port DROP NOT NULL');
        $this->addSql('ALTER TABLE service_ftth ALTER splitter_id DROP NOT NULL');
        $this->addSql('ALTER TABLE service_ftth ALTER optical_power DROP NOT NULL');
        $this->addSql('ALTER TABLE service_wimax ALTER antenna_ip DROP NOT NULL');
        $this->addSql('ALTER TABLE service_wimax ALTER antenna_mac DROP NOT NULL');
        $this->addSql('ALTER TABLE service_wimax ALTER ap_name DROP NOT NULL');
        $this->addSql('ALTER TABLE service_wimax ALTER signal_strength DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE service_ftth ALTER ont_mac SET NOT NULL');
        $this->addSql('ALTER TABLE service_ftth ALTER pon_port SET NOT NULL');
        $this->addSql('ALTER TABLE service_ftth ALTER splitter_id SET NOT NULL');
        $this->addSql('ALTER TABLE service_ftth ALTER optical_power SET NOT NULL');
        $this->addSql('ALTER TABLE service_wimax ALTER antenna_ip SET NOT NULL');
        $this->addSql('ALTER TABLE service_wimax ALTER antenna_mac SET NOT NULL');
        $this->addSql('ALTER TABLE service_wimax ALTER ap_name SET NOT NULL');
        $this->addSql('ALTER TABLE service_wimax ALTER signal_strength SET NOT NULL');
    }
}
