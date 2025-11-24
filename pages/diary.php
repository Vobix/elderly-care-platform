<?php
/**
 * Diary Page - View all mood history
 */

$page_title = "Mood Diary";
require_once __DIR__ . '/account/auth.php';
require_once __DIR__ . '/../_header.php';
require_once __DIR__ . '/../database/functions.php';

$user_id = $_SESSION['user_id'];

// Get all mood entries
global $pdo;
$stmt = $pdo->prepare("SELECT * FROM mood_logs WHERE user_id = ? ORDER BY entry_date DESC");
$stmt->execute([$user_id]);
$moods = $stmt->fetchAll();

// Get mood stats
$mood_stats = getMoodStats($user_id, 30);

$mood_data = [
    1 => ['emoji' => '😢', 'label' => 'Very Sad', 'color' => '#dc3545'],
    2 => ['emoji' => '🙁', 'label' => 'Sad', 'color' => '#fd7e14'],
    3 => ['emoji' => '😐', 'label' => 'Okay', 'color' => '#ffc107'],
    4 => ['emoji' => '🙂', 'label' => 'Happy', 'color' => '#28a745'],
    5 => ['emoji' => '😄', 'label' => 'Very Happy', 'color' => '#20c997']
];

require_once __DIR__ . '/../_header.php';
?>

<link rel="stylesheet" href="/assets/css/diary.css">

<h1 style="text-align: center;">📔 Your Mood Diary</h1>

<?php if ($mood_stats): ?>
<div class="stats-grid">
    <div class="stat-card">
        <div>📊 Average Mood</div>
        <div class="value"><?php echo round($mood_stats['avg_mood'], 1); ?>/5</div>
    </div>
    <div class="stat-card">
        <div>📝 Total Entries</div>
        <div class="value"><?php echo $mood_stats['total_entries']; ?></div>
    </div>
</div>
<?php endif; ?>

<div style="margin: 30px 0;">
    <a href="emotion/mood.php" class="btn btn-primary">➕ Log Today's Mood</a>
</div>

<h2 style="margin-top: 30px;">📋 Mental Health Assessments</h2>
<p style="color: #666; margin-bottom: 20px;">Take validated clinical questionnaires to track your mental wellness over time.</p>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-bottom: 30px;">
    <a href="emotion/questionnaire.php?type=wellbeing" class="btn btn-success" style="padding: 15px;">
        😊 Well-Being (WHO-5)
    </a>
    <a href="emotion/questionnaire.php?type=depression" class="btn btn-info" style="padding: 15px;">
        🧠 Depression Screening (GDS-15)
    </a>
    <a href="emotion/questionnaire.php?type=mood" class="btn btn-primary" style="padding: 15px;">
        � Mood Assessment (PHQ-9)
    </a>
    <a href="emotion/questionnaire.php?type=anxiety" class="btn btn-warning" style="padding: 15px;">
        😰 Anxiety Screening (GAD-7)
    </a>
    <a href="emotion/questionnaire.php?type=stress" class="btn btn-secondary" style="padding: 15px;">
        😓 Stress Assessment (PSS-4)
    </a>
    <a href="emotion/questionnaire.php?type=sleep" class="btn" style="padding: 15px; background: #6f42c1; color: white;">
        😴 Sleep Quality (PSQI)
    </a>
</div>

<?php if (empty($moods)): ?>
    <div class="alert alert-info">No mood entries yet. Start tracking your mood today!</div>
<?php else: ?>
    <h2>All Entries</h2>
    <?php foreach ($moods as $mood): ?>
        <div class="mood-entry" style="border-left-color: <?php echo $mood_data[$mood['mood_value']]['color']; ?>;">
            <div class="emoji"><?php echo $mood_data[$mood['mood_value']]['emoji']; ?></div>
            <div class="details">
                <h3><?php echo $mood_data[$mood['mood_value']]['label']; ?></h3>
                <p style="color: #666; font-size: 14px;"><?php echo date('F j, Y', strtotime($mood['entry_date'])); ?></p>
                <?php if (!empty($mood['mood_text'])): ?>
                    <p style="margin-top: 10px;"><?php echo htmlspecialchars($mood['mood_text']); ?></p>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../_footer.php'; ?>
