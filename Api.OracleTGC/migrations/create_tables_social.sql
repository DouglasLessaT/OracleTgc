-- =============================================================================
-- OracleTGC - Script SQL para criar tabelas de rede social / perfil
-- Execução: mysql -u usuario -p nome_do_banco < create_tables_social.sql
-- Ou executar no cliente MySQL/MariaDB após USE nome_do_banco;
-- =============================================================================
-- Requer: tabela users já existente (id CHAR(36)).
-- UUIDs: CHAR(36) para compatibilidade com migration Version20260127000000.
-- =============================================================================

-- -----------------------------------------------------------------------------
-- 1) Coluna plan em users
--    (Pular este bloco se a coluna plan já existir em users.)
-- -----------------------------------------------------------------------------
-- ALTER TABLE users ADD plan VARCHAR(20) NOT NULL DEFAULT 'free';
-- UPDATE users SET plan = type WHERE type IN ('free', 'premium');
-- UPDATE users SET plan = 'premium' WHERE type = 'admin';
-- UPDATE users SET type = 'customer' WHERE type IN ('free', 'premium');
-- UPDATE users SET type = 'guest' WHERE type NOT IN ('customer', 'admin');

-- -----------------------------------------------------------------------------
-- 2) user_profiles
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS user_profiles (
    id CHAR(36) NOT NULL,
    user_id CHAR(36) NOT NULL,
    avatar_url VARCHAR(500) DEFAULT NULL,
    display_name VARCHAR(100) DEFAULT NULL,
    bio LONGTEXT DEFAULT NULL,
    website VARCHAR(255) DEFAULT NULL,
    location VARCHAR(100) DEFAULT NULL,
    followers_count INT NOT NULL DEFAULT 0,
    following_count INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY UNIQ_user_profiles_user_id (user_id),
    CONSTRAINT FK_user_profiles_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- 3) activities
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS activities (
    id CHAR(36) NOT NULL,
    user_id CHAR(36) NOT NULL,
    type VARCHAR(50) NOT NULL,
    target_type VARCHAR(50) NOT NULL,
    target_id CHAR(36) NOT NULL,
    metadata JSON DEFAULT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_activities_user_created (user_id, created_at),
    CONSTRAINT FK_activities_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- 4) notifications
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notifications (
    id CHAR(36) NOT NULL,
    user_id CHAR(36) NOT NULL,
    actor_id CHAR(36) DEFAULT NULL,
    type VARCHAR(50) NOT NULL,
    target_type VARCHAR(50) DEFAULT NULL,
    target_id CHAR(36) DEFAULT NULL,
    read_at DATETIME DEFAULT NULL,
    extra JSON DEFAULT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_notifications_user_created (user_id, created_at),
    CONSTRAINT FK_notifications_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT FK_notifications_actor FOREIGN KEY (actor_id) REFERENCES users (id) ON DELETE SET NULL
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- 5) channels
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS channels (
    id CHAR(36) NOT NULL,
    owner_id CHAR(36) DEFAULT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL,
    description LONGTEXT DEFAULT NULL,
    subscribers_count INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY channels_slug_unique (slug),
    CONSTRAINT FK_channels_owner FOREIGN KEY (owner_id) REFERENCES users (id) ON DELETE CASCADE
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- 6) subscriptions
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS subscriptions (
    id CHAR(36) NOT NULL,
    user_id CHAR(36) NOT NULL,
    channel_id CHAR(36) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY subscriptions_user_channel_unique (user_id, channel_id),
    KEY idx_subscriptions_user (user_id),
    KEY idx_subscriptions_channel (channel_id),
    CONSTRAINT FK_subscriptions_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT FK_subscriptions_channel FOREIGN KEY (channel_id) REFERENCES channels (id) ON DELETE CASCADE
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- 7) hashtags
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS hashtags (
    id CHAR(36) NOT NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY hashtags_name_unique (name)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- 8) hashtag_usages
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS hashtag_usages (
    id CHAR(36) NOT NULL,
    hashtag_id CHAR(36) NOT NULL,
    period_start DATETIME NOT NULL,
    count INT NOT NULL DEFAULT 0,
    score DOUBLE PRECISION NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY hashtag_usages_hashtag_period_unique (hashtag_id, period_start),
    KEY idx_hashtag_usages_period_score (period_start, score),
    CONSTRAINT FK_hashtag_usages_hashtag FOREIGN KEY (hashtag_id) REFERENCES hashtags (id) ON DELETE CASCADE
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- 9) feed_items
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS feed_items (
    id CHAR(36) NOT NULL,
    user_id CHAR(36) NOT NULL,
    item_type VARCHAR(30) NOT NULL,
    item_id CHAR(36) NOT NULL,
    score DOUBLE PRECISION NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_feed_items_user_created (user_id, created_at),
    KEY idx_feed_items_user_score (user_id, score),
    CONSTRAINT FK_feed_items_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;

-- =============================================================================
-- Fim do script
-- =============================================================================
