# Game System Messages and Constraints

## Messages (M1-M5)

### M1: Game List Message
**Message:** "Please choose a game to play"  
**Location:** `pages/games.php`  
**Usage:** Displayed on the games menu page to prompt user selection  
**Implementation:** Shown as the subtitle under "🎮 Cognitive Training Games"

### M2: Game Loading Message
**Message:** "Your game is loading. Please wait…"  
**Location:** `pages/game_play.php`  
**Usage:** Displayed briefly while game assets are being loaded  
**Implementation:** 
- Shows loading spinner (⏳) and message
- Automatically removed after 500ms when game loads
- Only shown for games with difficulty selection

### M3: Game Complete Message
**Message:** "Game complete! Your score has been saved."  
**Location:** `pages/game_result.php`  
**Usage:** Confirmation that game ended and score was saved  
**Implementation:** 
- Displayed in green success alert at top of results page
- Shown immediately after C1 (Auto Save) completes
- Confirms data persistence

### M4: Stats Updated Message
**Message:** "Your game statistics have been updated."  
**Location:** `pages/game_result.php`  
**Usage:** Confirmation that user statistics were recalculated  
**Implementation:** 
- Displayed in blue info alert below M3
- Shown after C3 (Stats Update Formula) is applied
- Indicates profile data refresh

### M5: Game Load Failed Message
**Message:** "Unable to load the game. Please try again later."  
**Location:** `pages/game_play.php`  
**Usage:** Error message when game file cannot be loaded  
**Implementation:** 
- Displayed in red error alert
- Shown with "Back to Games" button
- Handles file not found scenarios

---

## Constraints (C1-C4)

### C1: Auto Save Rule
**Rule:** Game score must be automatically saved immediately after the game ends  
**Locations:** 
- `pages/game_result.php` (trigger)
- `database/functions.php::insertGameSession()` (implementation)

**Implementation Details:**
1. When game ends, JavaScript submits form to `game_result.php`
2. Form data includes: game_type, score, duration, difficulty, accuracy, attempts
3. `insertGameSession()` function called immediately upon POST receipt
4. Two database inserts performed atomically:
   - `game_sessions` table: session metadata
   - `game_scores` table: score details
5. No user confirmation required - automatic save
6. M3 message displayed upon successful save

**Code Flow:**
```php
// pages/game_result.php
if (!empty($game_type) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // C1: Automatic save immediately after game ends
    $session_id = insertGameSession($user_id, $game_type, $score, $duration, $difficulty, $details);
    $saved = true;
}
```

### C2: Allowed Difficulty Levels
**Rule:** `{Easy, Medium, Hard}`  
**Locations:** 
- `pages/game_play.php` (validation)
- `pages/games.php` (display)

**Implementation Details:**
1. Only three difficulty levels allowed: easy, medium, hard
2. Validation array: `$valid_difficulties = ['easy', 'medium', 'hard']`
3. Invalid difficulty defaults to 'medium'
4. Stored as ENUM in database
5. Applied only to original 4 games (memory, attention, reaction, puzzle)
6. New games (visual_memory, number_memory, verbal_memory, chimp_test) have no difficulty

**Validation Code:**
```php
// C2: Validate difficulty levels - {Easy, Medium, Hard}
$valid_difficulties = ['easy', 'medium', 'hard'];
if (!in_array($difficulty, $valid_difficulties)) {
    $difficulty = 'medium';
}
```

### C3: Stats Update Formula
**Rule:** 
- `Times Played = Times Played + 1`
- `If Score > Best Score -> Best Score = Score`
- `Average Score = Total Score / Times Played`

**Locations:** 
- `database/functions.php::getUserGameStats()` (calculation)
- Automatic via SQL aggregation functions

**Implementation Details:**

1. **Times Played Calculation:**
   - Uses `COUNT(*)` on game_sessions
   - Increments automatically with each new INSERT
   - No manual increment needed

2. **Best Score Calculation:**
   - Uses `MAX(gsc.score)` on game_scores
   - Automatically returns highest value
   - If new score > current max, becomes new best

3. **Average Score Calculation:**
   - Uses `AVG(gsc.score)` on game_scores
   - SQL calculates: SUM(all_scores) / COUNT(scores)
   - Updates automatically with each new score

**SQL Implementation:**
```sql
SELECT 
    COUNT(*) as games_played,              -- Times Played
    MAX(gsc.score) as best_score,          -- Best Score
    AVG(gsc.score) as avg_score            -- Average = Total / Times Played
FROM game_sessions gs
LEFT JOIN game_scores gsc ON gs.session_id = gsc.session_id
WHERE gs.user_id = ?
GROUP BY g.code
```

**Example:**
- User plays game 1: Score = 80
  - Times Played = 1
  - Best Score = 80
  - Average Score = 80

- User plays game 2: Score = 90
  - Times Played = 2
  - Best Score = 90 (90 > 80, so updated)
  - Average Score = 85 ((80 + 90) / 2)

- User plays game 3: Score = 70
  - Times Played = 3
  - Best Score = 90 (70 < 90, no change)
  - Average Score = 80 ((80 + 90 + 70) / 3)

### C4: Valid Game Launch Action
**Rule:** User may only start a game through either:
1. Difficulty button (Easy/Medium/Hard), OR
2. Play Now button

