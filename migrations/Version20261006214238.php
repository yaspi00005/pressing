<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261006214238 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commande (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, created_by_id INT DEFAULT NULL, numero VARCHAR(30) NOT NULL, date_depot DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_retrait_prevue DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_livraison DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', statut VARCHAR(20) NOT NULL, urgent TINYINT(1) NOT NULL, majoration_pourcent INT NOT NULL, remise INT NOT NULL, mode_livraison VARCHAR(20) NOT NULL, frais_livraison INT NOT NULL, adresse_livraison VARCHAR(255) DEFAULT NULL, notes LONGTEXT DEFAULT NULL, points_credites TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_6EEAA67DF55AE19E (numero), INDEX IDX_6EEAA67D19EB6921 (client_id), INDEX IDX_6EEAA67DB03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE depense (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(150) NOT NULL, categorie VARCHAR(30) NOT NULL, montant INT NOT NULL, date_depense DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', notes LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ligne_commande (id INT AUTO_INCREMENT NOT NULL, commande_id INT NOT NULL, article_id INT NOT NULL, service_id INT NOT NULL, quantite INT NOT NULL, prix_unitaire INT NOT NULL, observation VARCHAR(255) DEFAULT NULL, INDEX IDX_3170B74B82EA2E54 (commande_id), INDEX IDX_3170B74B7294869C (article_id), INDEX IDX_3170B74BED5CA9E6 (service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE paiement (id INT AUTO_INCREMENT NOT NULL, commande_id INT NOT NULL, created_by_id INT DEFAULT NULL, montant INT NOT NULL, mode VARCHAR(20) NOT NULL, reference VARCHAR(100) DEFAULT NULL, date_paiement DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_B1DC7A1E82EA2E54 (commande_id), INDEX IDX_B1DC7A1EB03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE produit_stock (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, unite VARCHAR(20) NOT NULL, quantite INT NOT NULL, seuil_alerte INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reclamation (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, commande_id INT DEFAULT NULL, objet VARCHAR(150) NOT NULL, description LONGTEXT NOT NULL, statut VARCHAR(20) NOT NULL, resolution LONGTEXT DEFAULT NULL, date_creation DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_CE60640419EB6921 (client_id), INDEX IDX_CE60640482EA2E54 (commande_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE service (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(60) NOT NULL, code VARCHAR(20) NOT NULL, actif TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_E19D9AD277153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tarif (id INT AUTO_INCREMENT NOT NULL, article_id INT NOT NULL, service_id INT NOT NULL, prix INT NOT NULL, INDEX IDX_E7189C97294869C (article_id), INDEX IDX_E7189C9ED5CA9E6 (service_id), UNIQUE INDEX UNIQ_E7189C97294869CED5CA9E6 (article_id, service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D19EB6921 FOREIGN KEY (client_id) REFERENCES clients (id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74B82EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74B7294869C FOREIGN KEY (article_id) REFERENCES articles_sous_categorie (id)');
        $this->addSql('ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74BED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
        $this->addSql('ALTER TABLE paiement ADD CONSTRAINT FK_B1DC7A1E82EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE paiement ADD CONSTRAINT FK_B1DC7A1EB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE60640419EB6921 FOREIGN KEY (client_id) REFERENCES clients (id)');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE60640482EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE tarif ADD CONSTRAINT FK_E7189C97294869C FOREIGN KEY (article_id) REFERENCES articles_sous_categorie (id)');
        $this->addSql('ALTER TABLE tarif ADD CONSTRAINT FK_E7189C9ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
        $this->addSql('ALTER TABLE clients ADD points_fidelite INT DEFAULT 0 NOT NULL, ADD notes LONGTEXT DEFAULT NULL, ADD created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D19EB6921');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DB03A8386');
        $this->addSql('ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74B82EA2E54');
        $this->addSql('ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74B7294869C');
        $this->addSql('ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74BED5CA9E6');
        $this->addSql('ALTER TABLE paiement DROP FOREIGN KEY FK_B1DC7A1E82EA2E54');
        $this->addSql('ALTER TABLE paiement DROP FOREIGN KEY FK_B1DC7A1EB03A8386');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE60640419EB6921');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE60640482EA2E54');
        $this->addSql('ALTER TABLE tarif DROP FOREIGN KEY FK_E7189C97294869C');
        $this->addSql('ALTER TABLE tarif DROP FOREIGN KEY FK_E7189C9ED5CA9E6');
        $this->addSql('DROP TABLE commande');
        $this->addSql('DROP TABLE depense');
        $this->addSql('DROP TABLE ligne_commande');
        $this->addSql('DROP TABLE paiement');
        $this->addSql('DROP TABLE produit_stock');
        $this->addSql('DROP TABLE reclamation');
        $this->addSql('DROP TABLE service');
        $this->addSql('DROP TABLE tarif');
        $this->addSql('ALTER TABLE clients DROP points_fidelite, DROP notes, DROP created_at');
    }
}
