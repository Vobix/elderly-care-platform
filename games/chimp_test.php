<?php
/**
 * Chimp Test Game
 * Click numbers in ascending order after they hide
 * Based on the famous chimpanzee working memory study
 */

require_once __DIR__ . '/../pages/account/auth.php';
require_once __DIR__ . '/../_header.php';

// Single configuration - no difficulty levels
$settings = ['starting_numbers' => 4, 'grid_size' => 5, 'hide_delay' => 1500];
?>

<link rel="stylesheet" href="/assets/css/game-common.css">

<div class="game-container">
    <div class="game-header">
        <h1>🐵 Chimp Test</h1>
        <p>Click the numbers in ascending order</p>
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
            <div class="label">Strikes</div>
            <div class="value" id="strikes">0/3</div>
        </div>
    </div>
    
    <div class="instructions">
        <h3>How to Play:</h3>
        <ul>
            <li>Numbers will appear on the grid</li>
            <li>After clicking the first number, the rest hide</li>
            <li>Click all numbers in ascending order (1, 2, 3...)</li>
            <li>Each level adds one more number</li>
            <li>Three strikes and you're out!</li>
        </ul>
    </div>
    
    <div id="game-area" style="margin: 30px auto; max-width: 700px;">
        <div id="chimp-grid" style="display: grid; gap: 15px; aspect-ratio: 1; background: #333; padding: 20px; border-radius: 10px;"></div>
    </div>
    
    <div class="message" id="message"></div>
    
    <div class="game-buttons">
        <button id="start-btn" class="btn btn-success" onclick="startGame()">🎮 Start Game</button>
        <button id="next-btn" class="btn btn-primary" style="display: none;" onclick="nextLevel()">➡️ Next Level</button>
    </div>
</div>

<form id="result-form" method="POST" action="/pages/game_result.php" style="display: none;">
    <input type="hidden" name="game_type" value="chimp_test">
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
<script src="/assets/js/chimp_test.js"></script>

<?php require_once __DIR__ . '/../_footer.php'; ?>