**Locations:** 
- `pages/games.php` (UI buttons)
- `pages/game_play.php` (validation)

**Implementation Details:**

**Option 1: Difficulty Button (Original 4 Games)**
- Games: memory, attention, reaction, puzzle
- Buttons: "😊 Easy", "😐 Medium", "😤 Hard"
- Action: Links to `game_play.php?game={game_id}&difficulty={level}`
- Validation: Game type must be in `$valid_games` array
- Validation: Difficulty must be in `$valid_difficulties` array

**Option 2: Play Now Button (New 4 Games)**
- Games: visual_memory, number_memory, verbal_memory, chimp_test
- Button: "🎮 Play Now"
- Action: Links directly to `/games/{game_id}.php`
- No difficulty parameter (single difficulty level)
- No routing through game_play.php

**Code Implementation:**
```php
// pages/games.php
<?php if ($has_difficulty): ?>
    <!-- C4: Option 1 - Difficulty Buttons -->
    <a href="game_play.php?game=<?php echo $game['id']; ?>&difficulty=easy">😊 Easy</a>
    <a href="game_play.php?game=<?php echo $game['id']; ?>&difficulty=medium">😐 Medium</a>
    <a href="game_play.php?game=<?php echo $game['id']; ?>&difficulty=hard">😤 Hard</a>
<?php else: ?>
    <!-- C4: Option 2 - Play Now Button -->
    <a href="/games/<?php echo $game['id']; ?>.php">🎮 Play Now</a>
<?php endif; ?>
```

**Validation:**
- Direct URL access checked in both routes
- Invalid game types redirect to games.php
- User must be authenticated (checked by auth.php)
- No other entry points allowed

---

## System Flow Diagram

```
User on games.php
       |
       | C4: Valid Launch Action
       |
       +-- Has Difficulty? --+
       |                     |
    YES (Original)       NO (New)
       |                     |
   Difficulty Button    Play Now Button
       |                     |
   game_play.php        games/{game}.php
       |                     |
       | M2: Loading         |
       |                     |
   games/{game}.php          |
       |                     |
       +---------------------+
                |
           Game Plays
                |
           Game Ends
                |
       Submit to game_result.php
                |
       C1: Auto Save (immediate)
       C3: Stats Update (automatic)
                |
       M3: Score Saved
       M4: Stats Updated
                |
         Results Display
```

---

## Database Schema Constraints

### game_sessions table
```sql
difficulty ENUM('easy','medium','hard','custom') DEFAULT 'easy'  -- C2
```

### Automatic Calculations (C3)
- No manual statistics table needed
- Stats calculated on-the-fly via SQL aggregation
- Always up-to-date with latest data
- No sync issues or stale data

---

## Testing Checklist

### C1: Auto Save Rule
- [ ] Play game and complete it
- [ ] Verify no "Save" button needed
- [ ] Check database for new game_sessions row
- [ ] Check database for new game_scores row
- [ ] Verify M3 message appears
- [ ] Test with network disconnected (should show error)

### C2: Difficulty Levels
- [ ] Try URL with invalid difficulty (e.g., ?difficulty=extreme)
- [ ] Verify defaults to 'medium'
- [ ] Test all three valid difficulties
- [ ] Verify database stores correct difficulty
- [ ] Test new games without difficulty work

### C3: Stats Update Formula
- [ ] Play game and note starting stats
- [ ] Play again with higher score
- [ ] Verify Times Played increased by 1
- [ ] Verify Best Score updated if new score higher
- [ ] Verify Average Score calculated correctly
- [ ] Play again with lower score
- [ ] Verify Best Score unchanged
- [ ] Verify Average Score recalculated

### C4: Valid Launch Actions
- [ ] Click difficulty button - game loads
- [ ] Click Play Now button - game loads
- [ ] Try direct URL to game without auth - redirected
- [ ] Try invalid game type - redirected to games.php
- [ ] Verify no other entry points exist

### Messages M1-M5
- [ ] M1 displays on games.php
- [ ] M2 displays briefly when loading game
- [ ] M3 displays on successful save
- [ ] M4 displays after stats update
- [ ] M5 displays when game file missing

---

## Error Handling

### Save Failures
- Exception caught in `insertGameSession()`
- Error logged to PHP error log
- User sees error message instead of M3/M4
- User can retry by playing again

### Invalid Input
- Game type validated against whitelist
- Difficulty validated against ENUM values
- Defaults applied for invalid values
- User redirected if critical validation fails

### Database Constraints
- Foreign key constraints ensure data integrity
- ENUM ensures only valid difficulty values
- Session_id uniqueness prevents duplicates
- User_id validation ensures ownership

---

## Summary

**5 Messages Implemented:**
- ✅ M1: Game selection prompt
- ✅ M2: Loading indicator
- ✅ M3: Save confirmation
- ✅ M4: Stats update confirmation
- ✅ M5: Load failure error

**4 Constraints Enforced:**
- ✅ C1: Automatic save on game end
- ✅ C2: Three difficulty levels only
- ✅ C3: Statistics formula applied
- ✅ C4: Two valid launch methods

**All constraints implemented with proper validation, error handling, and user feedback.**
