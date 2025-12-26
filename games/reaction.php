<?php
/**
 * Reaction Time Game
 * Click as fast as you can when the target appears
 */

require_once __DIR__ . '/../_header.php';

// Fixed settings - no difficulty selection
$settings = ['rounds' => 5, 'min_delay' => 1500, 'max_delay' => 3500];
?>

<link rel="stylesheet" href="/assets/css/game-common.css">

<style>
    .game-container { max-width: 800px; margin: 0 auto; text-align: center; padding: 20px; }
    .reaction-zone {
        width: 100%;
        height: 400px;
        background: #f5f5f5;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 24px;
        margin: 30px 0;
        border: 3px solid #ddd;
        transition: all 0.3s;
    }
    .reaction-zone.waiting { background: #fff3cd; border-color: #ffc107; }
    .reaction-zone.ready { background: #d4edda; border-color: #28a745; }
    .reaction-zone.too-soon { background: #f8d7da; border-color: #dc3545; }
    .high-contrast .reaction-zone { background: #000; border-color: #ffff00; color: #fff; }
    .high-contrast .reaction-zone.ready { background: #004400; border-color: #00ff00; }
    .target-circle {
        width: 150px;
        height: 150px;
        background: #28a745;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        animation: pulse 0.5s infinite;
    }
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }
    .game-info { display: flex; justify-content: space-around; margin: 20px 0; flex-wrap: wrap; gap: 15px; }
    .info-box { background: white; padding: 15px 25px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); min-width: 120px; }
    .high-contrast .info-box { background: #1a1a1a; border: 2px solid #ffff00; }
    .info-box .label { font-size: 14px; color: #666; }
    .high-contrast .info-box .label { color: #ccc; }
    .info-box .value { font-size: 28px; font-weight: bold; color: #FF9800; }
    .high-contrast .info-box .value { color: #ffff00; }
</style>

<div class="game-container">
    <h1 style="color: #FF9800;">⚡ Reaction Time</h1>
    <p>Click the target as fast as you can!</p>
    
    <div class="game-info">
        <div class="info-box">
            <div class="label">Round</div>
            <div class="value" id="round">0</div>
        </div>
        <div class="info-box">
            <div class="label">Avg Time</div>
            <div class="value" id="avg-time">0ms</div>
        </div>
        <div class="info-box">
            <div class="label">Best</div>
            <div class="value" id="best-time">-</div>
        </div>
    </div>
    
    <div class="reaction-zone" id="reaction-zone">
        <div id="zone-content">Click "Start Game" to begin!</div>
    </div>
    
    <div style="margin-top: 20px;">
        <button class="btn btn-success" id="start-btn" onclick="startGame()">▶️ Start Game</button>
        <a href="/pages/games.php" class="btn btn-secondary">← Back</a>
    </div>
</div>

<form id="result-form" method="POST" action="/pages/game_result.php" style="display: none;">
    <input type="hidden" name="game_type" value="reaction">
    <input type="hidden" name="score" id="final-score">
    <input type="hidden" name="duration" id="final-duration">
    <input type="hidden" name="attempts" id="final-attempts">
</form>

<script>
// Game configuration from PHP
const CONFIG = <?php echo json_encode($settings); ?>;
</script>
<script src="/assets/js/game-sounds.js"></script>
<script src="/assets/js/reaction.js"></script>
<script>
/* Game logic moved to external JS file
let currentRound = 0;
let reactionTimes = [];
let startTime, clickTime, timeout;
let gameStartTime;
let isWaiting = false;

function startGame() {
    currentRound = 0;
    reactionTimes = [];
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
    const zone = document.getElementById('reaction-zone');
    zone.className = 'reaction-zone waiting';
    document.getElementById('zone-content').textContent = '⏳ Wait for it...';
    isWaiting = true;
    
    const delay = CONFIG.min_delay + Math.random() * (CONFIG.max_delay - CONFIG.min_delay);
    timeout = setTimeout(showTarget, delay);
    
    zone.onclick = () => {
        if (isWaiting) {
            clearTimeout(timeout);
            zone.className = 'reaction-zone too-soon';
            document.getElementById('zone-content').textContent = '❌ Too soon! Wait for the target.';
            setTimeout(() => nextRound(), 1500);
        }
    };
}

function showTarget() {
    isWaiting = false;
    const zone = document.getElementById('reaction-zone');
    zone.className = 'reaction-zone ready';
    document.getElementById('zone-content').innerHTML = '<div class="target-circle">👆</div>';
    startTime = Date.now();
    
    zone.onclick = recordClick;
}

function recordClick() {
    clickTime = Date.now();
    const reactionTime = clickTime - startTime;
    reactionTimes.push(reactionTime);
    
    const zone = document.getElementById('reaction-zone');
    zone.className = 'reaction-zone';
    document.getElementById('zone-content').innerHTML = `<div style="font-size: 36px;">⚡ ${reactionTime}ms</div>`;
    
    updateStats();
    zone.onclick = null;
    
    setTimeout(() => nextRound(), 1000);
}

function updateStats() {
    document.getElementById('round').textContent = currentRound;
    const avg = Math.round(reactionTimes.reduce((a,b) => a+b, 0) / reactionTimes.length);
    document.getElementById('avg-time').textContent = avg + 'ms';
    const best = Math.min(...reactionTimes);
    document.getElementById('best-time').textContent = best + 'ms';
}

function endGame() {
    const duration = (Date.now() - gameStartTime) / 1000;
    const avgTime = reactionTimes.reduce((a,b) => a+b, 0) / reactionTimes.length;
    const score = Math.max(0, 100 - (avgTime - 200) / 10);
    
    // All game logic moved to external JS file
*/
</script>

<script src="/assets/js/game-voice-helper.js"></script>
<?php require_once __DIR__ . '/../_footer.php'; ?>
