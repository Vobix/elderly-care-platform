<?php
/**
 * Dashboard - Main Analytics Page
 * UC04: View Insights - with M4 insufficient data check
 */

$page_title = "Dashboard";
require_once __DIR__ . '/../account/auth.php';
require_once __DIR__ . '/../../_header.php';
require_once __DIR__ . '/../../database/functions.php';

$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

// Get mood stats
$mood_stats = getMoodStats($user_id, 7);
$recent_moods = getRecentMood($user_id, 7);

// Get game stats
$game_stats = getUserGameStats($user_id);
$recent_games = getRecentGameSessions($user_id, 5);

// Get questionnaire results
$questionnaire_results = getQuestionnaireResults($user_id);

// UC04 - M4: Check if sufficient data exists to generate charts (C1: Auto-Refresh Rule)
$hasMinimumData = (count($recent_moods) >= 3 || count($game_stats) >= 2 || count($questionnaire_results) >= 1);

// Calculate best performing game
$best_game = null;
$best_score = 0;
foreach ($game_stats as $stat) {
    if ($stat['avg_score'] > $best_score) {
        $best_score = $stat['avg_score'];
        $best_game = $stat;
    }
}

$game_names = [
    'memory' => '🧠 Memory Match',
    'attention' => '👁️ Attention Focus',
    'reaction' => '⚡ Reaction Time',
    'puzzle' => '🧩 Puzzle Solver',
    'card_flip' => '🃏 Card Flip',
    'number_memory' => '🔢 Number Memory',
    'chimp_test' => '🐵 Chimp Test'
    ];
?>

<link rel="stylesheet" href="/assets/css/dashboard.css">

<div class="dashboard-header">
    <div class="welcome">Welcome back, <?php echo htmlspecialchars($user['full_name'] ?? $user['email']); ?>! 👋</div>
    <p style="font-size: 18px; color: #666;">Here's your wellness overview</p>
</div>

<?php if (!$hasMinimumData): ?>
    <!-- UC04 - M4: Err Insufficient Data message -->
    <div style="background: #fff3cd; border: 2px solid #ffc107; padding: 30px; border-radius: 15px; text-align: center; margin: 30px 0;">
        <h2 style="color: #856404; margin-top: 0;">📊 Not enough data to show insights</h2>
        <p style="font-size: 18px; color: #856404; line-height: 1.8;">
            Please complete more cognitive questionnaires and mood entries.<br>
            <strong>We need at least:</strong> 3 mood entries OR 2 game sessions OR 1 questionnaire to generate meaningful insights.
        </p>
        <div class="quick-actions" style="margin-top: 30px; justify-content: center;">
            <a href="../emotion/mood.php" class="action-btn" style="background: #667eea;">😊 Log Your Mood</a>
            <a href="../games.php" class="action-btn" style="background: #764ba2;">🎮 Play a Game</a>
            <a href="../emotion/questionnaire.php" class="action-btn" style="background: #f093fb;">📋 Take Questionnaire</a>
        </div>
    </div>
