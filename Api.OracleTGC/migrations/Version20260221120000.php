<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Cria tabela user_profiles (perfil público do usuário).
 * Mantém users enxuta; dados de exibição e contadores sociais ficam aqui.
 */
final class Version20260221120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user_profiles table (1:1 with users)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user_profiles (
            id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\',
            user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\',
            avatar_url VARCHAR(500) DEFAULT NULL,
            display_name VARCHAR(100) DEFAULT NULL,
            bio LONGTEXT DEFAULT NULL,
            website VARCHAR(255) DEFAULT NULL,
            location VARCHAR(100) DEFAULT NULL,
            followers_count INT DEFAULT 0 NOT NULL,
            following_count INT DEFAULT 0 NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PRIMARY KEY(id),
            UNIQUE INDEX UNIQ_user_profiles_user_id (user_id),
            CONSTRAINT FK_user_profiles_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE user_profiles');
    }
}
