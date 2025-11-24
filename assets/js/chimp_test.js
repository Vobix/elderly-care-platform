// Chimp Test Game Logic

let currentLevel = 1;
let strikes = 0;
let numNumbers;
let nextExpectedNumber = 1;
let numbersHidden = false;
let canClick = false;
let gameStartTime;
let totalAttempts = 0;
let correctAttempts = 0;
let score = 0;

function startGame() {
    currentLevel = 1;
    strikes = 0;
    totalAttempts = 0;
    correctAttempts = 0;
    score = 0;
    gameStartTime = Date.now();
    numNumbers = CONFIG.starting_numbers;
    
    document.getElementById('start-btn').style.display = 'none';
    document.getElementById('message').textContent = '';
    
    updateDisplay();
    nextLevel();
}

function nextLevel() {
    document.getElementById('next-btn').style.display = 'none';
    document.getElementById('message').textContent = 'Click the first number to begin!';
    nextExpectedNumber = 1;
    numbersHidden = false;
    canClick = false;
    totalAttempts++;
    
    updateDisplay();
    createGrid();
    placeNumbers();
    
    // Enable clicking after a brief delay
    setTimeout(() => {
        canClick = true;
    }, 300);
}

function createGrid() {
    const grid = document.getElementById('chimp-grid');
    grid.innerHTML = '';
    grid.style.gridTemplateColumns = `repeat(${CONFIG.grid_size}, 1fr)`;
    
    const totalCells = CONFIG.grid_size * CONFIG.grid_size;
    
    for (let i = 0; i < totalCells; i++) {
        const cell = document.createElement('div');
        cell.className = 'chimp-cell';
        cell.dataset.index = i;
        grid.appendChild(cell);
    }
}

function placeNumbers() {
    const totalCells = CONFIG.grid_size * CONFIG.grid_size;
    const positions = [];
    
    // Generate unique random positions
    while (positions.length < numNumbers) {
        const pos = Math.floor(Math.random() * totalCells);
        if (!positions.includes(pos)) {
            positions.push(pos);
        }
    }
    
    // Place numbers on the grid
    positions.forEach((pos, index) => {
        const cell = document.querySelector(`[data-index="${pos}"]`);
        const number = index + 1;
        
        cell.textContent = number;
        cell.dataset.number = number;
        cell.classList.add('has-number');
        cell.onclick = () => handleNumberClick(number, cell);
    });
}

function handleNumberClick(number, cell) {
    if (!canClick) return;
    
    // First click - hide all other numbers
    if (number === 1 && !numbersHidden) {
        numbersHidden = true;
        document.querySelectorAll('.chimp-cell.has-number').forEach(c => {
            if (c !== cell) {
                c.textContent = '';
                c.classList.add('hidden-number');
            }
        });
        document.getElementById('message').textContent = 'Keep going...';
    }
    
    // Check if correct number
    if (number === nextExpectedNumber) {
        cell.classList.add('correct-click');
        cell.classList.remove('has-number', 'hidden-number');
        cell.textContent = '✓';
        cell.onclick = null;
        
        nextExpectedNumber++;
        
        // Check if all numbers clicked
        if (nextExpectedNumber > numNumbers) {
            canClick = false;
            correctAttempts++;
            score += numNumbers * 10;
            currentLevel++;
            numNumbers++;
            
            document.getElementById('message').textContent = `✅ Perfect! Level ${currentLevel} unlocked!`;
            document.getElementById('message').className = 'message correct';
            document.getElementById('next-btn').style.display = 'inline-block';
        }
    } else {
        // Wrong number clicked
        canClick = false;
        strikes++;
        
        cell.classList.add('wrong-click');
        
        // Show all numbers to reveal mistake
        document.querySelectorAll('.chimp-cell.hidden-number').forEach(c => {
            c.textContent = c.dataset.number;
            c.classList.add('revealed');
        });
        
        if (strikes >= 3) {
            document.getElementById('message').textContent = '❌ Game Over! Too many strikes.';
            document.getElementById('message').className = 'message incorrect';
            setTimeout(() => endGame(), 2000);
        } else {
            document.getElementById('message').textContent = `❌ Wrong! Strike ${strikes}/3. Try again!`;
            document.getElementById('message').className = 'message incorrect';
            document.getElementById('next-btn').style.display = 'inline-block';
        }
    }
    
    updateDisplay();
}

function updateDisplay() {
    document.getElementById('level').textContent = currentLevel;
    document.getElementById('numbers').textContent = numNumbers;
    document.getElementById('strikes').textContent = `${strikes}/3`;
}

function endGame() {
    const duration = (Date.now() - gameStartTime) / 1000;
    const accuracy = (correctAttempts / totalAttempts) * 100;
    
    document.getElementById('final-score').value = score;
    document.getElementById('final-duration').value = duration;
    document.getElementById('final-attempts').value = totalAttempts;
    document.getElementById('final-accuracy').value = accuracy;
    
    document.getElementById('result-form').submit();
}
