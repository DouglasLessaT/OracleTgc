<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Converte colunas UUID de BINARY(16) para CHAR(36) (string legível).
 * Corrige o erro "Data too long for column 'id'" ao usar tipo uuid_string.
 */
final class Version20260127000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Converte colunas UUID de BINARY(16) para CHAR(36) (uuid_string)';
    }

    public function up(Schema $schema): void
    {
        $this->dropAllUuidForeignKeys();

        $this->convertColumn('users', 'id', true);
        $this->convertColumn('card', 'id', true);
        $this->convertColumn('inventories', 'id', true);
        $this->convertColumn('inventories', 'user_id', false);
        $this->convertColumn('collections', 'id', true);
        $this->convertColumn('collections', 'inventory_id', false);
        $this->convertColumn('decks', 'id', true);
        $this->convertColumn('decks', 'inventory_id', false);
        $this->convertColumn('collection_shares', 'id', true);
        $this->convertColumn('collection_shares', 'collection_id', false);
        $this->convertColumn('collection_shares', 'shared_by_id', false);
        $this->convertColumn('collection_shares', 'shared_with_id', false);
        $this->addSql('ALTER TABLE collection_cards DROP PRIMARY KEY');
        $this->convertColumn('collection_cards', 'collection_id', false);
        $this->convertColumn('collection_cards', 'card_id', false);
        $this->addSql('ALTER TABLE collection_cards ADD PRIMARY KEY (collection_id, card_id)');
        $this->convertColumn('deck_cards', 'id', true);
        $this->convertColumn('deck_cards', 'deck_id', false);
        $this->convertColumn('deck_cards', 'card_id', false);
        $this->convertColumn('inventory_items', 'id', true);
        $this->convertColumn('inventory_items', 'inventory_id', false);
        $this->convertColumn('inventory_items', 'card_id', false);
        $this->convertColumn('marketplace_listings', 'id', true);
        $this->convertColumn('marketplace_listings', 'seller_id', false);
        $this->convertColumn('marketplace_listings', 'card_id', false);
        $this->convertColumn('marketplace_listings', 'collection_id', false);
        $this->convertColumn('posts', 'id', true);
        $this->convertColumn('posts', 'author_id', false);
        $this->convertColumn('user_followers', 'id', true);
        $this->convertColumn('user_followers', 'follower_id', false);
        $this->convertColumn('user_followers', 'following_id', false);

        $this->recreateAllUuidForeignKeys();
    }

    public function down(Schema $schema): void
    {
        $this->addSql('-- Reverter manualmente se necessário');
    }

    private function dropAllUuidForeignKeys(): void
    {
        $drops = [
            'ALTER TABLE collection_shares DROP FOREIGN KEY FK_627A898A514956FD',
            'ALTER TABLE collection_shares DROP FOREIGN KEY FK_627A898A5489CD19',
            'ALTER TABLE collection_shares DROP FOREIGN KEY FK_627A898AD14FE63F',
            'ALTER TABLE collections DROP FOREIGN KEY FK_D325D3EE9EEA759',
            'ALTER TABLE collection_cards DROP FOREIGN KEY FK_433AE0AE514956FD',
            'ALTER TABLE collection_cards DROP FOREIGN KEY FK_433AE0AE4ACC9A20',
            'ALTER TABLE deck_cards DROP FOREIGN KEY FK_C59FA212111948DC',
            'ALTER TABLE deck_cards DROP FOREIGN KEY FK_C59FA2124ACC9A20',
            'ALTER TABLE decks DROP FOREIGN KEY FK_A3FCC6329EEA759',
            'ALTER TABLE inventory_items DROP FOREIGN KEY FK_3D82424D9EEA759',
            'ALTER TABLE inventory_items DROP FOREIGN KEY FK_3D82424D4ACC9A20',
            'ALTER TABLE marketplace_listings DROP FOREIGN KEY FK_8E9E63128DE820D9',
            'ALTER TABLE marketplace_listings DROP FOREIGN KEY FK_8E9E63124ACC9A20',
            'ALTER TABLE marketplace_listings DROP FOREIGN KEY FK_8E9E6312514956FD',
            'ALTER TABLE posts DROP FOREIGN KEY FK_885DBAFAF675F31B',
            'ALTER TABLE user_followers DROP FOREIGN KEY FK_84E87043AC24F853',
            'ALTER TABLE user_followers DROP FOREIGN KEY FK_84E870431816E3A3',
        ];
        foreach ($drops as $sql) {
            $this->addSql($sql);
        }
    }

    private function convertColumn(string $table, string $column, bool $isPrimaryKey): void
    {
        $tmp = $column . '_c36';
        $hex = "LOWER(CONCAT(SUBSTR(HEX(`{$column}`), 1, 8), '-', SUBSTR(HEX(`{$column}`), 9, 4), '-', SUBSTR(HEX(`{$column}`), 13, 4), '-', SUBSTR(HEX(`{$column}`), 17, 4), '-', SUBSTR(HEX(`{$column}`), 21, 12)))";

        $this->addSql("ALTER TABLE `{$table}` ADD `{$tmp}` CHAR(36) DEFAULT NULL");
        $this->addSql("UPDATE `{$table}` SET `{$tmp}` = {$hex}");
        $this->addSql("ALTER TABLE `{$table}` MODIFY `{$tmp}` CHAR(36) NOT NULL");

        if ($isPrimaryKey) {
            $this->addSql("ALTER TABLE `{$table}` DROP PRIMARY KEY");
        }
        $this->addSql("ALTER TABLE `{$table}` DROP COLUMN `{$column}`");
        $this->addSql("ALTER TABLE `{$table}` CHANGE `{$tmp}` `{$column}` CHAR(36) NOT NULL");
        if ($isPrimaryKey) {
            $this->addSql("ALTER TABLE `{$table}` ADD PRIMARY KEY (`{$column}`)");
        }
    }

    private function recreateAllUuidForeignKeys(): void
    {
        $this->addSql('ALTER TABLE collection_shares ADD CONSTRAINT FK_627A898A514956FD FOREIGN KEY (collection_id) REFERENCES collections (id)');
        $this->addSql('ALTER TABLE collection_shares ADD CONSTRAINT FK_627A898A5489CD19 FOREIGN KEY (shared_by_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE collection_shares ADD CONSTRAINT FK_627A898AD14FE63F FOREIGN KEY (shared_with_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE collections ADD CONSTRAINT FK_D325D3EE9EEA759 FOREIGN KEY (inventory_id) REFERENCES inventories (id)');
        $this->addSql('ALTER TABLE collection_cards ADD CONSTRAINT FK_433AE0AE514956FD FOREIGN KEY (collection_id) REFERENCES collections (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE collection_cards ADD CONSTRAINT FK_433AE0AE4ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE deck_cards ADD CONSTRAINT FK_C59FA212111948DC FOREIGN KEY (deck_id) REFERENCES decks (id)');
        $this->addSql('ALTER TABLE deck_cards ADD CONSTRAINT FK_C59FA2124ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id)');
        $this->addSql('ALTER TABLE decks ADD CONSTRAINT FK_A3FCC6329EEA759 FOREIGN KEY (inventory_id) REFERENCES inventories (id)');
        $this->addSql('ALTER TABLE inventory_items ADD CONSTRAINT FK_3D82424D9EEA759 FOREIGN KEY (inventory_id) REFERENCES inventories (id)');
        $this->addSql('ALTER TABLE inventory_items ADD CONSTRAINT FK_3D82424D4ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id)');
        $this->addSql('ALTER TABLE marketplace_listings ADD CONSTRAINT FK_8E9E63128DE820D9 FOREIGN KEY (seller_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE marketplace_listings ADD CONSTRAINT FK_8E9E63124ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id)');
        $this->addSql('ALTER TABLE marketplace_listings ADD CONSTRAINT FK_8E9E6312514956FD FOREIGN KEY (collection_id) REFERENCES collections (id)');
        $this->addSql('ALTER TABLE posts ADD CONSTRAINT FK_885DBAFAF675F31B FOREIGN KEY (author_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE user_followers ADD CONSTRAINT FK_84E87043AC24F853 FOREIGN KEY (follower_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE user_followers ADD CONSTRAINT FK_84E870431816E3A3 FOREIGN KEY (following_id) REFERENCES users (id)');
    }
}