<?php else: ?>
    <!-- Show normal dashboard content when sufficient data exists -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="icon">😊</div>
            <div class="label">Avg Mood (7 days)</div>
            <div class="value"><?php echo $mood_stats ? round($mood_stats['avg_mood'], 1) : '-'; ?>/5</div>
        </div>
        
        <div class="stat-card">
            <div class="icon">🎮</div>
            <div class="label">Games Played</div>
            <div class="value"><?php echo array_sum(array_column($game_stats, 'games_played')); ?></div>
        </div>
        
        <div class="stat-card">
            <div class="icon">📝</div>
            <div class="label">Mood Entries</div>
            <div class="value"><?php echo $mood_stats ? $mood_stats['total_entries'] : 0; ?></div>
        </div>
        
        <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='../emotion/questionnaire_history.php'">
            <div class="icon">📋</div>
            <div class="label">Questionnaires</div>
            <div class="value"><?php echo count($questionnaire_results); ?></div>
            <div style="font-size: 12px; color: #667eea; margin-top: 5px;">Click to view history →</div>
        </div>
    </div>

    <div class="quick-actions">
        <a href="../emotion/mood.php" class="action-btn">😊 Log Mood</a>
        <a href="../games.php" class="action-btn">🎮 Play Games</a>
        <a href="../emotion/questionnaire.php" class="action-btn">📋 Questionnaire</a>
        <a href="../emotion/questionnaire_history.php" class="action-btn">📊 History</a>
        <a href="../diary.php" class="action-btn">📔 View Diary</a>
    </div>

    <?php if (!empty($recent_moods)): ?>
    <div class="section">
        <h2>📊 Mood Trend (Last 7 Days)</h2>
        <div class="mood-trend">
            <?php foreach (array_reverse($recent_moods) as $mood): ?>
                <div class="mood-bar" style="height: <?php echo ($mood['mood_value'] / 5) * 100; ?>%;" title="<?php echo date('M j', strtotime($mood['entry_date'])); ?>">
                    <div class="label"><?php echo date('M j', strtotime($mood['entry_date'])); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($game_stats)): ?>
    <div class="section">
        <h2>🎮 Game Performance</h2>
        <?php foreach ($game_stats as $stat): ?>
            <div class="game-item">
                <div>
                    <strong><?php echo $game_names[$stat['game_type']] ?? $stat['game_type']; ?></strong>
                    <div style="color: #666; font-size: 14px;">Played <?php echo $stat['games_played']; ?> times</div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 24px; font-weight: bold; color: #667eea;"><?php echo round($stat['avg_score']); ?></div>
                    <div style="font-size: 12px; color: #666;">Avg Score</div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if ($best_game): ?>
            <div style="background: #e3f2fd; padding: 20px; border-radius: 10px; margin-top: 20px; text-align: center;">
                <strong>🏆 Your Best Game:</strong> <?php echo $game_names[$best_game['game_type']]; ?><br>
                Average Score: <?php echo round($best_game['avg_score']); ?>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($recent_games)): ?>
    <div class="section">
        <h2>🎯 Recent Game Sessions</h2>
        <?php foreach ($recent_games as $game): ?>
            <div style="padding: 15px; background: #f8f9fa; border-radius: 8px; margin-bottom: 10px;">
                <strong><?php echo $game_names[$game['game_type']] ?? $game['game_type']; ?></strong>
                <span style="float: right; font-weight: bold; color: #667eea;">Score: <?php echo round($game['score']); ?></span>
                <div style="color: #666; font-size: 14px; margin-top: 5px;">
                    <?php echo date('M j, Y', strtotime($game['started_at'])); ?> | 
                    Difficulty: <?php echo ucfirst($game['difficulty']); ?> | 
                    Duration: <?php echo round($game['duration_seconds']); ?>s
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="section">
        <h2>💡 Insights & Recommendations</h2>
        <ul style="line-height: 2; padding-left: 25px;">
            <?php if ($mood_stats && $mood_stats['avg_mood'] >= 4): ?>
                <li>✅ Great job maintaining a positive mood!</li>
            <?php elseif ($mood_stats && $mood_stats['avg_mood'] < 3): ?>
                <li>💙 Consider activities that boost your mood or talk to someone.</li>
            <?php endif; ?>
            
            <?php if (count($game_stats) >= 3): ?>
                <li>🎮 You're actively training your cognitive skills - keep it up!</li>
            <?php else: ?>
                <li>🎯 Try playing more games to improve cognitive function.</li>
            <?php endif; ?>
            
            <?php if (count($questionnaire_results) === 0): ?>
                <li>📋 Take a wellness questionnaire to track your mental health.</li>
            <?php endif; ?>
            
            <li>🌟 <a href="report.php">View your weekly report</a> for detailed insights.</li>
        </ul>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../_footer.php'; ?>