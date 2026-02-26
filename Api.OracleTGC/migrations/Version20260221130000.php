<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Cria tabelas: activities, notifications, channels, subscriptions, hashtags, hashtag_usages, feed_items.
 */
final class Version20260221130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create activities, notifications, channels, subscriptions, hashtags, hashtag_usages, feed_items';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE activities (
            id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\',
            user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\',
            type VARCHAR(50) NOT NULL,
            target_type VARCHAR(50) NOT NULL,
            target_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\',
            metadata JSON DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX idx_activities_user_created (user_id, created_at),
            PRIMARY KEY(id),
            CONSTRAINT FK_activities_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE notifications (
            id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\',
            user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\',
            actor_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\',
            type VARCHAR(50) NOT NULL,
            target_type VARCHAR(50) DEFAULT NULL,
            target_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\',
            read_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            extra JSON DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX idx_notifications_user_created (user_id, created_at),
            PRIMARY KEY(id),
            CONSTRAINT FK_notifications_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
            CONSTRAINT FK_notifications_actor FOREIGN KEY (actor_id) REFERENCES users (id) ON DELETE SET NULL
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE channels (
            id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\',
            owner_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\',
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(100) NOT NULL,
            type VARCHAR(50) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            subscribers_count INT DEFAULT 0 NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX channels_slug_unique (slug),
            PRIMARY KEY(id),
            CONSTRAINT FK_channels_owner FOREIGN KEY (owner_id) REFERENCES users (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE subscriptions (
            id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\',
            user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\',
            channel_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\',
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX subscriptions_user_channel_unique (user_id, channel_id),
            INDEX idx_subscriptions_user (user_id),
            INDEX idx_subscriptions_channel (channel_id),
            PRIMARY KEY(id),
            CONSTRAINT FK_subscriptions_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
            CONSTRAINT FK_subscriptions_channel FOREIGN KEY (channel_id) REFERENCES channels (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE hashtags (
            id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\',
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX hashtags_name_unique (name),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE hashtag_usages (
            id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\',
            hashtag_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\',
            period_start DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            count INT DEFAULT 0 NOT NULL,
            score DOUBLE PRECISION DEFAULT 0 NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX hashtag_usages_hashtag_period_unique (hashtag_id, period_start),
            INDEX idx_hashtag_usages_period_score (period_start, score),
            PRIMARY KEY(id),
            CONSTRAINT FK_hashtag_usages_hashtag FOREIGN KEY (hashtag_id) REFERENCES hashtags (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE feed_items (
            id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\',
            user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\',
            item_type VARCHAR(30) NOT NULL,
            item_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\',
            score DOUBLE PRECISION DEFAULT 0 NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX idx_feed_items_user_created (user_id, created_at),
            INDEX idx_feed_items_user_score (user_id, score),
            PRIMARY KEY(id),
            CONSTRAINT FK_feed_items_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE feed_items');
        $this->addSql('DROP TABLE hashtag_usages');
        $this->addSql('DROP TABLE hashtags');
        $this->addSql('DROP TABLE subscriptions');
        $this->addSql('DROP TABLE channels');
        $this->addSql('DROP TABLE notifications');
        $this->addSql('DROP TABLE activities');
    }
}
