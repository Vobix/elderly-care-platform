<?php
/**
 * Visual Memory Game
 * Remember the positions of highlighted squares
 */

require_once __DIR__ . '/../pages/account/auth.php';
require_once __DIR__ . '/../_header.php';

// Single configuration - no difficulty levels
$settings = ['grid_size' => 4, 'initial_squares' => 3, 'rounds' => 20];
?>

<link rel="stylesheet" href="/assets/css/game-common.css">

<div class="game-container">
    <div class="game-header">
        <h1>👁️‍🗨️ Visual Memory</h1>
        <p>Remember the positions of the highlighted squares</p>
    </div>
    
    <div class="game-info">
        <div class="info-box">
            <div class="label">Level</div>
            <div class="value" id="level">1</div>
        </div>
        <div class="info-box">
            <div class="label">Score</div>
            <div class="value" id="score">0</div>
        </div>
        <div class="info-box">
            <div class="label">Lives</div>
            <div class="value" id="lives">3</div>
        </div>
    </div>
    
    <div class="instructions">
        <h3>How to Play:</h3>
        <ul>
            <li>Watch as squares light up on the grid</li>
            <li>Click all the squares that were highlighted</li>
            <li>Each level adds more squares to remember</li>
            <li>You have 3 lives - game ends when you run out</li>
        </ul>
    </div>
    
    <div id="game-area" style="margin: 30px auto; max-width: 500px;">
        <div id="grid" style="display: grid; gap: 5px; aspect-ratio: 1; background: #333; padding: 5px; border-radius: 10px;"></div>
    </div>
    
    <div class="message" id="message"></div>
    
    <div class="game-buttons">
        <button id="start-btn" class="btn btn-success" onclick="startGame()">🎮 Start Game</button>
        <button id="next-btn" class="btn btn-primary" style="display: none;" onclick="nextLevel()">➡️ Next Level</button>
    </div>
</div>

<form id="result-form" method="POST" action="/pages/game_result.php" style="display: none;">
    <input type="hidden" name="game_type" value="visual_memory">
    <input type="hidden" name="difficulty" value="medium">
    <input type="hidden" name="score" id="final-score">
    <input type="hidden" name="duration" id="final-duration">
    <input type="hidden" name="attempts" id="final-attempts">
    <input type="hidden" name="accuracy" id="final-accuracy">
</form>

<script>
// Game configuration from PHP
const CONFIG = <?php echo json_encode($settings); ?>;
</script>
<script src="/assets/js/visual_memory.js"></script>

<?php require_once __DIR__ . '/../_footer.php'; ?>
