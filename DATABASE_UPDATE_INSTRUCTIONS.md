# Database Update Instructions

## Changes Made

### 1. Added Insights Navigation
- Added "View Insights" button on the mood tracking page ([pages/emotion/mood.php](pages/emotion/mood.php))
- Users can now easily navigate from mood tracking to the insights dashboard

### 2. Fixed Game Session Saving
- Updated [database/functions.php](database/functions.php) to properly save game sessions and scores
- Fixed timestamp handling for game sessions
- Added details field to game_scores for better data tracking

### 3. Added Missing Games to Database
- Updated schema to include all available games
- Games added: Memory Match, Attention Focus, Puzzle Solver, Tetris, Gem Match

## To Apply These Changes to Your Database:

### Option 1: Run the Migration File (Recommended)
1. Open phpMyAdmin or your MySQL client
2. Select your `elder_care` database
3. Go to the SQL tab
4. Copy and paste the contents of `database/migrations/add_missing_games.sql`
5. Click "Go" to execute

### Option 2: Run Individual SQL Commands
Execute this SQL in your database:

```sql
-- Add missing games
INSERT IGNORE INTO `games` (`name`, `code`, `description`, `created_at`) VALUES
('Memory Match', 'memory', 'Test your memory by matching patterns and sequences', NOW()),
('Attention Focus', 'attention', 'Measure sustained attention and focus ability', NOW()),
('Puzzle Solver', 'puzzle', 'Challenge your problem-solving and spatial reasoning', NOW()),
('Tetris', 'tetris', 'Classic block-stacking game for spatial awareness', NOW()),
('Gem Match', 'gem_match', 'Match colorful gems in this pattern recognition game', NOW());
```

### Option 3: Fresh Database Install
If you're setting up a new database or want to start fresh:
1. Drop the existing `elder_care` database (WARNING: This deletes all data!)
2. Create a new `elder_care` database
3. Import the updated `database/elder_care.sql` file

## Verification

After applying the changes, verify that:

1. **Games Table**: Run this query to check all games are present:
```sql
SELECT game_id, name, code FROM games ORDER BY game_id;
```

You should see 9 games:
- Reaction Time
- Memory Match
- Number Memory
- Attention Focus
- Chimp Test
- Card Flip
- Puzzle Solver
- Tetris
- Gem Match

2. **Test Game Saving**: 
   - Play any cognitive game
   - Check that game sessions are saved:
```sql
SELECT gs.session_id, g.name, gs.started_at, sc.score
FROM game_sessions gs
JOIN games g ON gs.game_id = g.game_id
LEFT JOIN game_scores sc ON gs.session_id = sc.session_id
ORDER BY gs.started_at DESC
LIMIT 10;
```

3. **Test Insights Page**:
   - Log some moods
   - Play a few games
   - Visit the insights/dashboard page
   - Verify that graphs and statistics are displayed

## Troubleshooting

### Games not showing in insights?
- Make sure you've played at least 2 games or logged 3 moods
- Check that game_sessions and game_scores tables have data

### Duplicate game error?
- The `INSERT IGNORE` statement prevents duplicates
- If you get errors, check existing games first:
```sql
SELECT * FROM games WHERE code IN ('memory', 'attention', 'puzzle', 'tetris', 'gem_match');
```

### Foreign key constraint errors?
- Make sure the games table is populated before playing games
- Game sessions require a valid game_id from the games table

## What's Fixed

### Before:
- ❌ No easy way to navigate from mood page to insights
- ❌ Game sessions not being saved properly (incorrect timestamp handling)
- ❌ Missing games in database causing foreign key errors
- ❌ Insights page showing "insufficient data" even with game plays

### After:
- ✅ "View Insights" button on mood page
- ✅ Game sessions properly saved with correct timestamps
- ✅ All 9 games properly registered in database
- ✅ Insights page displays graphs when games are played
- ✅ Better data tracking with details field in game_scores

## Next Steps

After applying these database changes:
1. Test playing each game to ensure scores are saved
2. Check the insights dashboard to see your game statistics
3. Verify mood tracking still works correctly
4. Check that all navigation links work properly

If you encounter any issues, check the browser console for JavaScript errors and the PHP error log for backend issues.
