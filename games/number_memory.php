<?php
/**
 * Number Memory Game
 * Remember progressively longer number sequences
 */

require_once __DIR__ . '/../pages/account/auth.php';
require_once __DIR__ . '/../_header.php';

// Single configuration - no difficulty levels
$settings = ['starting_digits' => 4, 'display_time' => 1500, 'per_digit_time' => 400];
?>

<link rel="stylesheet" href="/assets/css/game-common.css">

<div class="game-container">
    <div class="game-header">
        <h1>🔢 Number Memory</h1>
        <p>Remember and type the number sequence</p>
    </div>
    
    <div class="game-info">
        <div class="info-box">
            <div class="label">Level</div>
            <div class="value" id="level">1</div>
        </div>
        <div class="info-box">
            <div class="label">Digits</div>
            <div class="value" id="digits">0</div>
        </div>
        <div class="info-box">
            <div class="label">Best Score</div>
            <div class="value" id="best">0</div>
        </div>
    </div>
    
    <div class="instructions">
        <h3>How to Play:</h3>
        <ul>
            <li>A number will appear on screen</li>
            <li>Memorize it before it disappears</li>
            <li>Type the number you saw</li>
            <li>Each level adds one more digit</li>
            <li>Game ends after first mistake</li>
        </ul>
    </div>
    
    <div id="game-area" style="margin: 30px auto; text-align: center;">
        <div id="number-display" style="font-size: 48px; font-weight: bold; min-height: 80px; display: flex; align-items: center; justify-content: center; color: #4CAF50; letter-spacing: 8px;"></div>
        
        <div id="input-area" style="display: none; margin-top: 30px;">
            <input type="text" id="number-input" placeholder="Enter the number" 
                   style="font-size: 32px; padding: 15px; width: 100%; max-width: 500px; text-align: center; border: 2px solid #4CAF50; border-radius: 8px; background: #2a2a2a; color: #fff; letter-spacing: 4px;" 
                   autocomplete="off">
            <button id="submit-btn" class="btn btn-success" onclick="submitAnswer()" style="margin-top: 20px; font-size: 18px;">✓ Submit</button>
        </div>
    </div>
    
    <div class="message" id="message"></div>
    
    <div class="game-buttons">
        <button id="start-btn" class="btn btn-success" onclick="startGame()">🎮 Start Game</button>
        <button id="next-btn" class="btn btn-primary" style="display: none;" onclick="nextLevel()">➡️ Next Number</button>
    </div>
</div>

<form id="result-form" method="POST" action="/pages/game_result.php" style="display: none;">
    <input type="hidden" name="game_type" value="number_memory">
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
<script src="/assets/js/number_memory.js"></script>

<?php require_once __DIR__ . '/../_footer.php'; ?>
