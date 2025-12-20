<?php
/**
 * Gem Match Game
 * Match 3 or more gems to clear them before time runs out
 */

$page_title = "Gem Match";
require_once __DIR__ . '/../pages/account/auth.php';
require_once __DIR__ . '/../_header.php';

$user_id = $_SESSION['user_id'];
?>

<link rel="stylesheet" href="/assets/css/game-common.css">
<link rel="stylesheet" href="/assets/css/gem_match.css">

<div class="gem-container">
    <div class="game-header">
        <h1>💎 Gem Match</h1>
        <p>Match 3 or more gems before time runs out!</p>
    </div>

    <div class="timer-bar-container">
        <div class="timer-bar" id="timerBar">60s</div>
    </div>

    <div class="game-stats">
        <div class="stat-box">
            <span class="stat-label">Score</span>
            <span class="stat-value" id="scoreDisplay">0</span>
        </div>
        <div class="stat-box">
            <span class="stat-label">Matches</span>
            <span class="stat-value" id="matchesDisplay">0</span>
        </div>
        <div class="stat-box">
            <span class="stat-label">Combo</span>
            <span class="stat-value" id="comboDisplay">0</span>
        </div>
    </div>

    <div class="gem-board" id="gemBoard"></div>

    <div class="game-instructions">
        <h3>How to Play</h3>
        <ul>
            <li>💎 Click a gem, then click an adjacent gem to swap them</li>
            <li>✨ Match 3 or more gems in a row or column to clear them</li>
            <li>🎯 Longer matches = More points!</li>
            <li>🔥 Make consecutive matches for combo bonuses</li>
            <li>⏰ You have 60 seconds - match as many as you can!</li>
        </ul>
    </div>
</div>

<div id="gameOverScreen" class="game-over-screen">
    <div class="game-over-content">
        <h2 id="gameOverTitle">⏰ Time's Up!</h2>
        <p id="gameOverMessage" style="font-size: 24px; margin: 20px 0;">Final Score: <span id="finalScoreText"></span></p>
        <p style="font-size: 18px;">Total Matches: <span id="finalMatchesText"></span></p>
        <p style="font-size: 18px;">Best Combo: <span id="finalComboText"></span></p>
    </div>
</div>

<script src="/assets/js/game-sounds.js"></script>
<script src="/assets/js/gem_match.js"></script>

<?php require_once __DIR__ . '/../_footer.php'; ?>
