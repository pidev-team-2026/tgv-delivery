<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260220201704 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE commande ADD code_promo VARCHAR(50) DEFAULT NULL, ADD remise DOUBLE PRECISION DEFAULT NULL, ADD gouvernorat VARCHAR(100) DEFAULT NULL, ADD estimation_livraison INT DEFAULT NULL');
        $this->addSql('ALTER TABLE produit DROP poids, DROP nouveaute, DROP meilleute_vente, DROP delai_preparation');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE commande DROP code_promo, DROP remise, DROP gouvernorat, DROP estimation_livraison');
        $this->addSql('ALTER TABLE produit ADD poids DOUBLE PRECISION DEFAULT NULL, ADD nouveaute TINYINT(1) NOT NULL, ADD meilleute_vente TINYINT(1) NOT NULL, ADD delai_preparation INT DEFAULT NULL');
    }
}
