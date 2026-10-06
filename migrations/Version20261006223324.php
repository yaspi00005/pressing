<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261006223324 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE journal (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, action VARCHAR(30) NOT NULL, cible VARCHAR(30) DEFAULT NULL, cible_id INT DEFAULT NULL, libelle VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_C1A7E74DA76ED395 (user_id), INDEX IDX_C1A7E74DE15DEC3BA96E5E09 (cible, cible_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE journal ADD CONSTRAINT FK_C1A7E74DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE commande ADD total INT DEFAULT 0 NOT NULL, ADD montant_paye INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE user ADD actif TINYINT(1) DEFAULT 1 NOT NULL');

        // Reprise des données existantes : montants mémorisés des commandes…
        $this->addSql('UPDATE commande c SET c.total = GREATEST(0,
            COALESCE((SELECT SUM(l.quantite * l.prix_unitaire) FROM ligne_commande l WHERE l.commande_id = c.id), 0)
            + ROUND(COALESCE((SELECT SUM(l.quantite * l.prix_unitaire) FROM ligne_commande l WHERE l.commande_id = c.id), 0) * c.majoration_pourcent / 100)
            + c.frais_livraison - c.remise)');
        $this->addSql('UPDATE commande c SET c.montant_paye = COALESCE((SELECT SUM(p.montant) FROM paiement p WHERE p.commande_id = c.id), 0)');
        // … et rôles : avant l'introduction des rôles, tout compte avait un accès complet.
        $this->addSql("UPDATE user SET roles = '[\"ROLE_ADMIN\"]' WHERE roles = '[]' OR roles = '' OR roles IS NULL");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE journal DROP FOREIGN KEY FK_C1A7E74DA76ED395');
        $this->addSql('DROP TABLE journal');
        $this->addSql('ALTER TABLE commande DROP total, DROP montant_paye');
        $this->addSql('ALTER TABLE user DROP actif');
    }
}
