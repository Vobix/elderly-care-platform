<?php
/**
 * Puzzle Solver Game
 * Complete visual patterns and sequences
 */

require_once __DIR__ . '/../_header.php';

$config = [
    'easy' => ['rounds' => 5, 'options' => 3],
    'medium' => ['rounds' => 7, 'options' => 4],
    'hard' => ['rounds' => 10, 'options' => 5]
];

$settings = $config[$difficulty];
?>

<link rel="stylesheet" href="/assets/css/game-common.css">

<style>
    .game-container { max-width: 900px; margin: 0 auto; text-align: center; padding: 20px; }
    .puzzle-area {
        background: white;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        margin: 30px 0;
    }
    .high-contrast .puzzle-area { background: #1a1a1a; border: 3px solid #ffff00; }
    .pattern-sequence {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin: 30px 0;
        flex-wrap: wrap;
    }
    .pattern-box {
        width: 100px;
        height: 100px;
        background: #f5f5f5;
        border: 3px solid #ddd;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
    }
    .pattern-box.missing {
        background: #fff3cd;
        border-color: #ffc107;
        border-style: dashed;
        font-size: 36px;
        color: #666;
    }
    .high-contrast .pattern-box { background: #000; border-color: #ffff00; color: #fff; }
    .options-area {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin: 30px 0;
        flex-wrap: wrap;
    }
    .option-box {
        width: 100px;
        height: 100px;
        background: white;
        border: 3px solid #9C27B0;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        cursor: pointer;
        transition: all 0.3s;
    }
    .option-box:hover { transform: scale(1.1); border-color: #7B1FA2; box-shadow: 0 4px 15px rgba(156,39,176,0.3); }
    .tap-only .option-box:hover { transform: none; }
    .high-contrast .option-box { background: #000; border-color: #ff00ff; }
    .game-info { display: flex; justify-content: space-around; margin: 20px 0; flex-wrap: wrap; gap: 15px; }
    .info-box { background: white; padding: 15px 25px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .high-contrast .info-box { background: #1a1a1a; border: 2px solid #ff00ff; }
    .info-box .value { font-size: 28px; font-weight: bold; color: #9C27B0; }
    .high-contrast .info-box .value { color: #ff00ff; }
    .message { font-size: 24px; font-weight: bold; margin: 20px 0; min-height: 30px; }
</style>

<div class="game-container">
    <h1 style="color: #9C27B0;">🧩 Puzzle Solver</h1>
    <p>Complete the pattern by choosing the right piece!</p>
    
    <div class="game-info">
        <div class="info-box">
            <div class="label">Round</div>
            <div class="value" id="round">0</div>
        </div>
        <div class="info-box">
            <div class="label">Score</div>
            <div class="value" id="score">0</div>
        </div>
        <div class="info-box">
            <div class="label">Correct</div>
            <div class="value" id="correct">0</div>
        </div>
    </div>
    
    <div class="puzzle-area">
        <h3>Find the missing piece:</h3>
        <div class="pattern-sequence" id="pattern-sequence"></div>
        
        <div class="message" id="message"></div>
        
        <h3>Choose an option:</h3>
        <div class="options-area" id="options-area"></div>
    </div>
    
    <div style="margin-top: 20px;">
        <button class="btn btn-success" id="start-btn" onclick="startGame()">▶️ Start Game</button>
        <button class="btn btn-primary" id="next-btn" onclick="nextRound()" style="display: none;">→ Next Puzzle</button>
        <a href="games.php" class="btn btn-secondary">← Back</a>
    </div>
</div>

<form id="result-form" method="POST" action="game_result.php" style="display: none;">
    <input type="hidden" name="game_type" value="puzzle">
    <input type="hidden" name="difficulty" value="<?php echo $difficulty; ?>">
    <input type="hidden" name="score" id="final-score">
    <input type="hidden" name="duration" id="final-duration">
    <input type="hidden" name="accuracy" id="final-accuracy">
</form>

<script>
// Game configuration from PHP
const CONFIG = <?php echo json_encode($settings); ?>;
</script>
<script src="/assets/js/puzzle.js"></script>
<script>
/* Game logic moved to external JS file
const PATTERNS = [
    ['🔴', '🔵', '🟢', '🟡', '🟣'],
    ['⭐', '🌙', '☀️', '🌟', '✨'],
    ['🍎', '🍊', '🍋', '🍉', '🍇'],
    ['🐶', '🐱', '🐭', '🐹', '🐰'],
    ['❤️', '💚', '💙', '💛', '💜'],
    ['🎵', '🎶', '🎸', '🎹', '🎺']
];

let currentRound = 0;
let score = 0;
let correct = 0;
let currentAnswer = '';
let gameStartTime;
let totalAttempts = 0;

function startGame() {
    currentRound = 0;
    score = 0;
    correct = 0;
    totalAttempts = 0;
    gameStartTime = Date.now();
    document.getElementById('start-btn').style.display = 'none';
    nextRound();
}

function nextRound() {
    if (currentRound >= CONFIG.rounds) {
        endGame();
        return;
    }
    
    currentRound++;
    totalAttempts++;
    updateDisplay();
    
    document.getElementById('next-btn').style.display = 'none';
    document.getElementById('message').textContent = '';
    
    const patternSet = PATTERNS[Math.floor(Math.random() * PATTERNS.length)];
    const patternLength = 3 + Math.floor(Math.random() * 2);
    const pattern = [];
    
    for (let i = 0; i < patternLength; i++) {
        pattern.push(patternSet[i % patternSet.length]);
    }
    
    const missingIndex = Math.floor(Math.random() * pattern.length);
    currentAnswer = pattern[missingIndex];
    pattern[missingIndex] = '?';
    
    displayPattern(pattern);
    displayOptions(patternSet, currentAnswer);
}

function displayPattern(pattern) {
    const container = document.getElementById('pattern-sequence');
    container.innerHTML = '';
    
    pattern.forEach(item => {
        const box = document.createElement('div');
        box.className = item === '?' ? 'pattern-box missing' : 'pattern-box';
        box.textContent = item;
        container.appendChild(box);
    });
}

function displayOptions(patternSet, correctAnswer) {
    const container = document.getElementById('options-area');
    container.innerHTML = '';
    
    const options = [correctAnswer];
    
    while (options.length < CONFIG.options) {
        const option = patternSet[Math.floor(Math.random() * patternSet.length)];
        if (!options.includes(option)) {
            options.push(option);
        }
    }
    
    options.sort(() => Math.random() - 0.5);
    
    options.forEach(option => {
        const box = document.createElement('div');
        box.className = 'option-box';
        box.textContent = option;
        box.onclick = () => checkAnswer(option, box);
        container.appendChild(box);
    });
}

function checkAnswer(selected, element) {
    document.querySelectorAll('.option-box').forEach(box => box.onclick = null);
    
    const message = document.getElementById('message');
    
    if (selected === currentAnswer) {
        element.style.background = '#d4edda';
        element.style.borderColor = '#28a745';
        message.textContent = '✅ Correct!';
        message.style.color = '#28a745';
        correct++;
        score += Math.round(100 / CONFIG.rounds);
    } else {
        element.style.background = '#f8d7da';
        element.style.borderColor = '#dc3545';
        message.textContent = '❌ Wrong. The answer was ' + currentAnswer;
        message.style.color = '#dc3545';
    }
    
    updateDisplay();
    document.getElementById('next-btn').style.display = 'inline-block';
}

function updateDisplay() {
    document.getElementById('round').textContent = currentRound;
    document.getElementById('score').textContent = score;
    document.getElementById('correct').textContent = correct;
}

function endGame() {
    const duration = (Date.now() - gameStartTime) / 1000;
    const accuracy = (correct / totalAttempts) * 100;
    
    // All game logic moved to external JS file
*/
</script>

<?php require_once __DIR__ . '/../_footer.php'; ?>
