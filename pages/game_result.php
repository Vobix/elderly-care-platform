<?php
/**
 * Game Result Page
 * Saves game results and displays summary
 * 
 * Messages:
 * M3: Game complete! Your score has been saved.
 * M4: Your game statistics have been updated.
 * 
 * Constraints:
 * C1: Auto Save Rule - Game score must be automatically saved immediately after game ends
 * C3: Stats Update Formula:
 *     - Times Played = Times Played + 1
 *     - If Score > Best Score -> Best Score = Score
 *     - Average Score = Total Score / Times Played
 */

$page_title = "Game Results";
require_once __DIR__ . '/account/auth.php';
require_once __DIR__ . '/../_header.php';
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../database/dao/GameDAO.php';
require_once __DIR__ . '/../database/dao/LeaderboardDAO.php';
require_once __DIR__ . '/../services/GameService.php';

$user_id = $_SESSION['user_id'];

// Phase 3: Initialize DAOs and pass to Service
$gameDAO = new GameDAO($pdo);
$leaderboardDAO = new LeaderboardDAO($pdo);
$gameService = new GameService($gameDAO, $leaderboardDAO);

// Get game data from POST
$game_type = $_POST['game_type'] ?? $_GET['game'] ?? '';
$score = $_POST['score'] ?? 0;
$duration = $_POST['duration'] ?? 0;
$difficulty = $_POST['difficulty'] ?? 'medium';
$attempts = $_POST['attempts'] ?? 0;
$accuracy = $_POST['accuracy'] ?? 0;

// Save game result using GameService
// Enforces C1 (Auto Save), C3 (Stats Update Formula)
// Returns M3, M4 messages
$result = null;
$ranking = null;
$leaderboard = [];

if (!empty($game_type) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Prepare additional details
    $details = [
        'max_score' => $_POST['max_score'] ?? null,
        'accuracy' => $accuracy,
        'level_reached' => $_POST['level_reached'] ?? null,
        'avg_reaction_ms' => $_POST['avg_reaction_ms'] ?? null
    ];
    
    // Use GameService - Enforces ALL constraints (C1, C3)
    $result = $gameService->completeGame($user_id, $game_type, $difficulty, $score, $details);
    $saved = $result['success'];
    
    // Get leaderboard and ranking after save
    if ($saved) {
        $ranking = $gameService->getUserRanking($user_id, $game_type);
        $leaderboard = $gameService->getLeaderboard($game_type, 10);
    }
} else {
    $saved = false;
}

// Get user's stats for this game
$stats_data = $gameService->getStats($user_id, $game_type);
$stats = $stats_data;

// Ensure stats has default values to prevent undefined key warnings
if ($stats) {
    $stats['times_played'] = $stats['times_played'] ?? 0;
    $stats['average_score'] = $stats['average_score'] ?? 0;
    $stats['best_score'] = $stats['best_score'] ?? 0;
}

// Game titles
$game_titles = [
    'memory' => 'Memory Match',
    'attention' => 'Attention Focus',
    'reaction' => 'Reaction Time',
    'puzzle' => 'Puzzle Solver',
    'card_flip' => 'Card Flip Memory',
    'chimp_test' => 'Chimp Test',
    'number_memory' => 'Number Memory',
    'tetris' => 'Tetris',
    'gem_match' => 'Gem Match'
];

$game_name = $game_titles[$game_type] ?? 'Game';

