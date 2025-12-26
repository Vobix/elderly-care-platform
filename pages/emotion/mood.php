<?php
/**
 * Mood Tracking Page
 * Daily emotion logging with emoji scale
 * 
 * BASIC FLOW:
 * BF:1 - User clicks mood on navigation bar
 * BF:2 - System displays mood options [M1: Msg Select Mood]
 * BF:3 - User selects one mood option
 * BF:4 - System displays optional text box [M2: Msg Optional Notes]
 * BF:5 - User enters notes (optional)
 * BF:6 - User clicks Save Today's Mood
 * BF:7 - System validates mood selection [A1: No Mood Selected]
 * BF:8 - System saves mood entry [C1: Mood Save Rule]
 * BF:9 - System updates Recent Mood History [C2: History Update Rule]
 * BF:10 - System displays confirmation message [M3: Msg Mood Saved]
 * 
 * ALTERNATE FLOW:
 * A1: No Mood Selected
 * A1.1 - Display error [M4: Err No Mood Selected]
 * A1.2 - Return to BF:2 without saving
 * 
 * Messages:
 * M1: Please select your mood for today
 * M2: Add your thoughts (optional)
 * M3: Your mood has been recorded
 * M4: Please select a mood before saving
 * 
 * Constraints:
 * C1: Mood Save Rule - Entry must contain at least one selected mood
 * C2: History Update Rule - New entry appears at top of history
 * C3: Optional Notes Rule - Notes may be empty, entry still saves
 */

$page_title = "Track Your Mood";
require_once __DIR__ . '/../account/auth.php';
require_once __DIR__ . '/../../_header.php';
require_once __DIR__ . '/../../database/config.php';
require_once __DIR__ . '/../../database/dao/MoodDAO.php';
require_once __DIR__ . '/../../services/MoodService.php';

$user_id = $_SESSION['user_id'];

// Phase 3: Initialize DAO and pass to Service
$moodDAO = new MoodDAO($pdo);
$moodService = new MoodService($moodDAO);

// Get message constants from service
$messages = MoodService::getMessages();
$msg_select_mood = $messages['M1'];
$msg_optional_notes = $messages['M2'];

$success = '';
$error = '';

// Check if user already logged mood today
$today_mood = $moodService->getTodaysMood($user_id);

// Handle form submission (BF:6 - User clicks Save Today's Mood)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mood_value = $_POST['mood_level'] ?? null;
    $mood_text = $_POST['notes'] ?? null; // C3: Notes optional

    // Use MoodService - Enforces ALL constraints and flows (BF:6-10, A1, C1-C3)
    $result = $moodService->recordMood($user_id, $mood_value, $mood_text);

    if ($result['success']) {
        // BF:10: Display confirmation (M3)
        $success = $result['message'];

        // Refresh today's mood
        $today_mood = $moodService->getTodaysMood($user_id);
    } else {
        // A1.1: Display error (M4)
        $error = $result['message'];
        // A1.2: Returns to BF:2 without saving (form redisplays below)
    }
}

// BF:9 / C2: Get recent mood entries (newest first)
$recent_moods = $moodService->getRecentHistory($user_id, 7);

// Mood labels and emojis - using MoodService
$mood_data = MoodService::getMoodLevels();

require_once __DIR__ . '/../../_header.php';
?>

<link rel="stylesheet" href="/assets/css/mood.css">

<div class="mood-container">
    <h1 style="text-align: center; font-size: 42px; margin-bottom: 10px;">😊 How are you feeling today?</h1>
    <!-- M1: Msg Select Mood (BF:2) -->
    <p style="text-align: center; font-size: 18px; color: #666; margin-bottom: 30px;">
        <?php echo $msg_select_mood; ?>
    </p>

    <!-- BF:10: Success message (M3: Msg Mood Saved) -->
    <?php if ($success): ?>
        <div class="alert alert-success">✅ <?php echo $success; ?></div>
    <?php endif; ?>

    <!-- A1.1: Error message (M4: Err No Mood Selected) -->
    <?php if ($error): ?>
        <div class="alert alert-error">❌ <?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($today_mood): ?>
        <div class="today-mood">
            <h2>Today's Mood</h2>
            <div class="emoji"><?php echo $mood_data[$today_mood['mood_value']]['emoji']; ?></div>
            <h3><?php echo $mood_data[$today_mood['mood_value']]['label']; ?></h3>
            <p style="margin-top: 15px;">Logged on <?php echo date('F j, Y', strtotime($today_mood['created_at'])); ?></p>
            <?php if (!empty($today_mood['notes'])): ?>
                <div class="mood-note">
                    <strong>Your note:</strong> <?php echo htmlspecialchars($today_mood['notes']); ?>
                </div>
            <?php endif; ?>
            <p style="margin-top: 20px;">✅ You can update your mood for today</p>
        </div>
    <?php else: ?>
        <div class="mood-card">
            <form method="POST" action="">
                <h2 style="margin-bottom: 20px; text-align: center;">Select Your Mood</h2>

                <!-- BF:3: User selects one of 5 mood levels -->
                <div class="mood-scale">
                    <?php foreach ($mood_data as $level => $data): ?>
                        <div class="mood-option">
                            <label class="mood-button" style="border-color: <?php echo $data['color']; ?>;"
                                data-level="<?php echo $level; ?>">
                                <input type="radio" name="mood_level" value="<?php echo $level; ?>" required>
                                <div class="emoji-display"><?php echo $data['emoji']; ?></div>
                            </label>
                            <div class="mood-label"><?php echo $data['label']; ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- BF:4: User adds optional notes -->
                <div class="notes-area">
                    <!-- M2: Msg Optional Notes -->
                    <label for="notes">💭 <?php echo $msg_optional_notes; ?></label>
                    <textarea id="notes" name="notes"
                        placeholder="Share your thoughts, what made you feel this way, or anything on your mind..."></textarea>
                </div>

                <button type="submit" class="submit-btn">💾 Save Today's Mood</button>
            </form>
        </div>
    <?php endif; ?>

    <!-- BF:9: System displays Recent Mood History (newest first) -->
    <!-- C2: History Update Rule - Display newest entry at top (ORDER BY entry_date DESC) -->
    <?php if (!empty($recent_moods)): ?>
        <div class="recent-moods">
            <h3>📅 Recent Mood History</h3>
            <div class="mood-history">
                <?php foreach ($recent_moods as $mood): ?>
                    <div class="mood-entry"
                        style="background: linear-gradient(to right, <?php echo $mood_data[$mood['mood_value']]['color']; ?>20, transparent);">
                        <div class="emoji"><?php echo $mood_data[$mood['mood_value']]['emoji']; ?></div>
                        <div class="details">
                            <strong><?php echo $mood_data[$mood['mood_value']]['label']; ?></strong>
                            <div class="date"><?php echo date('F j, Y', strtotime($mood['created_at'])); ?></div>
                            <?php if (!empty($mood['mood_text'])): ?>
                                <p style="margin-top: 8px;"><?php echo htmlspecialchars($mood['mood_text']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="text-align: center; margin-top: 20px;">
                <a href="../diary.php" class="btn btn-primary">📔 View Full Diary</a>
                <a href="../insights/report.php" class="btn btn-primary"
                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); margin-left: 10px;">📊 View
                    Insights</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="/assets/js/mood.js"></script>

<?php require_once __DIR__ . '/../../_footer.php'; ?>