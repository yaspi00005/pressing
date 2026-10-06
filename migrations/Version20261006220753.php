<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261006220753 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // Adresses opaques : chaque catégorie / article reçoit un identifiant aléatoire unique.
        $this->addSql("UPDATE articles_categories SET url = SUBSTRING(MD5(CONCAT(id, UUID(), RAND())), 1, 16)");
        $this->addSql("UPDATE articles_sous_categorie SET url = SUBSTRING(MD5(CONCAT(id, UUID(), RAND())), 1, 16)");
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DE004A0EF47645AE ON articles_categories (url)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DDD5F8DFF47645AE ON articles_sous_categorie (url)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_DE004A0EF47645AE ON articles_categories');
        $this->addSql('DROP INDEX UNIQ_DDD5F8DFF47645AE ON articles_sous_categorie');
    }
}
