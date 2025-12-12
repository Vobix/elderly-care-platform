-- ============================================================================
-- SCHEMA FIXES AND ENHANCEMENTS
-- Created: December 13, 2025
-- Purpose: Fix schema mismatches between SQL and DAOs, add new features
-- ============================================================================

-- ============================================================================
-- PART 1: FIX USERS TABLE SCHEMA MISMATCHES
-- ============================================================================

-- Rename id to user_id for consistency with DAOs (do this first)
ALTER TABLE `users` 
    CHANGE COLUMN `id` `user_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;

-- Add missing columns to users table to match UserDAO expectations
ALTER TABLE `users` 
    ADD COLUMN IF NOT EXISTS `username` VARCHAR(100) DEFAULT NULL AFTER `user_id`,
    ADD COLUMN IF NOT EXISTS `full_name` VARCHAR(200) DEFAULT NULL AFTER `password_hash`,
    ADD COLUMN IF NOT EXISTS `date_of_birth` DATE DEFAULT NULL AFTER `full_name`,
    ADD COLUMN IF NOT EXISTS `is_admin` TINYINT(1) NOT NULL DEFAULT 0 AFTER `role`,
    ADD COLUMN IF NOT EXISTS `has_completed_initial_assessment` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_admin`,
    ADD COLUMN IF NOT EXISTS `baseline_assessment_id` BIGINT(20) UNSIGNED DEFAULT NULL AFTER `has_completed_initial_assessment`;

-- Rename password_hash to password for DAO consistency
ALTER TABLE `users` 
    CHANGE COLUMN `password_hash` `password` VARCHAR(255) NOT NULL;

-- Add indexes for performance
CREATE INDEX IF NOT EXISTS `idx_username` ON `users` (`username`);
CREATE INDEX IF NOT EXISTS `idx_is_admin` ON `users` (`is_admin`);
CREATE INDEX IF NOT EXISTS `idx_has_completed_assessment` ON `users` (`has_completed_initial_assessment`);

-- Update existing users to have username (for compatibility)
UPDATE `users` SET `username` = SUBSTRING_INDEX(`email`, '@', 1) WHERE `username` IS NULL OR `username` = '';

-- Add unique constraint after populating username
ALTER TABLE `users` 
    MODIFY COLUMN `username` VARCHAR(100) NOT NULL,
    ADD UNIQUE KEY IF NOT EXISTS `uniq_username` (`username`);

-- ============================================================================
-- PART 2: CREATE user_game_stats TABLE (Referenced by GameDAO)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `user_game_stats` (
  `stat_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(10) UNSIGNED NOT NULL,
  `game_id` INT(10) UNSIGNED NOT NULL,
  `times_played` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `best_score` INT(11) NOT NULL DEFAULT 0,
  `average_score` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_score` BIGINT(20) NOT NULL DEFAULT 0,
  `rank` INT(10) UNSIGNED DEFAULT NULL,
  `percentile` DECIMAL(5,2) DEFAULT NULL,
  `last_played_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`stat_id`),
  UNIQUE KEY `uniq_user_game` (`user_id`, `game_id`),
  KEY `user_id` (`user_id`),
  KEY `game_id` (`game_id`),
  KEY `idx_best_score_desc` (`game_id`, `best_score` DESC),
  KEY `idx_avg_score_desc` (`game_id`, `average_score` DESC),
  CONSTRAINT `fk_user_game_stats_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_game_stats_game` FOREIGN KEY (`game_id`) REFERENCES `games` (`game_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migrate data from user_game_summary to user_game_stats if exists
INSERT IGNORE INTO `user_game_stats` (`user_id`, `times_played`, `best_score`, `last_played_at`)
SELECT `user_id`, `total_sessions`, `best_score`, `last_played_at`
FROM `user_game_summary`
WHERE EXISTS (SELECT 1 FROM `user_game_summary`);

-- ============================================================================
-- PART 3: ADD BASELINE ASSESSMENT TABLE
-- ============================================================================

CREATE TABLE IF NOT EXISTS `baseline_assessments` (
  `assessment_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(10) UNSIGNED NOT NULL,
  `questionnaire_id` INT(10) UNSIGNED NOT NULL,
  `score` DECIMAL(7,2) NOT NULL,
  `risk_category` ENUM('low', 'moderate', 'high', 'critical') NOT NULL,
  `interpretation` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`interpretation`)),
  `responses` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`responses`)),
  `completed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`assessment_id`),
  KEY `user_id` (`user_id`),
  KEY `questionnaire_id` (`questionnaire_id`),
  KEY `idx_risk_category` (`risk_category`),
  CONSTRAINT `fk_baseline_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_baseline_questionnaire` FOREIGN KEY (`questionnaire_id`) REFERENCES `questionnaires` (`questionnaire_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- PART 4: FIX QUESTIONNAIRE_RESPONSES SCHEMA
-- ============================================================================

-- Rename to match QuestionnaireDAO expectations
ALTER TABLE `questionnaire_responses` 
    CHANGE COLUMN `response_id` `result_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    CHANGE COLUMN `responses` `answers` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`answers`)),
    CHANGE COLUMN `taken_at` `completed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- Add interpretation column
ALTER TABLE `questionnaire_responses` 
    ADD COLUMN IF NOT EXISTS `interpretation` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`interpretation`));

-- ============================================================================
-- PART 5: FIX MOOD_LOGS SCHEMA
-- ============================================================================

-- Add created_at compatibility (already exists but ensure consistency)
ALTER TABLE `mood_logs` 
    MODIFY COLUMN `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- Rename mood_text to notes for consistency
