# Database Setup Instructions

## Quick Fix for "Undefined array key" Error

The error you're seeing means the games aren't in your database yet. Follow these steps:

### Option 1: Run the Quick Migration (Recommended)

1. Open phpMyAdmin or your MySQL client
2. Select your `elder_care` database
3. Run this SQL:

```sql
-- Add missing games
INSERT IGNORE INTO `games` (`name`, `code`, `description`, `created_at`) VALUES
('Memory Match', 'memory', 'Test your memory by matching patterns and sequences', NOW()),
('Attention Focus', 'attention', 'Measure sustained attention and focus ability', NOW()),
('Puzzle Solver', 'puzzle', 'Challenge your problem-solving and spatial reasoning', NOW()),
('Tetris', 'tetris', 'Classic block-stacking game for spatial awareness', NOW()),
('Gem Match', 'gem_match', 'Match colorful gems in this pattern recognition game', NOW());

-- Verify games were added
SELECT game_id, name, code FROM games ORDER BY game_id;
```

### Option 2: Import from Migration File

Run the SQL file: `database/migrations/add_missing_games.sql`

### Option 3: Recreate Database

If you want a fresh start with all games:
1. Backup your current database
2. Drop and recreate using `database/elder_care.sql`

## Do You Need to Restart XAMPP?

**No restart needed for:**
- PHP code changes
- CSS/JavaScript changes
- Adding data to existing tables

**Restart needed for:**
- PHP configuration changes (php.ini)
- Apache configuration changes
- MySQL configuration changes

**For this fix:** Just run the SQL above - no restart needed!

## Verify the Fix

After running the SQL:
1. Refresh your insights/dashboard page
2. Play a game (any game)
3. Check the dashboard again - graphs should now appear!

## Why This Happened

When you pulled the latest code, it included references to new games (tetris, gem_match) but your database didn't have these games yet. The code now has fallbacks to handle missing games gracefully, but it's better to have all games in the database.
