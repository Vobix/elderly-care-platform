<?php
/**
 * Attention Focus Game
 * Find specific items among distractions
 */

require_once __DIR__ . '/../_header.php';

$config = [
    'easy' => ['rounds' => 5, 'grid_size' => 4, 'targets' => 3],
    'medium' => ['rounds' => 7, 'grid_size' => 5, 'targets' => 4],
    'hard' => ['rounds' => 10, 'grid_size' => 6, 'targets' => 5]
];

$settings = $config[$difficulty];
?>

<link rel="stylesheet" href="/assets/css/game-common.css">

<style>
    .game-container { max-width: 800px; margin: 0 auto; text-align: center; padding: 20px; }
    .target-display {
        background: #e3f2fd;
        padding: 20px;
        border-radius: 10px;
        margin: 20px 0;
        font-size: 48px;
    }
    .high-contrast .target-display { background: #003366; border: 2px solid #00ffff; }
    .grid-container {
        display: grid;
        gap: 10px;
        margin: 30px auto;
        max-width: 600px;
    }
    .grid-item {
        aspect-ratio: 1;
        background: white;
        border: 3px solid #ddd;
        border-radius: 10px;
        font-size: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    .grid-item:hover { transform: scale(1.05); border-color: #2196F3; }
    .tap-only .grid-item:hover { transform: none; }
    .grid-item.correct { background: #d4edda; border-color: #28a745; animation: correct 0.5s; }
    .grid-item.wrong { background: #f8d7da; border-color: #dc3545; animation: shake 0.5s; }
    .grid-item.clicked { pointer-events: none; opacity: 0.5; }
    .high-contrast .grid-item { background: #000; border-color: #ffff00; color: #fff; }
    @keyframes correct { from { transform: scale(1.2); } to { transform: scale(1); } }
    @keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-10px); } 75% { transform: translateX(10px); } }
    .game-info { display: flex; justify-content: space-around; margin: 20px 0; flex-wrap: wrap; gap: 15px; }
    .info-box { background: white; padding: 15px 25px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .high-contrast .info-box { background: #1a1a1a; border: 2px solid #ffff00; }
    .info-box .value { font-size: 28px; font-weight: bold; color: #2196F3; }
    .high-contrast .info-box .value { color: #00ffff; }
</style>

<div class="game-container">
    <h1 style="color: #2196F3;">👁️ Attention Focus</h1>
    <p>Find all the matching symbols!</p>
    
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
            <div class="label">Found</div>
            <div class="value" id="found">0</div>
        </div>
    </div>
    
    <div class="target-display">
        <div>Find this: <span id="target-symbol">🎯</span></div>
    </div>
    
    <div class="grid-container" id="grid"></div>
    
    <div style="margin-top: 20px;">
        <button class="btn btn-success" id="start-btn" onclick="startGame()">▶️ Start Game</button>
        <a href="games.php" class="btn btn-secondary">← Back</a>
    </div>
</div>

<form id="result-form" method="POST" action="game_result.php" style="display: none;">
    <input type="hidden" name="game_type" value="attention">
    <input type="hidden" name="difficulty" value="<?php echo $difficulty; ?>">
    <input type="hidden" name="score" id="final-score">
    <input type="hidden" name="duration" id="final-duration">
    <input type="hidden" name="accuracy" id="final-accuracy">
</form>

<script>
// Game configuration from PHP
const CONFIG = <?php echo json_encode($settings); ?>;
</script>
<script src="/assets/js/attention.js"></script>
<script>
/* Game logic moved to external JS file
let currentRound = 0;
let score = 0;
let found = 0;
let targetSymbol = '';
let correctClicks = 0;
let totalClicks = 0;
let gameStartTime;

function startGame() {
    currentRound = 0;
    score = 0;
    correctClicks = 0;
    totalClicks = 0;
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
    found = 0;
    updateDisplay();
    
    targetSymbol = SYMBOLS[Math.floor(Math.random() * SYMBOLS.length)];
    document.getElementById('target-symbol').textContent = targetSymbol;
    
    createGrid();
}

function createGrid() {
    const grid = document.getElementById('grid');
    grid.innerHTML = '';
    grid.style.gridTemplateColumns = `repeat(${CONFIG.grid_size}, 1fr)`;
    
    const totalCells = CONFIG.grid_size * CONFIG.grid_size;
    const cells = [];
    
    // Add target symbols
    for (let i = 0; i < CONFIG.targets; i++) {
        cells.push({ symbol: targetSymbol, isTarget: true });
    }
    
    // Add distractor symbols
    for (let i = CONFIG.targets; i < totalCells; i++) {
        let symbol = SYMBOLS[Math.floor(Math.random() * SYMBOLS.length)];
        while (symbol === targetSymbol) {
            symbol = SYMBOLS[Math.floor(Math.random() * SYMBOLS.length)];
        }
        cells.push({ symbol: symbol, isTarget: false });
    }
    
    // Shuffle
    cells.sort(() => Math.random() - 0.5);
    
    // Create grid items
    cells.forEach((cell, index) => {
        const div = document.createElement('div');
        div.className = 'grid-item';
        div.textContent = cell.symbol;
        div.onclick = () => handleClick(div, cell.isTarget);
        grid.appendChild(div);
    });
}

function handleClick(element, isTarget) {
    if (element.classList.contains('clicked')) return;
    
    totalClicks++;
    element.classList.add('clicked');
    
    if (isTarget) {
        element.classList.add('correct');
        found++;
        correctClicks++;
        score += Math.round(100 / (CONFIG.rounds * CONFIG.targets));
        updateDisplay();
        
        if (found === CONFIG.targets) {
            setTimeout(() => nextRound(), 1000);
        }
    } else {
        element.classList.add('wrong');
        score = Math.max(0, score - 5);
        updateDisplay();
    }
}

function updateDisplay() {
    document.getElementById('round').textContent = currentRound;
    document.getElementById('score').textContent = score;
    document.getElementById('found').textContent = `${found}/${CONFIG.targets}`;
}

function endGame() {
    const duration = (Date.now() - gameStartTime) / 1000;
    const accuracy = (correctClicks / totalClicks) * 100;
    
    // All game logic moved to external JS file
*/
</script>

<?php require_once __DIR__ . '/../_footer.php'; ?>
