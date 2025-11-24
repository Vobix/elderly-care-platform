<?php
/**
 * Mood Tracking Page
 * Daily emotion logging with emoji scale
 */

$page_title = "Track Your Mood";
require_once __DIR__ . '/../account/auth.php';
require_once __DIR__ . '/../../_header.php';
require_once __DIR__ . '/../../database/functions.php';

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Check if user already logged mood today
global $pdo;
$stmt = $pdo->prepare("SELECT * FROM mood_logs WHERE user_id = ? AND entry_date = CURDATE()");
$stmt->execute([$user_id]);
$today_mood = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mood_value = $_POST['mood_level'] ?? 0;
    $mood_text = sanitizeInput($_POST['notes'] ?? '');
    
    // Mood emojis mapping
    $emoji_map = [1 => '😢', 2 => '🙁', 3 => '😐', 4 => '🙂', 5 => '😄'];
    $mood_emoji = $emoji_map[$mood_value] ?? '';
    
    if ($mood_value >= 1 && $mood_value <= 5) {
        try {
            insertMood($user_id, $mood_value, $mood_emoji, $mood_text);
            $success = $today_mood ? "Your mood has been updated successfully! 🎉" : "Your mood has been logged successfully! 🎉";
            
            // Refresh to show today's mood
            $stmt->execute([$user_id]);
            $today_mood = $stmt->fetch();
        } catch (Exception $e) {
            $error = "Failed to save mood. Please try again.";
            error_log("Mood insert error: " . $e->getMessage());
        }
    } else {
        $error = "Please select a valid mood level.";
    }
}

// Get recent mood entries
$recent_moods = getRecentMood($user_id, 7);

// Mood labels and emojis
$mood_data = [
    1 => ['emoji' => '😢', 'label' => 'Very Sad', 'color' => '#dc3545'],
    2 => ['emoji' => '🙁', 'label' => 'Sad', 'color' => '#fd7e14'],
    3 => ['emoji' => '😐', 'label' => 'Okay', 'color' => '#ffc107'],
    4 => ['emoji' => '🙂', 'label' => 'Happy', 'color' => '#28a745'],
    5 => ['emoji' => '😄', 'label' => 'Very Happy', 'color' => '#20c997']
];

require_once __DIR__ . '/../../_header.php';
?>

<link rel="stylesheet" href="/assets/css/mood.css">

<div class="mood-container">
    <h1 style="text-align: center; font-size: 42px; margin-bottom: 10px;">😊 How are you feeling today?</h1>
    <p style="text-align: center; font-size: 18px; color: #666; margin-bottom: 30px;">
        Track your emotional wellness daily
    </p>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($today_mood): ?>
        <div class="today-mood">
            <h2>Today's Mood</h2>
            <div class="emoji"><?php echo $mood_data[$today_mood['mood_value']]['emoji']; ?></div>
            <h3><?php echo $mood_data[$today_mood['mood_value']]['label']; ?></h3>
            <p style="margin-top: 15px; color: #666;">Logged on <?php echo date('F j, Y', strtotime($today_mood['entry_date'])); ?></p>
            <?php if (!empty($today_mood['mood_text'])): ?>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-top: 15px; text-align: left;">
                    <strong>Your note:</strong> <?php echo htmlspecialchars($today_mood['mood_text']); ?>
                </div>
            <?php endif; ?>
            <p style="margin-top: 20px; color: #666;">✅ You can update your mood for today</p>
        </div>
    <?php else: ?>
        <div class="mood-card">
            <form method="POST" action="">
                <h2 style="margin-bottom: 20px; text-align: center;">Select Your Mood</h2>
                
                <div class="mood-scale">
                    <?php foreach ($mood_data as $level => $data): ?>
                        <div class="mood-option">
                            <label class="mood-button" style="border-color: <?php echo $data['color']; ?>;" data-level="<?php echo $level; ?>">
                                <input type="radio" name="mood_level" value="<?php echo $level; ?>" required>
                                <div class="emoji-display"><?php echo $data['emoji']; ?></div>
                            </label>
                            <div class="mood-label"><?php echo $data['label']; ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="notes-area">
                    <label for="notes">💭 How are you feeling? (Optional)</label>
                    <textarea id="notes" name="notes" placeholder="Share your thoughts, what made you feel this way, or anything on your mind..."></textarea>
                </div>
                
                <button type="submit" class="submit-btn">💾 Save Today's Mood</button>
            </form>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($recent_moods)): ?>
        <div class="recent-moods">
            <h3>📅 Recent Mood History</h3>
            <div class="mood-history">
                <?php foreach ($recent_moods as $mood): ?>
                    <div class="mood-entry" style="border-left-color: <?php echo $mood_data[$mood['mood_value']]['color']; ?>;">
                        <div class="emoji"><?php echo $mood_data[$mood['mood_value']]['emoji']; ?></div>
                        <div class="details">
                            <strong><?php echo $mood_data[$mood['mood_value']]['label']; ?></strong>
                            <div class="date"><?php echo date('F j, Y', strtotime($mood['entry_date'])); ?></div>
                            <?php if (!empty($mood['mood_text'])): ?>
                                <p style="margin-top: 8px;"><?php echo htmlspecialchars($mood['mood_text']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="text-align: center; margin-top: 20px;">
                <a href="../diary.php" class="btn btn-primary">📔 View Full Diary</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="/assets/js/mood.js"></script>

<?php require_once __DIR__ . '/../../_footer.php'; ?>
