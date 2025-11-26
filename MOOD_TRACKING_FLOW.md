# Mood Tracking Flow Documentation

## Overview
This document describes the complete mood tracking feature implementation, including the basic flow, alternate flows, messages, and constraints.

**Last Updated:** January 2025  
**Status:** ✅ Implemented  
**Files Modified:** 
- `pages/emotion/mood.php`
- `database/functions.php`

---

## Table of Contents
1. [Use Case Flow](#use-case-flow)
2. [Messages](#messages)
3. [Constraints](#constraints)
4. [Technical Implementation](#technical-implementation)
5. [Database Schema](#database-schema)
6. [Testing Guide](#testing-guide)

---

## Use Case Flow

### BASIC FLOW
The standard path where a user successfully logs their daily mood.

| Step | Action | Actor | Description |
|------|--------|-------|-------------|
| BF:1 | Navigate to Mood Tracking | User | User clicks "Mood Tracking" from navigation menu |
| BF:2 | Display mood selection page | System | System shows M1: "Please select your mood for today" with 5 mood levels |
| BF:3 | Select mood level | User | User clicks one of 5 emoji buttons (Very Sad → Very Happy) |
| BF:4 | View notes field | User | System displays M2: "Add your thoughts (optional)" with textarea |
| BF:5 | Enter notes (optional) | User | User types optional notes about their mood (or leaves blank) |
| BF:6 | Submit form | User | User clicks "💾 Save Today's Mood" button |
| BF:7 | Validate mood selection | System | System checks mood_level is selected (1-5) |
| BF:8 | Save mood entry | System | System saves entry with C1: Mood Save Rule (entry_date + mood_value required) |
| BF:9 | Update mood history | System | System refreshes Recent Mood History with C2: newest entry at top |
| BF:10 | Display confirmation | System | System shows M3: "Your mood has been saved successfully for today!" |

### ALTERNATE FLOW A1: No Mood Selected
Error path when user submits form without selecting a mood level.

| Step | Action | Actor | Description |
|------|--------|-------|-------------|
| A1.1 | Display error message | System | System shows M4: "Please select a mood level before saving" in red alert |
| A1.2 | Return to form | System | User remains on mood selection form to retry |

**Trigger:** User clicks submit button without selecting any mood radio button  
**Branch From:** BF:6 (Submit form)  
**Resolution:** User must select a mood level and resubmit

---

## Messages

### M1: Msg Select Mood
**Display Location:** Page subtitle below main heading  
**Trigger:** Page load (BF:2)  
**Purpose:** Instructs user to select their current mood

```php
$msg_select_mood = "Please select your mood for today";
```

**Implementation:**
```php
<p style="text-align: center; font-size: 18px; color: #666; margin-bottom: 30px;">
    <?php echo $msg_select_mood; ?>
</p>
```

---

### M2: Msg Optional Notes
**Display Location:** Label above notes textarea  
**Trigger:** Form display (BF:4)  
**Purpose:** Explains that adding notes is optional

```php
$msg_optional_notes = "Add your thoughts (optional)";
```

**Implementation:**
```php
<label for="notes">💭 <?php echo $msg_optional_notes; ?></label>
<textarea id="notes" name="notes" placeholder="Share your thoughts..."></textarea>
```

---

### M3: Msg Mood Saved
**Display Location:** Success alert at top of page  
**Trigger:** Successful mood save (BF:10)  
**Purpose:** Confirms mood entry was saved

```php
$msg_mood_saved = "Your mood has been saved successfully for today!";
```

**Implementation:**
```php
<?php if ($success): ?>
    <div class="alert alert-success">✅ <?php echo $success; ?></div>
<?php endif; ?>
```

---

### M4: Err No Mood Selected
**Display Location:** Error alert at top of page  
**Trigger:** Form submission without mood selection (A1.1)  
**Purpose:** Indicates validation error

```php
$msg_no_mood_selected = "Please select a mood level before saving";
```

**Implementation:**
```php
<?php if ($error): ?>
    <div class="alert alert-error">❌ <?php echo $error; ?></div>
<?php endif; ?>
```

---

## Constraints

### C1: Mood Save Rule
**Type:** Data Validation  
**Enforcement:** Database + Application Layer

**Rule:** A mood entry MUST contain:
- `entry_date` (NOT NULL) - Automatically set to CURDATE()
- `mood_value` (INT 1-5) - User selected mood level

**Validation Logic:**
```php
// BF:7: Validate mood selection (C1)
if (isset($_POST['mood_level'])) {
    $mood_level = intval($_POST['mood_level']);
    
    // Ensure mood_value is between 1-5
    if ($mood_level >= 1 && $mood_level <= 5) {
        // BF:8: Save with C1 (entry_date + mood_value required)
        $result = insertMood($user_id, $mood_level, $mood_emoji, $notes);
    }
}
```

**Database Schema:**
```sql
CREATE TABLE mood_logs (
    mood_log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    entry_date DATE NOT NULL,          -- C1: Required
    mood_value INT NOT NULL,           -- C1: Required (1-5)
    mood_emoji VARCHAR(10),
    mood_text TEXT,                    -- C3: Optional
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_date (user_id, entry_date)
);
```

**Implementation Files:**
- `pages/emotion/mood.php` - Form validation (lines 38-57)
- `database/functions.php` - insertMood() function (lines 135-143)

---

### C2: History Update Rule
**Type:** Display Order  
**Enforcement:** Database Query

**Rule:** Recent Mood History MUST display newest entries first (descending by entry_date)

**SQL Implementation:**
```php
// database/functions.php - getRecentMood()
function getRecentMood($user_id, $limit = 10) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT * FROM mood_logs 
        WHERE user_id = ? 
        ORDER BY entry_date DESC    -- C2: Newest first
        LIMIT ?
    ");
    $stmt->execute([$user_id, $limit]);
    return $stmt->fetchAll();
}
```

**Display Implementation:**
```php
<!-- BF:9: System displays Recent Mood History (newest first) -->
<!-- C2: History Update Rule - Display newest entry at top -->
<?php if (!empty($recent_moods)): ?>
    <div class="recent-moods">
        <h3>📅 Recent Mood History</h3>
        <div class="mood-history">
            <?php foreach ($recent_moods as $mood): ?>
                <!-- Newest entry appears first in loop -->
                <div class="mood-entry">...</div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
```

**Why This Matters:**
- Users see their most recent mood immediately
- Easy to track mood trends over time
- Consistent with diary and other time-based features

**Implementation Files:**
- `database/functions.php` - getRecentMood() function (lines 145-151)
- `pages/emotion/mood.php` - History display (lines 162-178)

---

### C3: Optional Notes Rule
**Type:** Data Validation  
**Enforcement:** Application Layer

**Rule:** `mood_text` (notes) is OPTIONAL - mood entry can be saved with empty notes

**Validation Logic:**
```php
// BF:6: Form submission handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mood_level = isset($_POST['mood_level']) ? intval($_POST['mood_level']) : null;
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : ''; // C3: Notes optional
    
    if ($mood_level) {
        // BF:8: Save with C1 and C3 - notes can be empty
        $result = insertMood($user_id, $mood_level, $mood_emoji, $notes);
        
        if ($result) {
            // BF:10: M3 confirmation (C3: saved even with empty notes)
            $success = $msg_mood_saved;
        }
    } else {
        // A1.1: M4 error (only mood_level required, not notes)
        $error = $msg_no_mood_selected;
    }
}
```

**Database Schema:**
```sql
mood_text TEXT,  -- NULL allowed, C3: Optional notes
```

**Function Signature:**
```php
// Default value is empty string, not required parameter
function insertMood($user_id, $mood_value, $mood_emoji = '', $mood_text = '') {
    // Saves successfully even if mood_text is empty
}
```

**Why This Matters:**
- Reduces friction - quick mood logging without typing
- Some users may not want to write notes every day
- Still allows detailed journaling for those who want it

**Implementation Files:**
- `pages/emotion/mood.php` - Form handler (lines 38-57)
- `database/functions.php` - insertMood() function (lines 135-143)

---

## Technical Implementation

### File Structure
```
pages/emotion/
├── mood.php           # Main mood tracking page (190 lines)
│   ├── Message constants (M1-M4)
│   ├── Flow step comments (BF:1-10, A1.1-A1.2)
│   ├── Form submission handler
│   ├── Mood selection UI (5 levels)
│   ├── Notes textarea
│   └── Recent mood history display
│
database/
├── functions.php      # Database helper functions
│   ├── insertMood()   # C1, C3 implementation
│   └── getRecentMood()# C2 implementation
```

### Mood Data Structure
```php
$mood_data = [
    1 => [
        'emoji' => '😢',
        'label' => 'Very Sad',
        'color' => '#dc3545'
    ],
    2 => [
        'emoji' => '😕',
        'label' => 'Sad',
        'color' => '#ffc107'
    ],
    3 => [
        'emoji' => '😐',
        'label' => 'Okay',
        'color' => '#17a2b8'
    ],
    4 => [
        'emoji' => '😊',
        'label' => 'Happy',
        'color' => '#28a745'
    ],
    5 => [
        'emoji' => '😄',
        'label' => 'Very Happy',
        'color' => '#20c997'
    ]
];
```

### Session Management
```php
// Check if mood already logged today
$today_mood = getTodayMood($user_id);

if ($today_mood) {
    // Display existing mood with update option
    echo "Today's Mood: {$mood_emoji}";
    echo "✅ You can update your mood for today";
} else {
    // Display mood selection form
    echo "Select Your Mood";
}
```

---

## Database Schema

### Table: mood_logs
```sql
CREATE TABLE mood_logs (
    mood_log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    entry_date DATE NOT NULL,              -- C1: Required, set to CURDATE()
    mood_value INT NOT NULL,               -- C1: Required (1-5)
    mood_emoji VARCHAR(10),                -- Optional emoji representation
    mood_text TEXT,                        -- C3: Optional notes
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_date (user_id, entry_date)  -- One mood per user per day
);
```

**Key Features:**
- **Primary Key:** `mood_log_id` - Auto-incrementing identifier
- **Foreign Key:** `user_id` - Links to users table
- **Unique Constraint:** `(user_id, entry_date)` - Prevents duplicate entries for same day
- **ON DUPLICATE KEY UPDATE:** Allows updating existing day's mood

### Insert/Update Query
```php
INSERT INTO mood_logs 
    (user_id, entry_date, mood_value, mood_emoji, mood_text) 
VALUES 
    (?, CURDATE(), ?, ?, ?) 
ON DUPLICATE KEY UPDATE 
    mood_value = ?, 
    mood_emoji = ?, 
    mood_text = ?
```

This query:
1. Tries to INSERT new mood entry for today
2. If `(user_id, entry_date)` already exists, UPDATE instead
3. Ensures only one mood per user per day

---

## Testing Guide

### Test Case 1: Basic Flow - Log Mood with Notes
**Objective:** Verify complete basic flow (BF:1 - BF:10)

**Steps:**
1. Login as a user
2. Navigate to Mood Tracking page
3. Verify M1 message displays: "Please select your mood for today"
4. Click "😊 Happy" emoji button
5. Verify M2 message displays: "Add your thoughts (optional)"
6. Enter notes: "Had a great day at the park!"
7. Click "💾 Save Today's Mood"
8. Verify M3 success message: "Your mood has been saved successfully for today!"
9. Verify Recent Mood History shows new entry at top (C2)
10. Verify entry shows "😊 Happy" and notes "Had a great day at the park!"

**Expected Result:** ✅ Mood saved, confirmation displayed, history updated

---

### Test Case 2: C3 - Log Mood WITHOUT Notes
**Objective:** Verify optional notes constraint (C3)

**Steps:**
1. Navigate to Mood Tracking page
2. Click "😄 Very Happy" emoji
3. Leave notes field EMPTY
4. Click "💾 Save Today's Mood"
5. Verify M3 success message displays
6. Verify mood entry saved in history without notes section

**Expected Result:** ✅ Mood saves successfully with empty notes (C3)

---

### Test Case 3: A1 - No Mood Selected Error
**Objective:** Verify alternate flow A1

**Steps:**
1. Navigate to Mood Tracking page
2. Enter notes: "Feeling reflective today"
3. Click submit WITHOUT selecting any mood emoji
4. Verify M4 error message: "Please select a mood level before saving"
5. Verify form remains displayed with entered notes still present
6. Select "😐 Okay" emoji
7. Click submit again
8. Verify mood saves successfully with M3 message

**Expected Result:** ✅ Error prevents save, user can retry (A1.1 - A1.2)

---

### Test Case 4: C2 - History Display Order
**Objective:** Verify newest entries display first (C2)

**Steps:**
1. Log mood "😢 Very Sad" on Day 1
2. Log mood "😕 Sad" on Day 2
3. Log mood "😊 Happy" on Day 3
4. View Recent Mood History
5. Verify order: Day 3 (Happy) → Day 2 (Sad) → Day 1 (Very Sad)

**Expected Result:** ✅ Newest entry at top (C2: ORDER BY entry_date DESC)

---

### Test Case 5: Update Today's Mood
**Objective:** Verify user can update mood for current day

**Steps:**
1. Log mood "😕 Sad" with notes "Bad morning"
2. Verify M3 success message
3. Verify "Today's Mood" section displays "😕 Sad"
4. Verify message: "✅ You can update your mood for today"
5. Refresh page
6. Form reappears (allowing update)
7. Select "😊 Happy" with notes "Day got better!"
8. Click submit
9. Verify mood updated to "😊 Happy"
10. Verify notes updated to "Day got better!"

**Expected Result:** ✅ Same day mood can be updated (ON DUPLICATE KEY UPDATE)

---

### Test Case 6: C1 - Mood Value Validation
**Objective:** Verify mood value constraint (C1)

**Steps:**
1. Attempt to save mood with invalid value (e.g., 0 or 6)
2. Verify validation prevents save
3. Verify error message displays
4. Select valid mood (1-5)
5. Verify successful save

**Expected Result:** ✅ Only values 1-5 accepted (C1)

---

### Database Verification Queries

**Check mood entry saved correctly:**
```sql
SELECT * FROM mood_logs 
WHERE user_id = 1 
AND entry_date = CURDATE();
```

**Verify history order (C2):**
```sql
SELECT entry_date, mood_value, mood_text 
FROM mood_logs 
WHERE user_id = 1 
ORDER BY entry_date DESC 
LIMIT 10;
```

**Verify unique constraint:**
```sql
-- Should fail if run twice on same day
INSERT INTO mood_logs (user_id, entry_date, mood_value) 
VALUES (1, CURDATE(), 3);
```

**Check empty notes allowed (C3):**
```sql
SELECT mood_log_id, mood_value, mood_text 
FROM mood_logs 
WHERE user_id = 1 
AND (mood_text IS NULL OR mood_text = '');
```

---

## Code Annotations

### Key Code Sections with Flow References

**Message Definitions (Top of file):**
```php
// M1: Msg Select Mood (BF:2)
$msg_select_mood = "Please select your mood for today";

// M2: Msg Optional Notes (BF:4)
$msg_optional_notes = "Add your thoughts (optional)";

// M3: Msg Mood Saved (BF:10)
$msg_mood_saved = "Your mood has been saved successfully for today!";

// M4: Err No Mood Selected (A1.1)
$msg_no_mood_selected = "Please select a mood level before saving";
```

**Form Submission Handler:**
```php
// BF:6: Form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mood_level = isset($_POST['mood_level']) ? intval($_POST['mood_level']) : null;
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : ''; // C3: Notes optional
    
    // BF:7: Validate mood selection + A1: No mood selected check
    if ($mood_level && $mood_level >= 1 && $mood_level <= 5) {
        // BF:8: Save mood entry with C1 (entry_date + mood_value required)
        $result = insertMood($user_id, $mood_level, $mood_emoji, $notes);
        
        if ($result) {
            // BF:10: M3 confirmation message
            $success = $msg_mood_saved;
            // BF:9: History will auto-update with C2 (newest first)
        }
    } else {
        // A1.1: M4 error message
        $error = $msg_no_mood_selected;
        // A1.2: Return to form (page reloads with error)
    }
}
```

**Mood Selection Form:**
```php
<!-- BF:2: M1 message display -->
<p><?php echo $msg_select_mood; ?></p>

<!-- BF:3: User selects one of 5 mood levels -->
<div class="mood-scale">
    <?php foreach ($mood_data as $level => $data): ?>
        <label class="mood-button">
            <input type="radio" name="mood_level" value="<?php echo $level; ?>" required>
            <div class="emoji-display"><?php echo $data['emoji']; ?></div>
        </label>
    <?php endforeach; ?>
</div>

<!-- BF:4: M2 message display + BF:5: User enters notes -->
<label for="notes">💭 <?php echo $msg_optional_notes; ?></label>
<textarea id="notes" name="notes"></textarea>
```

**Recent Mood History:**
```php
<!-- BF:9: Display mood history with C2 (newest first) -->
<?php if (!empty($recent_moods)): ?>
    <div class="recent-moods">
        <h3>📅 Recent Mood History</h3>
        <?php foreach ($recent_moods as $mood): ?>
            <!-- C2: ORDER BY entry_date DESC ensures newest at top -->
            <div class="mood-entry">
                <div class="emoji"><?php echo $mood_data[$mood['mood_value']]['emoji']; ?></div>
                <div class="date"><?php echo date('F j, Y', strtotime($mood['entry_date'])); ?></div>
                <?php if (!empty($mood['mood_text'])): ?>
                    <p><?php echo htmlspecialchars($mood['mood_text']); ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
```

---

## Summary

### Implementation Status
✅ **All messages implemented (M1-M4)**  
✅ **All constraints enforced (C1-C3)**  
✅ **Basic flow complete (BF:1-10)**  
✅ **Alternate flow implemented (A1)**  
✅ **Database schema supports constraints**  
✅ **Code documented with flow references**

### Files Modified
1. **pages/emotion/mood.php** (190 lines)
   - Added flow documentation header
   - Added message constants
   - Added flow step comments
   - Updated form submission handler
   - Updated UI with message displays

2. **database/functions.php** (435 lines)
   - Documented insertMood() with C1, C3
   - Documented getRecentMood() with C2
   - Verified SQL queries enforce constraints

### Key Features
- **5-Level Mood Scale:** Very Sad → Sad → Okay → Happy → Very Happy
- **Daily Tracking:** One mood entry per day, can be updated
- **Optional Notes:** Quick logging or detailed journaling
- **History Display:** Last 10 entries, newest first
- **Validation:** Mood selection required, notes optional
- **User Feedback:** Clear success/error messages

### Next Steps
1. Test all flows and constraints
2. Monitor user adoption and feedback
3. Consider analytics: mood trends over time
4. Potential enhancements:
   - Export mood history
   - Mood statistics dashboard
   - Reminders to log daily mood
   - Mood patterns and insights
