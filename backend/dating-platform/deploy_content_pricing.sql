-- ============================================================================
-- Ezt masold be a phpMyAdmin "SQL" fulebe es futtasd le EGYBEN.
-- A kovetkezo 3 migraciot valtja ki:
--   2026_09_22_100000_add_pricing_to_images_and_albums
--   2026_09_22_110000_add_content_unlock_settings
--   2026_09_22_120000_add_credits_price_to_images_and_albums
-- ============================================================================

-- Ellenorizd elotte, hogy meg nincsenek-e meg ezek (ha esetleg mar futtattad elesben):
-- SHOW COLUMNS FROM images LIKE 'price';
-- SHOW COLUMNS FROM albums LIKE 'price';
-- SHOW TABLES LIKE 'content_unlocks';


-- --- 2026_09_22_100000: images/albums price + images.blurred_name + content_unlocks tabla ---
ALTER TABLE `images` ADD COLUMN `price` DECIMAL(8,2) NULL DEFAULT 0.00 AFTER `description`;
ALTER TABLE `images` ADD COLUMN `blurred_name` VARCHAR(191) NULL DEFAULT NULL AFTER `price`;

ALTER TABLE `albums` ADD COLUMN `price` DECIMAL(8,2) NULL DEFAULT 0.00 AFTER `description`;

CREATE TABLE `content_unlocks` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(10) UNSIGNED NOT NULL,
  `unlockable_type` VARCHAR(20) NOT NULL,
  `unlockable_id` INT(10) UNSIGNED NOT NULL,
  `method` VARCHAR(20) NOT NULL,
  `price_paid` DECIMAL(8,2) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `content_unlocks_user_id_unlockable_type_unlockable_id_unique` (`user_id`,`unlockable_type`,`unlockable_id`),
  KEY `content_unlocks_unlockable_type_unlockable_id_index` (`unlockable_type`,`unlockable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --- 2026_09_22_110000: globalis beallitasok (Settings) ---
INSERT INTO `settings` (`name`, `value`, `type`, `category`, `updated_at`)
VALUES ('CONTENT_UNLOCK_PRICE_MODE', 'credits', 'select|credits~money', 'Poze si albume cu pret', NOW());

INSERT INTO `settings` (`name`, `value`, `type`, `category`, `updated_at`)
VALUES ('CONTENT_UNLOCK_ENABLED', 'yes', 'toggle', 'Poze si albume cu pret', NOW());


-- --- 2026_09_22_120000: kulon kredit-ar oszlop (a EUR ar mellett, nem abbol szamolva) ---
ALTER TABLE `images` ADD COLUMN `price_credits` INT(10) UNSIGNED NULL DEFAULT 0 AFTER `price`;
ALTER TABLE `albums` ADD COLUMN `price_credits` INT(10) UNSIGNED NULL DEFAULT 0 AFTER `price`;


-- ============================================================================
-- Migrations tabla frissitese - hogy a "php artisan migrate" elesben tudja,
-- hogy ez a 3 migracio mar le lett futtatva.
-- ============================================================================
SET @next_batch = (SELECT MAX(`batch`) + 1 FROM `migrations`);

INSERT INTO `migrations` (`migration`, `batch`) VALUES
('2026_09_22_100000_add_pricing_to_images_and_albums', @next_batch),
('2026_09_22_110000_add_content_unlock_settings', @next_batch),
('2026_09_22_120000_add_credits_price_to_images_and_albums', @next_batch);
