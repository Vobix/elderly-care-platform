<?php
/**
 * Memory Match Game
 * Remember and match sequences of numbers/patterns
 */

require_once __DIR__ . '/../_header.php';

// Game configuration based on difficulty
$config = [
    'easy' => ['sequence_length' => 4, 'time_to_memorize' => 5, 'rounds' => 5],
    'medium' => ['sequence_length' => 6, 'time_to_memorize' => 4, 'rounds' => 7],
    'hard' => ['sequence_length' => 8, 'time_to_memorize' => 3, 'rounds' => 10]
];

$settings = $config[$difficulty];
?>

<link rel="stylesheet" href="/assets/css/game-common.css">

<style>
    .game-container {
        max-width: 800px;
        margin: 0 auto;
        text-align: center;
        padding: 20px;
    }
    
    .game-header {
        margin-bottom: 30px;
    }
    
    .game-header h1 {
        font-size: 36px;
        color: #4CAF50;
    }
    
    .high-contrast .game-header h1 {
        color: #00ff00;
    }
    
    .game-info {
        display: flex;
        justify-content: space-around;
        margin: 20px 0;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .info-box {
        background: white;
        padding: 15px 25px;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        min-width: 120px;
    }
    
    .high-contrast .info-box {
        background: #1a1a1a;
        border: 2px solid #00ff00;
    }
    
    .info-box .label {
        font-size: 14px;
        color: #666;
    }
    
    .high-contrast .info-box .label {
        color: #ccc;
    }
    
    .info-box .value {
        font-size: 28px;
        font-weight: bold;
        color: #4CAF50;
    }
    
    .high-contrast .info-box .value {
        color: #00ff00;
    }
    
    .sequence-display {
        background: white;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        margin: 30px 0;
        min-height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .high-contrast .sequence-display {
        background: #1a1a1a;
        border: 3px solid #00ff00;
    }
    
    .sequence-numbers {
        display: flex;
        gap: 15px;
        font-size: 48px;
        font-weight: bold;
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .number-box {
        width: 80px;
        height: 80px;
        background: #4CAF50;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 42px;
        animation: popIn 0.3s;
    }
    
    @keyframes popIn {
        from { transform: scale(0); }
        to { transform: scale(1); }
    }
    
    .input-area {
        margin: 30px 0;
    }
    
    .input-boxes {
        display: flex;
        gap: 10px;
        justify-content: center;
        flex-wrap: wrap;
        margin: 20px 0;
    }
    
    .input-box {
        width: 60px;
        height: 60px;
        font-size: 28px;
        text-align: center;
        border: 3px solid #ddd;
        border-radius: 8px;
        font-weight: bold;
    }
    
    .input-box:focus {
        border-color: #4CAF50;
        outline: none;
    }
    
    .high-contrast .input-box {
        background: #000;
        color: #fff;
        border-color: #00ff00;
    }
    
    .game-buttons {
        margin-top: 30px;
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .game-buttons .btn {
        padding: 15px 30px;
        font-size: 18px;
    }
    
    .instructions {
        background: #e3f2fd;
        padding: 20px;
        border-radius: 10px;
        margin: 20px 0;
        text-align: left;
    }
    
    .high-contrast .instructions {
        background: #003366;
        border: 2px solid #00ffff;
    }
    
    .message {
        font-size: 24px;
        font-weight: bold;
        margin: 20px 0;
        min-height: 30px;
    }
    
    .correct {
        color: #28a745;
    }
    
    .incorrect {
        color: #dc3545;
    }
</style>

<div class="game-container">
    <div class="game-header">
        <h1>🧠 Memory Match</h1>
        <p style="font-size: 18px;">Remember the sequence and repeat it!</p>
    </div>
    
    <div class="instructions">
        <strong>📖 How to Play:</strong>
        <ol style="margin: 10px 0; padding-left: 25px; line-height: 1.8;">
            <li>Watch the sequence of numbers carefully</li>
            <li>The numbers will disappear after <?php echo $settings['time_to_memorize']; ?> seconds</li>
            <li>Enter the numbers in the correct order</li>
            <li>Complete <?php echo $settings['rounds']; ?> rounds to finish the game</li>
        </ol>
    </div>
    
    <div class="game-info">
        <div class="info-box">
            <div class="label">Round</div>
            <div class="value" id="current-round">0</div>
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
    
    <div class="sequence-display" id="sequence-display">
        <div id="sequence-content">
            <p style="font-size: 24px;">Click "Start Game" to begin!</p>
        </div>
    </div>
    
    <div class="message" id="message"></div>
    
    <div class="input-area" id="input-area" style="display: none;">
        <h3>Enter the sequence:</h3>
        <div class="input-boxes" id="input-boxes"></div>
    </div>
    
    <div class="game-buttons">
        <button class="btn btn-success" id="start-btn" onclick="startGame()">▶️ Start Game</button>
        <button class="btn btn-primary" id="submit-btn" onclick="checkAnswer()" style="display: none;">✓ Submit Answer</button>
        <button class="btn btn-secondary" id="next-btn" onclick="nextRound()" style="display: none;">→ Next Round</button>
        <a href="/pages/games.php" class="btn btn-secondary">← Back to Games</a>
    </div>
</div>

<form id="result-form" method="POST" action="game_result.php" style="display: none;">
    <input type="hidden" name="game_type" value="memory">
    <input type="hidden" name="difficulty" value="<?php echo $difficulty; ?>">
    <input type="hidden" name="score" id="final-score">
    <input type="hidden" name="duration" id="final-duration">
    <input type="hidden" name="attempts" id="final-attempts">
    <input type="hidden" name="accuracy" id="final-accuracy">
</form>

<script>
// Game configuration from PHP
const DIFFICULTY = '<?php echo $difficulty; ?>';
const CONFIG = <?php echo json_encode($settings); ?>;
const MAX_ROUNDS = CONFIG.rounds;
const SEQUENCE_LENGTH = CONFIG.sequence_length;
const MEMORIZE_TIME = CONFIG.time_to_memorize * 1000;
</script>
<script src="/assets/js/memory.js"></script>

<script>
// Removed inline JavaScript - now in /assets/js/memory.js
/* Original code moved to external file for better organization

let currentRound = 0;
let score = 0;
let correctAnswers = 0;
let totalAttempts = 0;
let currentSequence = [];
let startTime;

function startGame() {
    currentRound = 0;
    score = 0;
    correctAnswers = 0;
    totalAttempts = 0;
    startTime = Date.now();
    
    document.getElementById('start-btn').style.display = 'none';
    nextRound();
}

function nextRound() {
    if (currentRound >= MAX_ROUNDS) {
        endGame();
        return;
    }
    
    currentRound++;
    totalAttempts++;
    updateDisplay();
    
    document.getElementById('next-btn').style.display = 'none';
    document.getElementById('submit-btn').style.display = 'none';
    document.getElementById('input-area').style.display = 'none';
    document.getElementById('message').textContent = '';
    
    // Generate random sequence
    currentSequence = [];
    for (let i = 0; i < SEQUENCE_LENGTH; i++) {
        currentSequence.push(Math.floor(Math.random() * 9) + 1);
    }
    
    // Show sequence
    showSequence();
}

function showSequence() {
    const content = document.getElementById('sequence-content');
    content.innerHTML = '<div class="sequence-numbers">' +
        currentSequence.map(num => `<div class="number-box">${num}</div>`).join('') +
        '</div>';
    
    // Hide sequence after memorize time
    setTimeout(() => {
        content.innerHTML = '<p style="font-size: 24px;">❓ What was the sequence?</p>';
        showInputBoxes();
    }, MEMORIZE_TIME);
}

function showInputBoxes() {
    const inputArea = document.getElementById('input-area');
    const inputBoxes = document.getElementById('input-boxes');
    
    inputBoxes.innerHTML = '';
    for (let i = 0; i < SEQUENCE_LENGTH; i++) {
        const input = document.createElement('input');
        input.type = 'number';
        input.className = 'input-box';
        input.min = '1';
        input.max = '9';
        input.id = `input-${i}`;
        input.onkeyup = (e) => {
            if (e.key === 'Enter') checkAnswer();
            if (input.value && i < SEQUENCE_LENGTH - 1) {
                document.getElementById(`input-${i + 1}`).focus();
            }
        };
        inputBoxes.appendChild(input);
    }
    
    inputArea.style.display = 'block';
    document.getElementById('submit-btn').style.display = 'inline-block';
    document.getElementById('input-0').focus();
}

function checkAnswer() {
    const userSequence = [];
    for (let i = 0; i < SEQUENCE_LENGTH; i++) {
        const value = document.getElementById(`input-${i}`).value;
        userSequence.push(parseInt(value) || 0);
    }
    
    const isCorrect = JSON.stringify(userSequence) === JSON.stringify(currentSequence);
    const message = document.getElementById('message');
    
    if (isCorrect) {
        correctAnswers++;
        score += Math.round(100 / MAX_ROUNDS);
        message.textContent = '✅ Correct! Well done!';
        message.className = 'message correct';
    } else {
        message.textContent = '❌ Incorrect. The correct sequence was: ' + currentSequence.join(' ');
        message.className = 'message incorrect';
    }
    
    updateDisplay();
    document.getElementById('submit-btn').style.display = 'none';
    document.getElementById('next-btn').style.display = 'inline-block';
}

function updateDisplay() {
    document.getElementById('current-round').textContent = currentRound;
    document.getElementById('score').textContent = score;
    document.getElementById('correct').textContent = correctAnswers;
}

function endGame() {
    const duration = (Date.now() - startTime) / 1000;
    const accuracy = (correctAnswers / totalAttempts) * 100;
    
    // All game logic moved to external JS file
*/
</script>

<script src="/assets/js/game-voice-helper.js"></script>
<?php require_once __DIR__ . '/../_footer.php'; ?>
