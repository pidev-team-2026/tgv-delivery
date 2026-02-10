<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260206231744 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande ADD nom_client VARCHAR(255) DEFAULT NULL, ADD telephone_client VARCHAR(20) DEFAULT NULL, ADD adresse_livraison VARCHAR(500) DEFAULT NULL, ADD notes LONGTEXT DEFAULT NULL, ADD reduction DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE produit ADD image_url VARCHAR(255) DEFAULT NULL, ADD description_longue LONGTEXT DEFAULT NULL, ADD categorie VARCHAR(100) DEFAULT NULL, ADD marque VARCHAR(100) DEFAULT NULL, ADD remise DOUBLE PRECISION DEFAULT NULL, ADD poids DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande DROP nom_client, DROP telephone_client, DROP adresse_livraison, DROP notes, DROP reduction');
        $this->addSql('ALTER TABLE produit DROP image_url, DROP description_longue, DROP categorie, DROP marque, DROP remise, DROP poids');
    }
}
