<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260208233409 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE card (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, game VARCHAR(20) NOT NULL, image_url LONGTEXT DEFAULT NULL, discr VARCHAR(255) NOT NULL, set_code VARCHAR(20) DEFAULT NULL, set_name VARCHAR(255) DEFAULT NULL, collector_number VARCHAR(20) DEFAULT NULL, rarity VARCHAR(50) DEFAULT NULL, mana_cost VARCHAR(50) DEFAULT NULL, type_line VARCHAR(255) DEFAULT NULL, oracle_text LONGTEXT DEFAULT NULL, power VARCHAR(255) DEFAULT NULL, toughness VARCHAR(10) DEFAULT NULL, loyalty VARCHAR(10) DEFAULT NULL, colors JSON DEFAULT NULL, color_identity JSON DEFAULT NULL, artist VARCHAR(255) DEFAULT NULL, is_foil TINYINT(1) DEFAULT NULL, price_usd DOUBLE PRECISION DEFAULT NULL, price_eur DOUBLE PRECISION DEFAULT NULL, price_tix DOUBLE PRECISION DEFAULT NULL, set_id VARCHAR(50) DEFAULT NULL, number VARCHAR(20) DEFAULT NULL, supertype VARCHAR(50) DEFAULT NULL, subtypes JSON DEFAULT NULL, hp INT DEFAULT NULL, types JSON DEFAULT NULL, evolves_from VARCHAR(255) DEFAULT NULL, attacks JSON DEFAULT NULL, weaknesses JSON DEFAULT NULL, resistances JSON DEFAULT NULL, retreat_cost VARCHAR(50) DEFAULT NULL, national_pokedex_number VARCHAR(10) DEFAULT NULL, card_number VARCHAR(20) DEFAULT NULL, color VARCHAR(50) DEFAULT NULL, category VARCHAR(50) DEFAULT NULL, cost INT DEFAULT NULL, counter INT DEFAULT NULL, attributes JSON DEFAULT NULL, effect LONGTEXT DEFAULT NULL, `trigger` LONGTEXT DEFAULT NULL, price_jpy DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE collection_shares (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', collection_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', shared_by_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', shared_with_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', permission VARCHAR(20) NOT NULL, INDEX IDX_627A898A514956FD (collection_id), INDEX IDX_627A898A5489CD19 (shared_by_id), INDEX IDX_627A898AD14FE63F (shared_with_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE collections (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', inventory_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, game VARCHAR(20) NOT NULL, description LONGTEXT DEFAULT NULL, set_code VARCHAR(10) DEFAULT NULL, set_name VARCHAR(255) DEFAULT NULL, target_count INT DEFAULT NULL, is_complete TINYINT(1) NOT NULL, is_default_all_cards TINYINT(1) DEFAULT 0 NOT NULL, is_game_deck TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_D325D3EE9EEA759 (inventory_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE collection_cards (collection_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', card_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_433AE0AE514956FD (collection_id), INDEX IDX_433AE0AE4ACC9A20 (card_id), PRIMARY KEY(collection_id, card_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE deck_cards (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', deck_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', card_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', quantity INT NOT NULL, zone VARCHAR(20) NOT NULL, INDEX IDX_C59FA212111948DC (deck_id), INDEX IDX_C59FA2124ACC9A20 (card_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE decks (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', inventory_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, game VARCHAR(20) NOT NULL, description LONGTEXT DEFAULT NULL, format VARCHAR(50) DEFAULT NULL, is_legal TINYINT(1) NOT NULL, validation_errors JSON DEFAULT NULL, INDEX IDX_A3FCC6329EEA759 (inventory_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE inventories (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE inventory_items (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', inventory_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', card_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', quantity INT NOT NULL, metadata JSON DEFAULT NULL, `condition` VARCHAR(50) DEFAULT NULL, language VARCHAR(50) DEFAULT NULL, is_foil TINYINT(1) NOT NULL, purchase_price DOUBLE PRECISION DEFAULT NULL, current_price DOUBLE PRECISION DEFAULT NULL, INDEX IDX_3D82424D9EEA759 (inventory_id), INDEX IDX_3D82424D4ACC9A20 (card_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE marketplace_listings (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', seller_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', card_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', collection_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', type VARCHAR(20) NOT NULL, price NUMERIC(12, 2) NOT NULL, currency VARCHAR(10) NOT NULL, status VARCHAR(20) NOT NULL, description LONGTEXT DEFAULT NULL, INDEX IDX_8E9E63128DE820D9 (seller_id), INDEX IDX_8E9E63124ACC9A20 (card_id), INDEX IDX_8E9E6312514956FD (collection_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE posts (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', author_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', content LONGTEXT NOT NULL, type VARCHAR(50) DEFAULT NULL, metadata JSON DEFAULT NULL, INDEX IDX_885DBAFAF675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_followers (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', follower_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', following_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_84E87043AC24F853 (follower_id), INDEX IDX_84E870431816E3A3 (following_id), UNIQUE INDEX user_follower_unique (follower_id, following_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', email VARCHAR(255) NOT NULL, phone VARCHAR(30) DEFAULT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(100) NOT NULL, type VARCHAR(20) NOT NULL, is_active TINYINT(1) NOT NULL, last_login_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', scanned_cards_count INT DEFAULT 0 NOT NULL, last_reset_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
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

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE collection_shares DROP FOREIGN KEY FK_627A898A514956FD');
        $this->addSql('ALTER TABLE collection_shares DROP FOREIGN KEY FK_627A898A5489CD19');
        $this->addSql('ALTER TABLE collection_shares DROP FOREIGN KEY FK_627A898AD14FE63F');
        $this->addSql('ALTER TABLE collections DROP FOREIGN KEY FK_D325D3EE9EEA759');
        $this->addSql('ALTER TABLE collection_cards DROP FOREIGN KEY FK_433AE0AE514956FD');
        $this->addSql('ALTER TABLE collection_cards DROP FOREIGN KEY FK_433AE0AE4ACC9A20');
        $this->addSql('ALTER TABLE deck_cards DROP FOREIGN KEY FK_C59FA212111948DC');
        $this->addSql('ALTER TABLE deck_cards DROP FOREIGN KEY FK_C59FA2124ACC9A20');
        $this->addSql('ALTER TABLE decks DROP FOREIGN KEY FK_A3FCC6329EEA759');
        $this->addSql('ALTER TABLE inventory_items DROP FOREIGN KEY FK_3D82424D9EEA759');
        $this->addSql('ALTER TABLE inventory_items DROP FOREIGN KEY FK_3D82424D4ACC9A20');
        $this->addSql('ALTER TABLE marketplace_listings DROP FOREIGN KEY FK_8E9E63128DE820D9');
        $this->addSql('ALTER TABLE marketplace_listings DROP FOREIGN KEY FK_8E9E63124ACC9A20');
        $this->addSql('ALTER TABLE marketplace_listings DROP FOREIGN KEY FK_8E9E6312514956FD');
        $this->addSql('ALTER TABLE posts DROP FOREIGN KEY FK_885DBAFAF675F31B');
        $this->addSql('ALTER TABLE user_followers DROP FOREIGN KEY FK_84E87043AC24F853');
        $this->addSql('ALTER TABLE user_followers DROP FOREIGN KEY FK_84E870431816E3A3');
        $this->addSql('DROP TABLE card');
        $this->addSql('DROP TABLE collection_shares');
        $this->addSql('DROP TABLE collections');
        $this->addSql('DROP TABLE collection_cards');
        $this->addSql('DROP TABLE deck_cards');
        $this->addSql('DROP TABLE decks');
        $this->addSql('DROP TABLE inventories');
        $this->addSql('DROP TABLE inventory_items');
        $this->addSql('DROP TABLE marketplace_listings');
        $this->addSql('DROP TABLE posts');
        $this->addSql('DROP TABLE user_followers');
        $this->addSql('DROP TABLE users');
    }
}
