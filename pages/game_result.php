<?php
/**
 * Game Result Page
 * Saves game results and displays summary
 */

$page_title = "Game Results";
require_once __DIR__ . '/account/auth.php';
require_once __DIR__ . '/../_header.php';
require_once __DIR__ . '/../database/functions.php';

$user_id = $_SESSION['user_id'];

// Get game data from POST
$game_type = $_POST['game_type'] ?? $_GET['game'] ?? '';
$score = $_POST['score'] ?? 0;
$duration = $_POST['duration'] ?? 0;
$difficulty = $_POST['difficulty'] ?? 'medium';
$attempts = $_POST['attempts'] ?? 0;
$accuracy = $_POST['accuracy'] ?? 0;

// Validate and save to database
if (!empty($game_type) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Prepare additional details as JSON
        $details = json_encode([
            'attempts' => $attempts,
            'accuracy' => $accuracy,
            'score' => $score
        ]);
        
        $session_id = insertGameSession($user_id, $game_type, $score, $duration, $difficulty, $details);
        $saved = true;
    } catch (Exception $e) {
        error_log("Game save error: " . $e->getMessage());
        $saved = false;
    }
} else {
    $saved = false;
}

// Get user's stats for this game
$game_stats = getUserGameStats($user_id, $game_type);
$stats = $game_stats[0] ?? null;

// Game titles
$game_titles = [
    'memory' => 'Memory Match',
    'attention' => 'Attention Focus',
    'reaction' => 'Reaction Time',
    'puzzle' => 'Puzzle Solver'
];

$game_name = $game_titles[$game_type] ?? 'Game';

// Performance messages
$performance_message = '';
if ($score >= 90) {
    $performance_message = "🌟 Outstanding! You're amazing!";
} elseif ($score >= 75) {
    $performance_message = "🎉 Great job! Well done!";
} elseif ($score >= 60) {
    $performance_message = "👍 Good effort! Keep practicing!";
} else {
    $performance_message = "💪 Keep trying! You'll improve!";
}

require_once __DIR__ . '/../_header.php';
?>

<link rel="stylesheet" href="/assets/css/game-result.css">

<div class="result-container">
    <?php if ($saved): ?>
        <div class="alert alert-success">
            ✅ Your game results have been saved!
        </div>
    <?php endif; ?>
    
    <div class="result-header">
        <h1><?php echo htmlspecialchars($game_name); ?></h1>
        <p style="font-size: 18px;">Difficulty: <?php echo ucfirst($difficulty); ?></p>
        <div class="score-display"><?php echo round($score); ?></div>
        <div class="performance"><?php echo $performance_message; ?></div>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="icon">⏱️</div>
            <div class="label">Duration</div>
            <div class="value"><?php echo round($duration); ?>s</div>
        </div>
        
        <?php if ($attempts > 0): ?>
        <div class="stat-card">
            <div class="icon">🎯</div>
            <div class="label">Attempts</div>
            <div class="value"><?php echo $attempts; ?></div>
        </div>
        <?php endif; ?>
        
        <?php if ($accuracy > 0): ?>
        <div class="stat-card">
            <div class="icon">📊</div>
            <div class="label">Accuracy</div>
            <div class="value"><?php echo round($accuracy); ?>%</div>
        </div>
        <?php endif; ?>
        
        <div class="stat-card">
            <div class="icon">🏆</div>
            <div class="label">Difficulty</div>
            <div class="value"><?php echo ucfirst($difficulty); ?></div>
        </div>
    </div>
    
    <?php if ($stats): ?>
    <div class="comparison">
        <h3>📈 Your Progress</h3>
        <p style="font-size: 16px; line-height: 1.8;">
            <strong>Games Played:</strong> <?php echo $stats['games_played']; ?><br>
            <strong>Average Score:</strong> <?php echo round($stats['avg_score']); ?><br>
            <strong>Best Score:</strong> <?php echo round($stats['best_score']); ?><br>
            <strong>Total Time Played:</strong> <?php echo round($stats['total_time'] / 60); ?> minutes
        </p>
        
        <?php if ($score > $stats['best_score']): ?>
            <div style="margin-top: 15px; padding: 15px; background: #d4edda; color: #155724; border-radius: 8px;">
                🎊 <strong>New Personal Best!</strong> You beat your previous record!
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <div class="action-buttons">
        <a href="game_play.php?game=<?php echo $game_type; ?>&difficulty=<?php echo $difficulty; ?>" class="btn btn-primary">
            🔄 Play Again
        </a>
        <a href="games.php" class="btn btn-secondary">
            🎮 All Games
        </a>
        <a href="/pages/insights/dashboard.php" class="btn btn-success">
            📊 Dashboard
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../_footer.php'; ?>
