<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Adiciona coluna plan (plano de assinatura) em users e migra dados de type para type+plan.
 * type = papel (guest, customer, admin), plan = assinatura (free, premium).
 */
final class Version20260210120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add users.plan column; backfill from type and normalize type to guest/customer/admin';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD plan VARCHAR(20) NOT NULL DEFAULT \'free\'');
        $this->addSql('UPDATE users SET plan = type WHERE type IN (\'free\', \'premium\')');
        $this->addSql('UPDATE users SET plan = \'premium\' WHERE type = \'admin\'');
        $this->addSql('UPDATE users SET type = \'customer\' WHERE type IN (\'free\', \'premium\')');
        $this->addSql('UPDATE users SET type = \'guest\' WHERE type NOT IN (\'customer\', \'admin\')');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP plan');
    }
}
