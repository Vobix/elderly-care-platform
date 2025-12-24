-- Add missing games to games table
-- This ensures all games have proper game_id entries for game_sessions to reference

-- Insert missing games if they don't exist
INSERT IGNORE INTO `games` (`name`, `code`, `description`, `created_at`) VALUES
('Memory Match', 'memory', 'Test your memory by matching patterns and sequences', NOW()),
('Attention Focus', 'attention', 'Measure sustained attention and focus ability', NOW()),
('Puzzle Solver', 'puzzle', 'Challenge your problem-solving and spatial reasoning', NOW()),
('Tetris', 'tetris', 'Classic block-stacking game for spatial awareness', NOW()),
('Gem Match', 'gem_match', 'Match colorful gems in this pattern recognition game', NOW());

-- Verify all games are inserted
SELECT game_id, name, code FROM games ORDER BY game_id;
