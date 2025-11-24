// Reaction Time Game JavaScript
// This game requires the server-rendered CONFIG constant to be defined in the page

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
    
    document.getElementById('final-score').value = Math.round(score);
    document.getElementById('final-duration').value = duration;
    document.getElementById('final-attempts').value = CONFIG.rounds;
    document.getElementById('result-form').submit();
}
