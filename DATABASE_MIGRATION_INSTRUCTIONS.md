# Database Migration Instructions

## ⚠️ CRITICAL: READ BEFORE PROCEEDING

This migration contains **BREAKING CHANGES** that will rename columns and create new tables. Make sure to:
1. **BACKUP YOUR DATABASE** before running this migration
2. Stop your application server during migration
3. Test on a development database first

---

## Step 1: Backup Current Database

```sql
-- Create a backup (adjust paths as needed)
mysqldump -u your_username -p elder_care > elder_care_backup_$(date +%Y%m%d_%H%M%S).sql
```

Or use phpMyAdmin: Export → Go

---

## Step 2: Execute Migration SQL

### Option A: Command Line
```bash
mysql -u your_username -p elder_care < database/migrations/schema_fixes_and_enhancements.sql
```

### Option B: phpMyAdmin
1. Select `elder_care` database
2. Click **SQL** tab
3. Click **Choose File** and select `database/migrations/schema_fixes_and_enhancements.sql`
4. Click **Go**

### Option C: SQL Client (MySQL Workbench, HeidiSQL, etc.)
1. Open the SQL file
2. Execute all statements

---

## Step 3: Verify Migration Success

Run these queries to confirm:

```sql
-- Check users table columns
SHOW COLUMNS FROM users;
-- Should see: user_id, username, email, password, full_name, date_of_birth, 
--              is_admin, is_active, has_completed_initial_assessment, baseline_assessment_id

-- Check new tables exist
SHOW TABLES;
-- Should include: user_game_stats, baseline_assessments, admin_actions

-- Check standard questionnaires
SELECT questionnaire_id, name, short_code FROM questionnaires;
-- Should see: WHO5, PHQ9, GAD7, GDS15, PSQI, PSS4

-- Check games list
SELECT game_id, name, code FROM games;
-- Should see 7 games (no visual_memory or verbal_memory)
```

---

## Step 4: Create Admin User

```sql
-- Find an existing user to promote (replace user_id)
UPDATE users SET is_admin = 1 WHERE user_id = 1;

-- Or create a new admin user
INSERT INTO users (username, email, password, full_name, is_admin, is_active, created_at)
VALUES (
  'admin',
  'admin@elderly-care.com',
  '$2y$10$YourHashedPasswordHere',  -- Use password_hash('your_password', PASSWORD_DEFAULT) in PHP
  'System Administrator',
  1,  -- is_admin = true
  1,  -- is_active = true
  NOW()
);
```

**To generate hashed password**, create `generate_password.php`:
```php
<?php
echo password_hash('your_secure_password', PASSWORD_DEFAULT);
```

Run: `php generate_password.php` and copy the hash.

---

## Step 5: Test the Migration

### Test Admin Panel
1. Log in with admin credentials
2. Should redirect to `/pages/admin/index.php`
3. Test each admin page:
   - **Dashboard**: Check stats display
   - **Users**: Search, filter, view details, deactivate/reactivate
   - **Content**: Add/edit/delete games and questionnaires
   - **Analytics**: View all charts and apply filters

### Test Baseline Assessment
1. Register a new user
2. Should automatically redirect to PHQ-9 questionnaire
3. Complete questionnaire
4. Verify:
   - `baseline_assessments` table has new record
   - User's `has_completed_initial_assessment = 1`
   - Risk category displayed (low/moderate/high/critical)
   - User redirected to dashboard on next login (not back to questionnaire)

### Test Leaderboard
1. Play any game (e.g., Memory Game)
2. Complete the game
3. On result page, verify:
   - Leaderboard table displays top 10 players
   - Your rank is shown
   - Percentile message appears: "You are in the top X% of players!"
   - Score comparison message appears

### Test Regular User Login
1. Log in as non-admin user (is_admin = 0)
2. Should redirect to `/pages/insights/dashboard.php`
3. Should NOT be able to access `/pages/admin/*` pages

---

## Migration Details

