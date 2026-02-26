<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Adiciona colunas de verificação de email na tabela users.
 */
final class Version20260210000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adiciona email_verification_token e email_verified_at em users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD email_verification_token VARCHAR(6) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD email_verified_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP email_verification_token');
        $this->addSql('ALTER TABLE users DROP email_verified_at');
    }
}
