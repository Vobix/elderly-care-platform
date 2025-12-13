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
let gridSize;
let hideDelay = 3000; // Starting delay in ms

function startGame() {
    currentLevel = 1;
    strikes = 0;
    totalAttempts = 0;
    correctAttempts = 0;
    score = 0;
    hideDelay = 3000;
    gameStartTime = Date.now();
    numNumbers = CONFIG.starting_numbers;
    gridSize = CONFIG.grid_size;
    
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
    
    // Dynamically adjust grid size to accommodate more numbers
    const minGridSize = Math.ceil(Math.sqrt(numNumbers * 1.8));
    gridSize = Math.max(CONFIG.grid_size, minGridSize);
    
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
    grid.style.gridTemplateColumns = `repeat(${gridSize}, 1fr)`;
    
    const totalCells = gridSize * gridSize;
    
    for (let i = 0; i < totalCells; i++) {
        const cell = document.createElement('div');
        cell.className = 'chimp-cell';
        cell.dataset.index = i;
        grid.appendChild(cell);
    }
}

function placeNumbers() {
    const totalCells = gridSize * gridSize;
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
    
    // Auto-hide numbers after delay (gets faster each level)
    setTimeout(() => {
        if (!numbersHidden && canClick) {
            numbersHidden = true;
            document.querySelectorAll('.chimp-cell.has-number').forEach(c => {
                c.classList.add('flip-hide');
                setTimeout(() => {
                    c.textContent = '';
                    c.classList.add('hidden-number');
                    c.classList.remove('flip-hide');
                }, 300);
            });
            document.getElementById('message').textContent = 'Click them in order!';
        }
    }, hideDelay);
}

function handleNumberClick(number, cell) {
    if (!canClick) return;
    
    // First click - immediately hide all numbers
    if (!numbersHidden) {
        numbersHidden = true;
        document.querySelectorAll('.chimp-cell.has-number').forEach(c => {
            c.textContent = ''; // Remove number immediately before flip
            c.classList.add('flip-hide');
            setTimeout(() => {
                if (!c.classList.contains('correct-click')) {
                    c.classList.add('hidden-number');
                }
                c.classList.remove('flip-hide');
            }, 300);
        });
        document.getElementById('message').textContent = 'Click them in order!';
    }
    
    // Check if correct number
    if (number === nextExpectedNumber) {
        cell.classList.add('flipped');
        setTimeout(() => {
            cell.classList.add('correct-click');
            cell.classList.remove('has-number', 'hidden-number');
            cell.textContent = '✓';
            cell.onclick = null;
        }, 300);
        
        nextExpectedNumber++;
        
        // Check if all numbers clicked
        if (nextExpectedNumber > numNumbers) {
            canClick = false;
            correctAttempts++;
            score += currentLevel * 100; // Level-based scoring: level 1 = 100, level 2 = 200, etc.
            currentLevel++;
            numNumbers++;
            
            // Ramp up difficulty: decrease hide delay from 3s to 1s minimum
            if (currentLevel === 2) hideDelay = 2500;
            else if (currentLevel === 3) hideDelay = 2000;
            else if (currentLevel === 4) hideDelay = 1500;
            else if (currentLevel >= 5) hideDelay = 1000;
            
            document.getElementById('message').textContent = `✅ Perfect! Level ${currentLevel} unlocked! +${(currentLevel - 1) * 100} points`;
            document.getElementById('message').className = 'message correct';
            document.getElementById('next-btn').style.display = 'inline-block';
        }
    } else {
        // Wrong number clicked
        canClick = false;
        strikes++;
        
        cell.classList.add('flipped');
        setTimeout(() => {
            cell.classList.add('wrong-click');
        }, 300);
        
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
    document.getElementById('score').textContent = score;
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