### What This Migration Does

#### Column Renames (DAO Compatibility)
- `users.id` → `users.user_id`
- `users.password_hash` → `users.password`
- `mood_logs.mood_text` → `mood_logs.notes`
- `questionnaire_responses.response_id` → `questionnaire_responses.result_id`
- `questionnaire_responses.responses` → `questionnaire_responses.answers`
- `questionnaire_responses.taken_at` → `questionnaire_responses.completed_at`

#### New Columns
- `users.username` (VARCHAR(50), unique) - for leaderboard display
- `users.full_name` (VARCHAR(100)) - complete name
- `users.date_of_birth` (DATE) - for age-specific features
- `users.is_admin` (TINYINT(1), default 0) - admin flag
- `users.has_completed_initial_assessment` (TINYINT(1), default 0) - baseline tracking
- `users.baseline_assessment_id` (INT) - reference to baseline
- `questionnaires.type` (VARCHAR(50)) - questionnaire categorization
- `questionnaire_responses.interpretation` (JSON) - detailed results
- `game_scores.details` (JSON) - game-specific data

#### New Tables

**user_game_stats**
- Per-game statistics tracking
- Columns: stat_id, user_id, game_id, times_played, best_score, average_score, total_score, rank, percentile, last_played_at
- Replaces `user_game_summary` (old table)
- Supports leaderboard calculations

**baseline_assessments**
- Initial wellness assessment storage
- Columns: assessment_id, user_id, questionnaire_id, score, risk_category (ENUM), interpretation (JSON), responses (JSON), completed_at
- Links to users table
- Tracks risk categories: low, moderate, high, critical

**admin_actions**
- Admin activity audit log
- Columns: action_id, admin_user_id, action_type, target_user_id, description, metadata (JSON), created_at
- Tracks all admin operations
- Supports compliance and accountability

#### Data Additions
- 6 standard questionnaires (WHO5, PHQ9, GAD7, GDS15, PSQI, PSS4)
- card_flip game entry
- Removes duplicate games (visual_memory, verbal_memory)

---

## Rollback Instructions (If Needed)

If something goes wrong:

```bash
# Restore from backup
mysql -u your_username -p elder_care < elder_care_backup_YYYYMMDD_HHMMSS.sql
```

---

## Common Issues

### Issue: "Column 'user_id' already exists"
**Solution**: Migration already run. Check if tables/columns exist before re-running.

### Issue: Admin login redirects to regular dashboard
**Solution**: Make sure user has `is_admin = 1` in users table.

### Issue: New user registration doesn't redirect to questionnaire
**Solution**: Check that `has_completed_initial_assessment` column exists and defaults to 0.

### Issue: Leaderboard not showing
**Solution**: 
1. Verify `user_game_stats` table exists
2. Play a game to populate data
3. Check GameService calls `recalculateRanks()`

### Issue: Baseline assessment not saving
**Solution**: Verify `baseline_assessments` table exists and has all columns.

---

## Support

If you encounter issues:
1. Check error logs: `/var/log/mysql/error.log` or phpMyAdmin → SQL tab
2. Verify database user has ALTER TABLE permissions
3. Ensure MySQL version supports JSON columns (5.7.8+)
4. Check all FK constraints are satisfied

---

## Next Steps After Migration

1. ✅ Verify all tables and columns
2. ✅ Create at least one admin user
3. ✅ Test admin panel (all 3 use cases)
4. ✅ Test baseline assessment flow
5. ✅ Test leaderboard system
6. ✅ Monitor error logs for any issues
7. ✅ Update application documentation
8. ✅ Train admin users on new panel features

---

## Architecture Notes

All new features follow the existing 3-tier architecture:
- **Presentation Layer**: Admin pages, enhanced login/registration
- **Service Layer**: GameService with leaderboard integration
- **Data Layer**: UserDAO, GameDAO, QuestionnaireDAO, LeaderboardDAO

No breaking changes to existing Service or DAO interfaces (only additions).
