// Visual Memory Game Logic

let currentLevel = 1;
let score = 0;
let lives = 3;
let highlightedSquares = [];
let selectedSquares = [];
let gameStartTime;
let gridSize;
let isShowingSquares = false;
let canClick = false;

function startGame() {
    currentLevel = 1;
    score = 0;
    lives = 3;
    gameStartTime = Date.now();
    gridSize = CONFIG.grid_size;
    
    document.getElementById('start-btn').style.display = 'none';
    document.getElementById('message').textContent = '';
    
    updateDisplay();
    nextLevel();
}

function nextLevel() {
    document.getElementById('next-btn').style.display = 'none';
    document.getElementById('message').textContent = '';
    highlightedSquares = [];
    selectedSquares = [];
    canClick = false;
    
    updateDisplay();
    createGrid();
    
    // Calculate number of squares to highlight (increases with level)
    const numSquares = CONFIG.initial_squares + Math.floor((currentLevel - 1) / 2);
    
    setTimeout(() => showSquares(numSquares), 500);
}

function createGrid() {
    const grid = document.getElementById('grid');
    grid.innerHTML = '';
    grid.style.gridTemplateColumns = `repeat(${gridSize}, 1fr)`;
    
    const totalCells = gridSize * gridSize;
    
    for (let i = 0; i < totalCells; i++) {
        const square = document.createElement('div');
        square.className = 'memory-square';
        square.dataset.index = i;
        square.onclick = () => handleSquareClick(i);
        grid.appendChild(square);
    }
}

function showSquares(numSquares) {
    isShowingSquares = true;
    const totalCells = gridSize * gridSize;
    const indices = [];
    
    // Generate random unique indices
    while (indices.length < numSquares) {
        const index = Math.floor(Math.random() * totalCells);
        if (!indices.includes(index)) {
            indices.push(index);
        }
    }
    
    highlightedSquares = indices;
    
    // Highlight the squares
    indices.forEach(index => {
        const square = document.querySelector(`[data-index="${index}"]`);
        square.classList.add('highlighted');
    });
    
    // Hide after 2 seconds
    setTimeout(() => {
        indices.forEach(index => {
            const square = document.querySelector(`[data-index="${index}"]`);
            square.classList.remove('highlighted');
        });
        isShowingSquares = false;
        canClick = true;
        document.getElementById('message').textContent = 'Click the squares that were highlighted!';
    }, 2000);
}

function handleSquareClick(index) {
    if (!canClick || isShowingSquares) return;
    
    const square = document.querySelector(`[data-index="${index}"]`);
    
    // Prevent clicking the same square twice
    if (selectedSquares.includes(index)) return;
    
    selectedSquares.push(index);
    square.classList.add('selected');
    
    // Check if correct
    if (highlightedSquares.includes(index)) {
        square.classList.add('correct');
        
        // Check if all squares found
        if (selectedSquares.length === highlightedSquares.length) {
            canClick = false;
            score += currentLevel * 10;
            currentLevel++;
            document.getElementById('message').textContent = '✅ Perfect! Moving to next level...';
            document.getElementById('message').className = 'message correct';
            setTimeout(() => nextLevel(), 1500);
        }
    } else {
        // Wrong square clicked
        square.classList.add('wrong');
        lives--;
        canClick = false;
        
        if (lives <= 0) {
            endGame();
        } else {
            document.getElementById('message').textContent = `❌ Wrong! ${lives} lives remaining. Try again!`;
            document.getElementById('message').className = 'message incorrect';
            
            // Show correct squares
            highlightedSquares.forEach(idx => {
                document.querySelector(`[data-index="${idx}"]`).classList.add('show-correct');
            });
            
            setTimeout(() => nextLevel(), 2000);
        }
    }
    
    updateDisplay();
}

function updateDisplay() {
    document.getElementById('level').textContent = currentLevel;
    document.getElementById('score').textContent = score;
    document.getElementById('lives').textContent = lives;
}

function endGame() {
    const duration = (Date.now() - gameStartTime) / 1000;
    const levelsCompleted = currentLevel - 1;
    const accuracy = (levelsCompleted / currentLevel) * 100;
    
    document.getElementById('final-score').value = score;
    document.getElementById('final-duration').value = duration;
    document.getElementById('final-attempts').value = currentLevel;
    document.getElementById('final-accuracy').value = accuracy;
    
    document.getElementById('result-form').submit();
}
