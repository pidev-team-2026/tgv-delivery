<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260209235437 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE courier (id INT AUTO_INCREMENT NOT NULL, cin VARCHAR(20) NOT NULL, license_number VARCHAR(20) NOT NULL, vehicle_type VARCHAR(20) NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_CF134C7CA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE merchant (id INT AUTO_INCREMENT NOT NULL, cin VARCHAR(20) NOT NULL, business_name VARCHAR(100) NOT NULL, business_type VARCHAR(50) NOT NULL, location VARCHAR(150) NOT NULL, user_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_74AB25E1A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(50) NOT NULL, last_name VARCHAR(50) NOT NULL, email VARCHAR(100) NOT NULL, phone VARCHAR(20) DEFAULT NULL, paswword VARCHAR(255) NOT NULL, role VARCHAR(20) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE courier ADD CONSTRAINT FK_CF134C7CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE merchant ADD CONSTRAINT FK_74AB25E1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE courier DROP FOREIGN KEY FK_CF134C7CA76ED395');
        $this->addSql('ALTER TABLE merchant DROP FOREIGN KEY FK_74AB25E1A76ED395');
        $this->addSql('DROP TABLE courier');
        $this->addSql('DROP TABLE merchant');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
