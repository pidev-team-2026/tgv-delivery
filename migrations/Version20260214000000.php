<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260214000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Contrat entity and migrate datedebutcontrat from Partenaire';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE contrat (id INT AUTO_INCREMENT NOT NULL, partenaire_id INT NOT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, notification_envoyee_at DATETIME DEFAULT NULL, INDEX IDX_contrat_partenaire (partenaire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contrat ADD CONSTRAINT FK_contrat_partenaire FOREIGN KEY (partenaire_id) REFERENCES partenaire (id)');
        
        $this->addSql('
            INSERT INTO contrat (partenaire_id, date_debut, date_fin)
            SELECT id, DATE(datedebutcontrat), DATE_ADD(DATE(datedebutcontrat), INTERVAL 1 YEAR)
            FROM partenaire
        ');
        
        $this->addSql('ALTER TABLE partenaire DROP COLUMN datedebutcontrat');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE partenaire ADD datedebutcontrat DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE contrat DROP FOREIGN KEY FK_contrat_partenaire');
        $this->addSql('DROP TABLE contrat');
    }
}
