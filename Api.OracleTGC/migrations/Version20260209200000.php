<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260209200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adiciona campos de verificação de email em users (email_verification_token, email_verified_at)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD email_verification_token VARCHAR(6) DEFAULT NULL, ADD email_verified_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP email_verification_token, DROP email_verified_at');
    }
}
