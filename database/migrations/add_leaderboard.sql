-- Leaderboard Enhancement Migration
-- Adds global and per-game leaderboard tracking
-- Run this after initial schema setup

-- Add leaderboard tracking columns to user_game_stats if not exists
ALTER TABLE `user_game_stats` 
ADD COLUMN IF NOT EXISTS `global_rank` INT UNSIGNED DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `percentile` DECIMAL(5,2) DEFAULT NULL COMMENT 'Top X% of players';

-- Create index for faster leaderboard queries
CREATE INDEX IF NOT EXISTS `idx_best_score_desc` ON `user_game_stats` (`game_id`, `best_score` DESC);
CREATE INDEX IF NOT EXISTS `idx_avg_score_desc` ON `user_game_stats` (`game_id`, `average_score` DESC);