ALTER TABLE `mood_logs` 
    CHANGE COLUMN `mood_text` `notes` VARCHAR(255) DEFAULT NULL;

-- ============================================================================
-- PART 6: ADD QUESTIONNAIRES TYPE COLUMN
-- ============================================================================

ALTER TABLE `questionnaires` 
    ADD COLUMN IF NOT EXISTS `type` VARCHAR(50) NOT NULL DEFAULT 'wellness' AFTER `short_code`;

-- Update existing questionnaires with type
UPDATE `questionnaires` SET `type` = `short_code` WHERE `type` = 'wellness';

-- Add index on type for QuestionnaireDAO queries
CREATE INDEX IF NOT EXISTS `idx_type` ON `questionnaires` (`type`);

-- ============================================================================
-- PART 7: INSERT STANDARD QUESTIONNAIRES
-- ============================================================================

INSERT IGNORE INTO `questionnaires` (`name`, `short_code`, `type`, `version`) VALUES
('WHO-5 Well-Being Index', 'WHO5', 'WHO5', '1.0'),
('Patient Health Questionnaire-9', 'PHQ9', 'PHQ9', '1.0'),
('Generalized Anxiety Disorder-7', 'GAD7', 'GAD7', '1.0'),
('Geriatric Depression Scale-15', 'GDS15', 'GDS15', '1.0'),
('Pittsburgh Sleep Quality Index', 'PSQI', 'PSQI', '1.0'),
('Perceived Stress Scale-4', 'PSS4', 'PSS4', '1.0');

-- ============================================================================
-- PART 8: ADD GAME_SCORES DETAILS COLUMN
-- ============================================================================

-- Add details JSON column for game-specific data
ALTER TABLE `game_scores` 
    ADD COLUMN IF NOT EXISTS `details` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)) AFTER `level_reached`;

-- ============================================================================
-- PART 9: UPDATE GAMES TABLE WITH NEW GAMES
-- ============================================================================

-- Remove duplicate games
DELETE FROM `games` WHERE `code` = 'visual_memory';
DELETE FROM `games` WHERE `code` = 'verbal_memory';

-- Insert new card flip game
INSERT IGNORE INTO `games` (`name`, `code`, `description`) VALUES
('Card Flip', 'card_flip', 'Pattern matching memory game - flip cards to find matching pairs');

-- Update existing games
UPDATE `games` SET `name` = 'Memory Grid', `description` = 'Classic memory card matching game' WHERE `code` = 'memory';
UPDATE `games` SET `description` = 'Remember and recall increasingly longer number sequences' WHERE `code` = 'number_memory';
UPDATE `games` SET `description` = 'Working memory test - remember number positions in sequence' WHERE `code` = 'chimp_test';
UPDATE `games` SET `description` = 'Test your reflexes and reaction speed' WHERE `code` = 'reaction';
UPDATE `games` SET `description` = 'Focus and concentration training' WHERE `code` = 'attention';
UPDATE `games` SET `description` = 'Visual-spatial problem solving' WHERE `code` = 'puzzle';

-- ============================================================================
-- PART 10: CREATE ADMIN ACTIVITY LOG TABLE
-- ============================================================================

CREATE TABLE IF NOT EXISTS `admin_actions` (
  `action_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_user_id` INT(10) UNSIGNED NOT NULL,
  `action_type` VARCHAR(100) NOT NULL,
  `target_user_id` INT(10) UNSIGNED DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `metadata` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`action_id`),
  KEY `admin_user_id` (`admin_user_id`),
  KEY `target_user_id` (`target_user_id`),
  KEY `action_type` (`action_type`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `fk_admin_actions_admin` FOREIGN KEY (`admin_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_admin_actions_target` FOREIGN KEY (`target_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- VERIFICATION QUERIES (Comment out after running)
-- ============================================================================

-- SELECT 'Users table structure:' AS '';
-- DESCRIBE users;

-- SELECT 'User game stats table structure:' AS '';
-- DESCRIBE user_game_stats;

-- SELECT 'Baseline assessments table structure:' AS '';
-- DESCRIBE baseline_assessments;

-- SELECT 'Questionnaire responses table structure:' AS '';
-- DESCRIBE questionnaire_responses;

-- SELECT 'Games available:' AS '';
-- SELECT * FROM games ORDER BY game_id;

-- SELECT 'Questionnaires available:' AS '';
-- SELECT * FROM questionnaires ORDER BY questionnaire_id;

-- ============================================================================
-- END OF MIGRATION
-- ============================================================================
