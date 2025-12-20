<?php
/**
 * Tetris Multiplayer Result Page
 * Displays results for both players without saving to leaderboard
 */

$page_title = "Tetris - Match Results";
require_once __DIR__ . '/../pages/account/auth.php';
require_once __DIR__ . '/../_header.php';

// Get player data from POST
$p1_score = $_POST['p1_score'] ?? 0;
$p1_lines = $_POST['p1_lines'] ?? 0;
$p1_level = $_POST['p1_level'] ?? 0;

$p2_score = $_POST['p2_score'] ?? 0;
$p2_lines = $_POST['p2_lines'] ?? 0;
$p2_level = $_POST['p2_level'] ?? 0;

$winner = $_POST['winner'] ?? 1;
$duration = $_POST['duration'] ?? 0;

$minutes = floor($duration / 60);
$seconds = $duration % 60;
?>

<link rel="stylesheet" href="/assets/css/game-common.css">
<style>
.result-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 40px 20px;
}

.winner-banner {
    text-align: center;
    padding: 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    margin-bottom: 30px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
}

.winner-banner h1 {
    margin: 0;
    font-size: 48px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.winner-banner .trophy {
    font-size: 72px;
    margin-bottom: 10px;
}

.players-comparison {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin: 30px 0;
}

.player-result {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    position: relative;
}

.player-result.winner {
    border: 3px solid #FFD700;
    box-shadow: 0 0 20px rgba(255, 215, 0, 0.4);
}

.player-result.loser {
    opacity: 0.7;
}

.player-result.p1 {
    border-top: 5px solid #2196F3;
}

.player-result.p2 {
    border-top: 5px solid #F44336;
}

.player-header {
    text-align: center;
    font-size: 28px;
    font-weight: bold;
    margin-bottom: 20px;
}

.player-header.p1 {
    color: #2196F3;
}

.player-header.p2 {
    color: #F44336;
}

.winner-badge {
    position: absolute;
    top: -15px;
    right: 20px;
    background: #FFD700;
    color: #333;
    padding: 5px 15px;
    border-radius: 20px;
    font-weight: bold;
    font-size: 14px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.stat-row {
    display: flex;
    justify-content: space-between;
    padding: 15px 0;
    border-bottom: 1px solid #eee;
}

.stat-row:last-child {
    border-bottom: none;
}

.stat-label {
    font-size: 16px;
    color: #666;
}

.stat-value {
    font-size: 24px;
    font-weight: bold;
    color: #333;
}

.game-info {
    background: white;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    margin: 20px 0;
}

.action-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 30px;
}

.btn {
    padding: 15px 30px;
    font-size: 18px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s;
}

.btn-primary {
    background: #4CAF50;
    color: white;
}

.btn-primary:hover {
    background: #45a049;
    transform: translateY(-2px);
}

.btn-secondary {
    background: #2196F3;
    color: white;
}

.btn-secondary:hover {
    background: #0b7dda;
    transform: translateY(-2px);
}
</style>

<div class="result-container">
    <div class="winner-banner">
        <div class="trophy">🏆</div>
        <h1>Player <?php echo $winner; ?> Wins!</h1>
        <p style="font-size: 20px; margin: 10px 0 0 0;">Survival Mode - Last One Standing</p>
    </div>

    <div class="game-info">
        <p style="font-size: 18px; margin: 5px 0;"><strong>Game Duration:</strong> <?php echo $minutes; ?>m <?php echo $seconds; ?>s</p>
        <p style="font-size: 16px; color: #666; margin: 5px 0;">Hot-Seat Multiplayer</p>
    </div>

    <div class="players-comparison">
        <!-- Player 1 -->
        <div class="player-result p1 <?php echo $winner == 1 ? 'winner' : 'loser'; ?>">
            <?php if ($winner == 1): ?>
                <div class="winner-badge">👑 WINNER</div>
            <?php endif; ?>
            
            <div class="player-header p1">👤 Player 1</div>
            
            <div class="stat-row">
                <span class="stat-label">Final Score</span>
                <span class="stat-value"><?php echo number_format($p1_score); ?></span>
            </div>
            
            <div class="stat-row">
                <span class="stat-label">Lines Cleared</span>
                <span class="stat-value"><?php echo $p1_lines; ?></span>
            </div>
            
            <div class="stat-row">
                <span class="stat-label">Level Reached</span>
                <span class="stat-value"><?php echo $p1_level; ?></span>
            </div>
            
            <?php if ($winner != 1): ?>
                <div style="text-align: center; margin-top: 20px; color: #f44336; font-size: 18px;">
                    💀 Defeated
                </div>
            <?php endif; ?>
        </div>

        <!-- Player 2 -->
        <div class="player-result p2 <?php echo $winner == 2 ? 'winner' : 'loser'; ?>">
            <?php if ($winner == 2): ?>
                <div class="winner-badge">👑 WINNER</div>
            <?php endif; ?>
            
            <div class="player-header p2">👤 Player 2</div>
            
            <div class="stat-row">
                <span class="stat-label">Final Score</span>
                <span class="stat-value"><?php echo number_format($p2_score); ?></span>
            </div>
            
            <div class="stat-row">
                <span class="stat-label">Lines Cleared</span>
                <span class="stat-value"><?php echo $p2_lines; ?></span>
            </div>
            
            <div class="stat-row">
                <span class="stat-label">Level Reached</span>
                <span class="stat-value"><?php echo $p2_level; ?></span>
            </div>
            
            <?php if ($winner != 2): ?>
                <div style="text-align: center; margin-top: 20px; color: #f44336; font-size: 18px;">
                    💀 Defeated
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="action-buttons">
        <a href="/games/tetris.php" class="btn btn-primary">🔄 Play Again</a>
        <a href="/pages/games.php" class="btn btn-secondary">🎮 All Games</a>
    </div>
</div>

<?php require_once __DIR__ . '/../_footer.php'; ?>
