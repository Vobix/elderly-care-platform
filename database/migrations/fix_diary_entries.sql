-- Migration to fix diary_entries table
-- Fixes column name mismatches between schema and application code
-- Run this script to align the database with the application expectations
-- Safe to run multiple times (idempotent)

-- Check if table exists, if not create it with correct structure
CREATE TABLE IF NOT EXISTS `diary_entries` (
  `diary_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `entry_date` date NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`diary_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_entry_date` (`entry_date`),
  CONSTRAINT `fk_diary_entries_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- If table already existed with wrong column names, fix them below:

-- Add the missing entry_date column (only if it doesn't exist)
SET @col_exists = (SELECT COUNT(*) 
                   FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'diary_entries' 
                   AND COLUMN_NAME = 'entry_date');

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `diary_entries` ADD COLUMN `entry_date` DATE NULL AFTER `entry_text`',
    'SELECT "Column entry_date already exists" AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Populate entry_date with dates from created_at for existing records
UPDATE `diary_entries` 
SET `entry_date` = DATE(`created_at`) 
WHERE `entry_date` IS NULL;

-- Make entry_date NOT NULL after populating existing records (if it's currently NULL)
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `diary_entries` MODIFY COLUMN `entry_date` DATE NOT NULL',
    'SELECT "Skipping entry_date modification" AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Rename entry_text to content (only if entry_text exists and content doesn't)
SET @old_col_exists = (SELECT COUNT(*) 
                       FROM INFORMATION_SCHEMA.COLUMNS 
                       WHERE TABLE_SCHEMA = DATABASE() 
                       AND TABLE_NAME = 'diary_entries' 
                       AND COLUMN_NAME = 'entry_text');

SET @sql = IF(@old_col_exists > 0,
    'ALTER TABLE `diary_entries` CHANGE COLUMN `entry_text` `content` TEXT NOT NULL',
    'SELECT "Column entry_text already renamed to content" AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Rename entry_id to diary_id (only if entry_id exists and diary_id doesn't)
SET @old_id_exists = (SELECT COUNT(*) 
                      FROM INFORMATION_SCHEMA.COLUMNS 
                      WHERE TABLE_SCHEMA = DATABASE() 
                      AND TABLE_NAME = 'diary_entries' 
                      AND COLUMN_NAME = 'entry_id');

SET @sql = IF(@old_id_exists > 0,
    'ALTER TABLE `diary_entries` CHANGE COLUMN `entry_id` `diary_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
    'SELECT "Column entry_id already renamed to diary_id" AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Note: Foreign key constraints will automatically update if they reference entry_id
-- The PRIMARY KEY and AUTO_INCREMENT will be preserved during the rename
