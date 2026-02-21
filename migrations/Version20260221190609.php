<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260221190609 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE livreur (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, telephone VARCHAR(20) NOT NULL, email VARCHAR(180) DEFAULT NULL, statut VARCHAR(20) DEFAULT \'disponible\' NOT NULL, type VARCHAR(20) DEFAULT \'propre\' NOT NULL, vehicule VARCHAR(20) DEFAULT NULL, immatriculation VARCHAR(20) DEFAULT NULL, zones_couvertes VARCHAR(255) DEFAULT NULL, societe_partenaire VARCHAR(150) DEFAULT NULL, photo VARCHAR(255) DEFAULT NULL, note DOUBLE PRECISION DEFAULT NULL, nombre_livraisons INT DEFAULT 0 NOT NULL, date_creation DATETIME NOT NULL, date_modification DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE commande ADD livreur_id INT DEFAULT NULL, DROP livreur, CHANGE remise remise DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DF8646701 FOREIGN KEY (livreur_id) REFERENCES livreur (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_6EEAA67DF8646701 ON commande (livreur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE livreur');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DF8646701');
        $this->addSql('DROP INDEX IDX_6EEAA67DF8646701 ON commande');
        $this->addSql('ALTER TABLE commande ADD livreur VARCHAR(100) DEFAULT NULL, DROP livreur_id, CHANGE remise remise DOUBLE PRECISION DEFAULT \'0\'');
    }
}
