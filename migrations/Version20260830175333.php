<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260830175333 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE action (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(20) NOT NULL, libelle VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, responsable_email VARCHAR(180) NOT NULL, responsable_nom VARCHAR(100) DEFAULT NULL, responsable_prenom VARCHAR(100) DEFAULT NULL, budget_prevu NUMERIC(14, 2) NOT NULL, budget_engage NUMERIC(14, 2) NOT NULL, budget_execute NUMERIC(14, 2) NOT NULL, date_debut DATE DEFAULT NULL, date_fin DATE DEFAULT NULL, statut VARCHAR(20) NOT NULL, actif TINYINT NOT NULL, programme_id INT NOT NULL, UNIQUE INDEX UNIQ_47CC8C9277153098 (code), INDEX IDX_47CC8C9262BB7AEE (programme_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE axe_strategique (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(20) NOT NULL, libelle VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, actif TINYINT NOT NULL, UNIQUE INDEX UNIQ_E5E4A5CF77153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE indicateur (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(20) NOT NULL, libelle VARCHAR(255) NOT NULL, unite VARCHAR(50) DEFAULT NULL, type_indicateur VARCHAR(20) NOT NULL, valeur_reference NUMERIC(14, 2) DEFAULT NULL, valeur_cible NUMERIC(14, 2) NOT NULL, periodicite VARCHAR(20) NOT NULL, programme_id INT DEFAULT NULL, action_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_7C663A2777153098 (code), INDEX IDX_7C663A2762BB7AEE (programme_id), INDEX IDX_7C663A279D32F035 (action_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE programme (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(20) NOT NULL, libelle VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, exercice_budgetaire INT NOT NULL, rprog_email VARCHAR(180) NOT NULL, rprog_nom VARCHAR(100) DEFAULT NULL, rprog_prenom VARCHAR(100) DEFAULT NULL, budget_prevu NUMERIC(14, 2) NOT NULL, budget_engage NUMERIC(14, 2) NOT NULL, budget_execute NUMERIC(14, 2) NOT NULL, date_debut DATE DEFAULT NULL, date_fin DATE DEFAULT NULL, statut VARCHAR(20) NOT NULL, actif TINYINT NOT NULL, axe_strategique_id INT NOT NULL, UNIQUE INDEX UNIQ_3DDCB9FF77153098 (code), INDEX IDX_3DDCB9FF88A57A65 (axe_strategique_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE realisation_indicateur (id INT AUTO_INCREMENT NOT NULL, periode VARCHAR(20) NOT NULL, valeur_realisee NUMERIC(14, 2) NOT NULL, date_saisie DATE NOT NULL, observations LONGTEXT DEFAULT NULL, saisi_par_email VARCHAR(180) DEFAULT NULL, indicateur_id INT NOT NULL, INDEX IDX_CF5D701DDA3B8F3D (indicateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sous_tache (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, responsable_email VARCHAR(180) NOT NULL, responsable_nom VARCHAR(100) DEFAULT NULL, responsable_prenom VARCHAR(100) DEFAULT NULL, date_debut DATE DEFAULT NULL, date_fin DATE DEFAULT NULL, statut VARCHAR(20) NOT NULL, avancement_pourcentage SMALLINT NOT NULL, actif TINYINT NOT NULL, tache_id INT NOT NULL, INDEX IDX_EC632090D2235D39 (tache_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tache (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, responsable_email VARCHAR(180) NOT NULL, responsable_nom VARCHAR(100) DEFAULT NULL, responsable_prenom VARCHAR(100) DEFAULT NULL, date_debut DATE DEFAULT NULL, date_fin DATE DEFAULT NULL, statut VARCHAR(20) NOT NULL, avancement_pourcentage SMALLINT NOT NULL, budget_prevu NUMERIC(14, 2) NOT NULL, budget_execute NUMERIC(14, 2) NOT NULL, actif TINYINT NOT NULL, action_id INT NOT NULL, INDEX IDX_938720759D32F035 (action_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE action ADD CONSTRAINT FK_47CC8C9262BB7AEE FOREIGN KEY (programme_id) REFERENCES programme (id)');
        $this->addSql('ALTER TABLE indicateur ADD CONSTRAINT FK_7C663A2762BB7AEE FOREIGN KEY (programme_id) REFERENCES programme (id)');
        $this->addSql('ALTER TABLE indicateur ADD CONSTRAINT FK_7C663A279D32F035 FOREIGN KEY (action_id) REFERENCES action (id)');
        $this->addSql('ALTER TABLE programme ADD CONSTRAINT FK_3DDCB9FF88A57A65 FOREIGN KEY (axe_strategique_id) REFERENCES axe_strategique (id)');
        $this->addSql('ALTER TABLE realisation_indicateur ADD CONSTRAINT FK_CF5D701DDA3B8F3D FOREIGN KEY (indicateur_id) REFERENCES indicateur (id)');
        $this->addSql('ALTER TABLE sous_tache ADD CONSTRAINT FK_EC632090D2235D39 FOREIGN KEY (tache_id) REFERENCES tache (id)');
        $this->addSql('ALTER TABLE tache ADD CONSTRAINT FK_938720759D32F035 FOREIGN KEY (action_id) REFERENCES action (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE action DROP FOREIGN KEY FK_47CC8C9262BB7AEE');
        $this->addSql('ALTER TABLE indicateur DROP FOREIGN KEY FK_7C663A2762BB7AEE');
        $this->addSql('ALTER TABLE indicateur DROP FOREIGN KEY FK_7C663A279D32F035');
        $this->addSql('ALTER TABLE programme DROP FOREIGN KEY FK_3DDCB9FF88A57A65');
        $this->addSql('ALTER TABLE realisation_indicateur DROP FOREIGN KEY FK_CF5D701DDA3B8F3D');
        $this->addSql('ALTER TABLE sous_tache DROP FOREIGN KEY FK_EC632090D2235D39');
        $this->addSql('ALTER TABLE tache DROP FOREIGN KEY FK_938720759D32F035');
        $this->addSql('DROP TABLE action');
        $this->addSql('DROP TABLE axe_strategique');
        $this->addSql('DROP TABLE indicateur');
        $this->addSql('DROP TABLE programme');
        $this->addSql('DROP TABLE realisation_indicateur');
        $this->addSql('DROP TABLE sous_tache');
        $this->addSql('DROP TABLE tache');
    }
}
