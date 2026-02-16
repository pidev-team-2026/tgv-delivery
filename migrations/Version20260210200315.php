<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260210200315 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande ADD nom_client VARCHAR(100) NOT NULL, ADD telephone VARCHAR(20) NOT NULL, ADD email VARCHAR(255) DEFAULT NULL, ADD adresse_livraison LONGTEXT NOT NULL, ADD ville VARCHAR(100) NOT NULL, ADD code_postal VARCHAR(10) NOT NULL, ADD mode_paiement VARCHAR(50) NOT NULL, ADD paiement_effectue TINYINT(1) NOT NULL, ADD frais_livraison DOUBLE PRECISION DEFAULT NULL, ADD notes LONGTEXT DEFAULT NULL, ADD date_livraison_souhaitee DATETIME DEFAULT NULL, ADD date_livraison_effective DATETIME DEFAULT NULL, ADD livreur VARCHAR(100) DEFAULT NULL, DROP id_commande, CHANGE statut statut VARCHAR(50) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6EEAA67DAEA34913 ON commande (reference)');
        $this->addSql('ALTER TABLE produit ADD image VARCHAR(255) DEFAULT NULL, ADD categorie VARCHAR(100) NOT NULL, ADD poids DOUBLE PRECISION DEFAULT NULL, ADD promotion INT DEFAULT NULL, ADD nouveaute TINYINT(1) NOT NULL, ADD meilleute_vente TINYINT(1) NOT NULL, ADD delai_preparation INT DEFAULT NULL, DROP id_prod, CHANGE description description LONGTEXT NOT NULL, CHANGE statut statut VARCHAR(50) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_6EEAA67DAEA34913 ON commande');
        $this->addSql('ALTER TABLE commande ADD id_commande INT NOT NULL, DROP nom_client, DROP telephone, DROP email, DROP adresse_livraison, DROP ville, DROP code_postal, DROP mode_paiement, DROP paiement_effectue, DROP frais_livraison, DROP notes, DROP date_livraison_souhaitee, DROP date_livraison_effective, DROP livreur, CHANGE statut statut VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE produit ADD id_prod INT NOT NULL, DROP image, DROP categorie, DROP poids, DROP promotion, DROP nouveaute, DROP meilleute_vente, DROP delai_preparation, CHANGE description description VARCHAR(255) NOT NULL, CHANGE statut statut VARCHAR(255) NOT NULL');
    }
}
