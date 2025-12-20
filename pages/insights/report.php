<?php
/**
 * Weekly Summary Report
 */

$page_title = "Weekly Report";
require_once __DIR__ . '/../account/auth.php';
require_once __DIR__ . '/../../_header.php';
require_once __DIR__ . '/../../database/functions.php';

$user_id = $_SESSION['user_id'];

// Get data for the last 7 days
global $pdo;

// Mood data
$stmt = $pdo->prepare("SELECT * FROM mood_logs WHERE user_id = ? AND entry_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) ORDER BY entry_date DESC");
$stmt->execute([$user_id]);
$week_moods = $stmt->fetchAll();

// Game data - need to join with games and game_scores tables
$stmt = $pdo->prepare("
    SELECT gs.*, g.code as game_type, gsc.score,
           TIMESTAMPDIFF(SECOND, gs.started_at, gs.ended_at) as duration_seconds
    FROM game_sessions gs
    JOIN games g ON gs.game_id = g.game_id
    LEFT JOIN game_scores gsc ON gs.session_id = gsc.session_id
    WHERE gs.user_id = ? AND gs.started_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
");
$stmt->execute([$user_id]);
$week_games = $stmt->fetchAll();

// Calculate averages
$avg_mood = !empty($week_moods) ? array_sum(array_column($week_moods, 'mood_value')) / count($week_moods) : 0;
$avg_game_score = !empty($week_games) ? array_sum(array_column($week_games, 'score')) / count($week_games) : 0;
$total_game_time = !empty($week_games) ? array_sum(array_column($week_games, 'duration_seconds')) : 0;

// Mood correlation with games
$correlation_insight = "";
if (!empty($week_moods) && !empty($week_games)) {
    if ($avg_mood >= 4 && $avg_game_score >= 75) {
        $correlation_insight = "🌟 Great week! Your positive mood correlates with excellent game performance.";
    } elseif ($avg_mood < 3 && $avg_game_score < 60) {
        $correlation_insight = "💙 Lower mood may affect cognitive performance. Consider self-care activities.";
    } else {
        $correlation_insight = "👍 Keep maintaining balance between emotional wellness and cognitive activities.";
    }
}

require_once __DIR__ . '/../../_header.php';
?>

<link rel="stylesheet" href="/assets/css/report.css">

<div class="report-container">
    <div class="report-header">
        <h1 style="margin: 0;">📊 Your Weekly Report</h1>
        <p style="margin-top: 10px; font-size: 18px;"><?php echo date('F j', strtotime('-7 days')) . ' - ' . date('F j, Y'); ?></p>
    </div>
    
    <div class="metric-grid">
        <div class="metric-card">
            <div>😊 Average Mood</div>
            <div class="metric-value"><?php echo round($avg_mood, 1); ?>/5</div>
            <div><?php echo count($week_moods); ?> entries</div>
        </div>
        
        <div class="metric-card">
            <div>🎮 Games Played</div>
            <div class="metric-value"><?php echo count($week_games); ?></div>
            <div>Avg Score: <?php echo round($avg_game_score); ?></div>
        </div>
        
        <div class="metric-card">
            <div>⏱️ Total Game Time</div>
            <div class="metric-value"><?php echo round($total_game_time / 60); ?></div>
            <div>minutes</div>
        </div>
    </div>
    
    <?php if ($correlation_insight): ?>
    <div class="insight-box">
        <h3 style="margin-top: 0;">💡 Key Insight</h3>
        <p style="font-size: 18px; line-height: 1.6;"><?php echo $correlation_insight; ?></p>
    </div>
    <?php endif; ?>
    
    <div class="section">
        <h2>📈 Weekly Trends</h2>
        <?php if (!empty($week_moods)): ?>
            <h3>Mood Entries</h3>
            <div style="display: flex; gap: 10px; align-items: flex-end; height: 150px; margin: 20px 0;">
                <?php foreach (array_reverse(array_slice($week_moods, 0, 7)) as $mood): ?>
                    <div style="flex: 1; background: linear-gradient(to top, #667eea, #764ba2); border-radius: 5px 5px 0 0; height: <?php echo ($mood['mood_value'] / 5) * 100; ?>%; min-height: 20px; position: relative;">
                        <div style="position: absolute; bottom: -25px; left: 50%; transform: translateX(-50%); font-size: 11px; white-space: nowrap;">
                            <?php echo date('M j', strtotime($mood['entry_date'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No mood data for this week.</p>
        <?php endif; ?>
    </div>
    
    <div class="section">
        <h2>🎯 Recommendations</h2>
        <ul style="line-height: 2; padding-left: 25px;">
            <?php if ($avg_mood >= 4): ?>
                <li>✅ Excellent mood maintenance! Keep up your current activities.</li>
            <?php else: ?>
                <li>💙 Try mood-boosting activities like games, social interaction, or exercise.</li>
            <?php endif; ?>
            
            <?php if (count($week_games) >= 5): ?>
                <li>🎮 Great cognitive engagement! You're actively training your brain.</li>
            <?php else: ?>
                <li>🧠 Consider playing more cognitive games to boost mental fitness.</li>
            <?php endif; ?>
            
            <?php if ($avg_game_score >= 75): ?>
                <li>🌟 Outstanding game performance! Challenge yourself with harder difficulties.</li>
            <?php elseif ($avg_game_score >= 60): ?>
                <li>👍 Good progress! Keep practicing to improve your scores.</li>
            <?php else: ?>
                <li>💪 Keep trying! Consistent practice will improve your performance.</li>
            <?php endif; ?>
            
            <li>📋 Take regular wellness questionnaires to track mental health trends.</li>
            <li>📝 Log your mood daily for more accurate insights.</li>
        </ul>
    </div>
    
    <div style="text-align: center; margin-top: 30px;">
        <a href="dashboard.php" class="btn btn-primary">📊 Back to Dashboard</a>
        <a href="../diary.php" class="btn btn-secondary">📔 View Diary</a>
    </div>
</div>

<?php require_once __DIR__ . '/../../_footer.php'; ?>
