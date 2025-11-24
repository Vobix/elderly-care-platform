// Attention Game JavaScript
// This game requires the server-rendered CONFIG constant to be defined in the page

const SYMBOLS = ['🎯', '⭐', '🎨', '🎭', '🎪', '🎸', '🎺', '🎻', '🎲', '🎰', '🏆', '🏅', '⚽', '🏀', '🎾', '🏐'];
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
    
    document.getElementById('final-score').value = score;
    document.getElementById('final-duration').value = duration;
    document.getElementById('final-accuracy').value = accuracy;
    document.getElementById('result-form').submit();
}
