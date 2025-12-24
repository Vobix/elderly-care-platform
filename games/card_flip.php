<?php
/**
 * Card Flip Pattern Matching Game
 * Match pairs of cards to complete the game
 */

require_once __DIR__ . '/../pages/account/auth.php';
require_once __DIR__ . '/../_header.php';

// Single configuration - difficulty increases with fewer moves allowed
$settings = ['grid_size' => 4, 'max_moves' => 30, 'show_time' => 2000];
?>

<link rel="stylesheet" href="/assets/css/game-common.css">
<style>
.card-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
    max-width: 500px;
    margin: 30px auto;
    perspective: 1000px;
}

.card {
    aspect-ratio: 1;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    cursor: pointer;
    position: relative;
    transform-style: preserve-3d;
    transition: transform 0.6s;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.card:hover:not(.matched):not(.flipped) {
    transform: scale(1.05);
}

.card.flipped {
    transform: rotateY(180deg);
}

.card.matched {
    opacity: 0.6;
    cursor: default;
    animation: matchPulse 0.5s;
}

@keyframes matchPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.card-front, .card-back {
    position: absolute;
    width: 100%;
    height: 100%;
    backface-visibility: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-size: 48px;
}

.card-front {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.card-back {
    background: white;
    transform: rotateY(180deg);
    border: 3px solid #667eea;
}

.moves-counter {
    text-align: center;
    font-size: 20px;
    margin: 20px 0;
    color: #2d3748;
}

.moves-remaining {
    font-weight: bold;
    color: #667eea;
}

.game-over-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.game-over-content {
    background: white;
    padding: 40px;
    border-radius: 20px;
    text-align: center;
    max-width: 400px;
}

.difficulty-selector {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin: 20px 0;
}

.difficulty-btn {
    padding: 12px 30px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.difficulty-btn.easy {
    background: #4CAF50;
    color: white;
}

.difficulty-btn.medium {
    background: #FF9800;
    color: white;
}

.difficulty-btn.hard {
    background: #f44336;
    color: white;
}

.difficulty-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}
</style>

<div class="game-container">
    <div class="game-header">
        <h1>🎴 Card Flip Memory</h1>
        <p>Match all pairs of cards to win!</p>
    </div>
    
    <div class="game-info">
        <div class="info-box">
            <div class="label">Level</div>
            <div class="value" id="level">1</div>
        </div>
        <div class="info-box">
            <div class="label">Pairs Found</div>
            <div class="value" id="pairs">0/8</div>
        </div>
        <div class="info-box">
            <div class="label">Moves</div>
            <div class="value" id="moves">0</div>
        </div>
        <div class="info-box">
            <div class="label">Best Score</div>
            <div class="value" id="best">0</div>
        </div>
    </div>
    
    <div class="difficulty-selector" id="difficulty-selector">
        <button class="difficulty-btn easy" onclick="startGame('easy')">Easy (4x4)</button>
        <button class="difficulty-btn medium" onclick="startGame('medium')">Medium (4x5)</button>
        <button class="difficulty-btn hard" onclick="startGame('hard')">Hard (4x6)</button>
    </div>
    
    <div class="moves-counter" id="moves-info" style="display:none;">
        Moves remaining: <span class="moves-remaining" id="moves-remaining">30</span>
    </div>
    
    <div id="message" class="message"></div>
    
    <div class="card-grid" id="card-grid" style="display:none;"></div>
    
    <button class="game-button" id="restart-btn" style="display:none;" onclick="restartGame()">
        🔄 Restart Level
    </button>
</div>

<div class="game-over-overlay" id="game-over-overlay">
    <div class="game-over-content">
        <h2 id="game-over-title">Level Complete!</h2>
        <p id="game-over-message"></p>
        <button class="game-button" onclick="nextLevel()">Next Level</button>
        <button class="game-button secondary" onclick="location.reload()">Back to Menu</button>
    </div>
</div>

<!-- Result Form -->
<form id="result-form" action="/pages/game_result.php" method="POST" style="display: none;">
    <input type="hidden" name="game_type" value="card_flip">
    <input type="hidden" name="difficulty" id="final-difficulty">
    <input type="hidden" name="score" id="final-score">
    <input type="hidden" name="duration" id="final-duration">
    <input type="hidden" name="attempts" id="final-attempts">
    <input type="hidden" name="accuracy" id="final-accuracy">
</form>

<script>
// Game configuration from PHP
const CONFIG = <?php echo json_encode($settings); ?>;
</script>
<script src="/assets/js/card_flip.js"></script>

<script src="/assets/js/game-voice-helper.js"></script>
<?php require_once __DIR__ . '/../_footer.php'; ?>
