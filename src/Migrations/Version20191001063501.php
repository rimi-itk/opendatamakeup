<?php

declare(strict_types=1);

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191001063501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE data_transform (id INT AUTO_INCREMENT NOT NULL, data_wrangler_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, transformer VARCHAR(255) NOT NULL, transformer_arguments LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', position INT NOT NULL, INDEX IDX_F81DC19B758EA5F (data_wrangler_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fos_user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', UNIQUE INDEX UNIQ_957A647992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_957A6479A0D96FBF (email_canonical), UNIQUE INDEX UNIQ_957A6479C05FB297 (confirmation_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE data_source (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, url VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE data_wrangler (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE data_wrangler_data_source (data_wrangler_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', data_source_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_F3ABC7F9758EA5F (data_wrangler_id), INDEX IDX_F3ABC7F91A935C57 (data_source_id), PRIMARY KEY(data_wrangler_id, data_source_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE data_transform ADD CONSTRAINT FK_F81DC19B758EA5F FOREIGN KEY (data_wrangler_id) REFERENCES data_wrangler (id)');
        $this->addSql('ALTER TABLE data_wrangler_data_source ADD CONSTRAINT FK_F3ABC7F9758EA5F FOREIGN KEY (data_wrangler_id) REFERENCES data_wrangler (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE data_wrangler_data_source ADD CONSTRAINT FK_F3ABC7F91A935C57 FOREIGN KEY (data_source_id) REFERENCES data_source (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE data_wrangler_data_source DROP FOREIGN KEY FK_F3ABC7F91A935C57');
        $this->addSql('ALTER TABLE data_transform DROP FOREIGN KEY FK_F81DC19B758EA5F');
        $this->addSql('ALTER TABLE data_wrangler_data_source DROP FOREIGN KEY FK_F3ABC7F9758EA5F');
        $this->addSql('DROP TABLE data_transform');
        $this->addSql('DROP TABLE fos_user');
        $this->addSql('DROP TABLE data_source');
        $this->addSql('DROP TABLE data_wrangler');
        $this->addSql('DROP TABLE data_wrangler_data_source');
    }
}