// Define which games don't use certain features
$no_difficulty_games = ['card_flip', 'number_memory', 'chimp_test', 'tetris', 'gem_match', 'reaction'];
$no_duration_games = ['tetris', 'gem_match']; // Tetris is endless, gem_match is fixed 60s
$has_difficulty = !in_array($game_type, $no_difficulty_games);
$has_duration = !in_array($game_type, $no_duration_games);

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
    <?php if ($saved && $result): ?>
        <div class="alert alert-success">
            ✅ <?php echo $result['message']; // M3 + M4 from GameService ?>
        </div>
    <?php elseif ($result && !$result['success']): ?>
        <div class="alert alert-error">
            ❌ <?php echo $result['message']; // M5 from GameService ?>
        </div>
    <?php endif; ?>
    
    <div class="result-header">
        <h1><?php echo htmlspecialchars($game_name); ?></h1>
        <?php if ($has_difficulty): ?>
        <p style="font-size: 18px;">Difficulty: <?php echo ucfirst($difficulty); ?></p>
        <?php endif; ?>
        <div class="score-display"><?php echo round($score); ?></div>
        <div class="performance"><?php echo $performance_message; ?></div>
    </div>
    
    <div class="stats-grid">
        <?php if ($has_duration && $duration > 0): ?>
        <div class="stat-card">
            <div class="icon">⏱️</div>
            <div class="label">Duration</div>
            <div class="value"><?php echo round($duration); ?>s</div>
        </div>
        <?php endif; ?>
        
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
        
        <?php if ($has_difficulty): ?>
        <div class="stat-card">
            <div class="icon">🏆</div>
            <div class="label">Difficulty</div>
            <div class="value"><?php echo ucfirst($difficulty); ?></div>
        </div>
        <?php endif; ?>
    </div>
    
    <?php if ($stats): ?>
    <div class="comparison">
        <h3>📈 Your Progress</h3>
        <p style="font-size: 16px; line-height: 1.8;">
            <strong>Games Played:</strong> <?php echo $stats['times_played']; // C3: Times Played ?><br>
            <strong>Average Score:</strong> <?php echo round($stats['average_score']); // C3: Average Score ?><br>
            <strong>Best Score:</strong> <?php echo round($stats['best_score']); // C3: Best Score ?><br>
        </p>
        
        <?php if ($score > $stats['best_score']): ?>
            <div style="margin-top: 15px; padding: 15px; background: #d4edda; color: #155724; border-radius: 8px;">
                🎊 <strong>New Personal Best!</strong> You beat your previous record!
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <?php if ($ranking): ?>
    <div class="ranking-section" style="margin-top: 30px; padding: 25px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 12px;">
        <h3 style="margin: 0 0 15px 0; font-size: 24px; text-align: center;">🏅 Your Global Ranking</h3>
        
        <!-- Bell Curve Visualization -->
        <div style="background: rgba(255,255,255,0.15); padding: 20px; border-radius: 10px; margin-bottom: 20px;">
            <svg width="100%" height="150" viewBox="0 0 400 150" style="display: block;">
                <!-- Bell curve path -->
                <path d="M 0,140 Q 50,120 100,80 Q 150,30 200,20 Q 250,30 300,80 Q 350,120 400,140" 
                      fill="none" stroke="rgba(255,255,255,0.6)" stroke-width="2"/>
                <path d="M 0,140 Q 50,120 100,80 Q 150,30 200,20 Q 250,30 300,80 Q 350,120 400,140 L 400,150 L 0,150 Z" 
                      fill="url(#gradient)" opacity="0.3"/>
                
                <!-- Gradient definition -->
                <defs>
                    <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" style="stop-color:#ff6b6b;stop-opacity:1" />
                        <stop offset="25%" style="stop-color:#ffd93d;stop-opacity:1" />
                        <stop offset="50%" style="stop-color:#6bcf7f;stop-opacity:1" />
                        <stop offset="75%" style="stop-color:#4d96ff;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#9b59b6;stop-opacity:1" />
                    </linearGradient>
                </defs>
                
                <!-- Your position marker -->
                <?php 
                $markerX = 400 - ($ranking['percentile'] / 100 * 400); // Reverse because higher percentile = better
                $markerY = 20 + (abs(50 - $ranking['percentile']) / 50 * 120); // Height on bell curve
                ?>
                <circle cx="<?php echo $markerX; ?>" cy="<?php echo $markerY; ?>" r="8" fill="#FFD700" stroke="#FFF" stroke-width="3">
                    <animate attributeName="r" values="8;12;8" dur="1.5s" repeatCount="indefinite"/>
                </circle>
                <text x="<?php echo $markerX; ?>" y="<?php echo $markerY - 15; ?>" fill="#FFD700" font-size="14" font-weight="bold" text-anchor="middle">YOU</text>
                
                <!-- Percentile markers -->
                <text x="40" y="145" fill="rgba(255,255,255,0.7)" font-size="10" text-anchor="middle">0%</text>
                <text x="120" y="145" fill="rgba(255,255,255,0.7)" font-size="10" text-anchor="middle">25%</text>
                <text x="200" y="145" fill="rgba(255,255,255,0.7)" font-size="10" text-anchor="middle">50%</text>
                <text x="280" y="145" fill="rgba(255,255,255,0.7)" font-size="10" text-anchor="middle">75%</text>
                <text x="360" y="145" fill="rgba(255,255,255,0.7)" font-size="10" text-anchor="middle">100%</text>
            </svg>
        </div>
        
        <div style="text-align: center;">
            <div style="font-size: 48px; font-weight: bold; margin: 10px 0;">
                #<?php echo number_format($ranking['rank']); ?>
            </div>
            <div style="font-size: 20px; margin: 10px 0;">
                out of <?php echo number_format($ranking['total_players']); ?> players
            </div>
            <div style="font-size: 32px; font-weight: bold; margin: 15px 0; padding: 15px; background: rgba(255,255,255,0.2); border-radius: 10px;">
                Top <?php echo number_format($ranking['percentile'], 1); ?>%
            </div>
            <div style="font-size: 18px; margin-top: 10px;">
                <?php echo $ranking['message']; ?>
            </div>
            <div style="margin-top: 15px; font-size: 14px; opacity: 0.9;">
                You're better than <?php echo number_format($ranking['better_than']); ?> player<?php echo $ranking['better_than'] != 1 ? 's' : ''; ?>!
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($leaderboard)): ?>
    <div class="leaderboard-section" style="margin-top: 30px;">
        <h3 style="text-align: center; margin-bottom: 20px;">🏆 Top 10 Leaderboard</h3>
        <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th style="padding: 15px; text-align: left;">Rank</th>
                        <th style="padding: 15px; text-align: left;">Player</th>
                        <th style="padding: 15px; text-align: right;">Score</th>
                        <th style="padding: 15px; text-align: center;">Games</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaderboard as $index => $entry): ?>
                    <tr style="border-top: 1px solid #e9ecef; <?php echo $entry['user_id'] == $user_id ? 'background: #fff3cd;' : ''; ?>">
                        <td style="padding: 15px;">
                            <?php 
                            if ($entry['rank'] == 1) echo '🥇';
                            elseif ($entry['rank'] == 2) echo '🥈';
                            elseif ($entry['rank'] == 3) echo '🥉';
                            else echo '#' . $entry['rank'];
                            ?>
                        </td>
                        <td style="padding: 15px; font-weight: <?php echo $entry['user_id'] == $user_id ? 'bold' : 'normal'; ?>;">
                            <?php echo htmlspecialchars($entry['username']); ?>
                            <?php if ($entry['user_id'] == $user_id): ?>
                                <span style="color: #667eea;">(You)</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 15px; text-align: right; font-weight: bold; color: #667eea;">
                            <?php echo number_format($entry['score']); ?>
                        </td>
                        <td style="padding: 15px; text-align: center; color: #666;">
                            <?php echo $entry['times_played']; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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
