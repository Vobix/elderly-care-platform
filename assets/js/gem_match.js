/**
 * Gem Match - Match-3 Game
 * Match 3 or more gems to score points before time runs out
 */

// Game configuration
const GRID_SIZE = 8;
const GAME_DURATION = 60; // seconds
const GEM_TYPES = ['🔴', '🔵', '🟢', '🟡', '🟣', '🟠'];

// Game state
let grid = [];
let selectedGem = null;
let score = 0;
let totalMatches = 0;
let currentCombo = 0;
let maxCombo = 0;
let timeRemaining = GAME_DURATION;
let gameActive = false;
let timerInterval = null;
let isProcessing = false;
let isDragging = false;
let dragStartGem = null;
let dragTargetGem = null;
let dragElement = null;

// DOM elements
const boardElement = document.getElementById('gemBoard');
const scoreDisplay = document.getElementById('scoreDisplay');
const matchesDisplay = document.getElementById('matchesDisplay');
const comboDisplay = document.getElementById('comboDisplay');
const timerBar = document.getElementById('timerBar');
const gameOverScreen = document.getElementById('gameOverScreen');

// Initialize game
function initGame() {
    createGrid();
    renderGrid();
    startTimer();
    gameActive = true;
}

// Create initial grid with no matches
function createGrid() {
    grid = [];
    for (let row = 0; row < GRID_SIZE; row++) {
        grid[row] = [];
        for (let col = 0; col < GRID_SIZE; col++) {
            let gemType;
            do {
                gemType = getRandomGem();
            } while (wouldCreateMatch(row, col, gemType));
            grid[row][col] = gemType;
        }
    }
}

// Get random gem type
function getRandomGem() {
    return GEM_TYPES[Math.floor(Math.random() * GEM_TYPES.length)];
}

// Check if placing a gem would create a match (for initial grid)
function wouldCreateMatch(row, col, gemType) {
    // Check horizontal
    let horizontalCount = 1;
    if (col >= 1 && grid[row][col - 1] === gemType) horizontalCount++;
    if (col >= 2 && grid[row][col - 2] === gemType) horizontalCount++;
    if (horizontalCount >= 3) return true;

    // Check vertical
    let verticalCount = 1;
    if (row >= 1 && grid[row - 1][col] === gemType) verticalCount++;
    if (row >= 2 && grid[row - 2][col] === gemType) verticalCount++;
    if (verticalCount >= 3) return true;

    return false;
}

// Render the grid
function renderGrid() {
    boardElement.innerHTML = '';
    for (let row = 0; row < GRID_SIZE; row++) {
        for (let col = 0; col < GRID_SIZE; col++) {
            const gemElement = document.createElement('div');
            gemElement.className = 'gem';
            gemElement.textContent = grid[row][col];
            gemElement.dataset.row = row;
            gemElement.dataset.col = col;
            gemElement.addEventListener('click', () => handleGemClick(row, col));
            
            // Add drag events
            gemElement.addEventListener('mousedown', (e) => handleDragStart(row, col, e));
            gemElement.addEventListener('mouseenter', (e) => handleDragOver(row, col, e));
            
            // Add touch events for mobile
            gemElement.addEventListener('touchstart', (e) => handleDragStart(row, col, e));
            gemElement.addEventListener('touchmove', (e) => handleTouchMove(e));
            gemElement.addEventListener('touchend', (e) => handleDragEnd());
            
            boardElement.appendChild(gemElement);
        }
    }
    
    // Add global mouseup listener to handle drag end even outside grid
    document.addEventListener('mouseup', handleDragEnd);
}

// Handle gem click
function handleGemClick(row, col) {
    if (!gameActive || isProcessing || isDragging) return;

    const clickedGem = { row, col };

    if (selectedGem === null) {
        // First gem selected
        selectedGem = clickedGem;
        highlightGem(row, col, true);
        gameSounds.playClick();
    } else {
        // Second gem selected
        if (selectedGem.row === row && selectedGem.col === col) {
            // Clicked same gem, deselect
            highlightGem(row, col, false);
            selectedGem = null;
            return;
        }

        // Check if gems are adjacent
        if (areAdjacent(selectedGem, clickedGem)) {
            highlightGem(selectedGem.row, selectedGem.col, false);
            swapGems(selectedGem, clickedGem);
            selectedGem = null;
        } else {
            // Not adjacent, select new gem
            highlightGem(selectedGem.row, selectedGem.col, false);
            selectedGem = clickedGem;
            highlightGem(row, col, true);
            gameSounds.playClick();
        }
    }
}

// Handle drag start
function handleDragStart(row, col, e) {
    if (!gameActive || isProcessing) return;
    
    e.preventDefault();
    isDragging = true;
    dragStartGem = { row, col };
    dragTargetGem = null;
    
    // Clear any previous selection
    if (selectedGem !== null) {
        highlightGem(selectedGem.row, selectedGem.col, false);
        selectedGem = null;
    }
    
    // Mark source gem as being dragged
    const sourceIndex = row * GRID_SIZE + col;
    const sourceElement = boardElement.children[sourceIndex];
    sourceElement.classList.add('drag-source');
    
    // Create dragging element
    dragElement = document.createElement('div');
    dragElement.className = 'gem-dragging';
    dragElement.textContent = grid[row][col];
    document.body.appendChild(dragElement);
    
    // Position at cursor/touch
    const clientX = e.clientX || (e.touches && e.touches[0].clientX);
    const clientY = e.clientY || (e.touches && e.touches[0].clientY);
    dragElement.style.left = clientX + 'px';
    dragElement.style.top = clientY + 'px';
    
    gameSounds.playClick();
}

// Handle drag over (mouse)
function handleDragOver(row, col, e) {
    if (!isDragging || !dragStartGem) return;
    
    // Update dragging element position
    if (dragElement && e.clientX) {
        dragElement.style.left = e.clientX + 'px';
        dragElement.style.top = e.clientY + 'px';
    }
    
    const targetGem = { row, col };
    
    // Check if we dragged to an adjacent gem
    if (areAdjacent(dragStartGem, targetGem) && 
        (dragStartGem.row !== row || dragStartGem.col !== col)) {
        // Unhighlight previous target
        if (dragTargetGem) {
            const prevIndex = dragTargetGem.row * GRID_SIZE + dragTargetGem.col;
            boardElement.children[prevIndex].classList.remove('drag-target');
        }
        // Highlight new target
        dragTargetGem = targetGem;
        const targetIndex = row * GRID_SIZE + col;
        boardElement.children[targetIndex].classList.add('drag-target');
    } else if (dragStartGem.row === row && dragStartGem.col === col) {
        // Dragging over the source gem - clear target
        if (dragTargetGem) {
            const prevIndex = dragTargetGem.row * GRID_SIZE + dragTargetGem.col;
            boardElement.children[prevIndex].classList.remove('drag-target');
            dragTargetGem = null;
        }
    }
}

// Handle mouse move (for updating drag element position)
function handleMouseMove(e) {
    if (!isDragging || !dragElement) return;
    dragElement.style.left = e.clientX + 'px';
    dragElement.style.top = e.clientY + 'px';
}

// Handle touch move
function handleTouchMove(e) {
    if (!isDragging || !dragStartGem) return;
    
    e.preventDefault();
    const touch = e.touches[0];
    
    // Update dragging element position
    if (dragElement) {
        dragElement.style.left = touch.clientX + 'px';
        dragElement.style.top = touch.clientY + 'px';
    }
    
    const element = document.elementFromPoint(touch.clientX, touch.clientY);
    
    if (element && element.classList.contains('gem')) {
        const row = parseInt(element.dataset.row);
        const col = parseInt(element.dataset.col);
        const targetGem = { row, col };
        
        // Check if we dragged to an adjacent gem
        if (areAdjacent(dragStartGem, targetGem) && 
            (dragStartGem.row !== row || dragStartGem.col !== col)) {
            // Unhighlight previous target
            if (dragTargetGem && (dragTargetGem.row !== row || dragTargetGem.col !== col)) {
                const prevIndex = dragTargetGem.row * GRID_SIZE + dragTargetGem.col;
                boardElement.children[prevIndex].classList.remove('drag-target');
            }
            // Highlight new target
            dragTargetGem = targetGem;
            const targetIndex = row * GRID_SIZE + col;
            boardElement.children[targetIndex].classList.add('drag-target');
        } else if (dragStartGem.row === row && dragStartGem.col === col) {
            // Dragging over the source gem - clear target
            if (dragTargetGem) {
                const prevIndex = dragTargetGem.row * GRID_SIZE + dragTargetGem.col;
                boardElement.children[prevIndex].classList.remove('drag-target');
                dragTargetGem = null;
            }
        }
    }
}

// Handle drag end
function handleDragEnd() {
    // Always remove dragging element first
    if (dragElement) {
        dragElement.remove();
        dragElement = null;
    }
    
    // Clear drag source styling
    if (dragStartGem) {
        const sourceIndex = dragStartGem.row * GRID_SIZE + dragStartGem.col;
        if (boardElement.children[sourceIndex]) {
            boardElement.children[sourceIndex].classList.remove('drag-source');
        }
    }
    
    // Clear drag target styling
    if (dragTargetGem) {
        const targetIndex = dragTargetGem.row * GRID_SIZE + dragTargetGem.col;
        if (boardElement.children[targetIndex]) {
            boardElement.children[targetIndex].classList.remove('drag-target');
        }
        
        // Only swap if we have both valid start and target gems
        if (dragStartGem && areAdjacent(dragStartGem, dragTargetGem)) {
            swapGems(dragStartGem, dragTargetGem);
        }
    }
    
    // Reset all drag state
    isDragging = false;
    dragStartGem = null;
    dragTargetGem = null;
}

// Check if two gems are adjacent
function areAdjacent(gem1, gem2) {
    const rowDiff = Math.abs(gem1.row - gem2.row);
    const colDiff = Math.abs(gem1.col - gem2.col);
    return (rowDiff === 1 && colDiff === 0) || (rowDiff === 0 && colDiff === 1);
}

// Highlight or unhighlight a gem
function highlightGem(row, col, highlight) {
    const index = row * GRID_SIZE + col;
    const gemElement = boardElement.children[index];
    if (highlight) {
        gemElement.classList.add('selected');
    } else {
        gemElement.classList.remove('selected');
    }
}

// Swap two gems
async function swapGems(gem1, gem2) {
    isProcessing = true;

    // Swap in grid
    const temp = grid[gem1.row][gem1.col];
    grid[gem1.row][gem1.col] = grid[gem2.row][gem2.col];
    grid[gem2.row][gem2.col] = temp;

    renderGrid();
    gameSounds.playMove();

    // Check for matches
    const matches = findAllMatches();
    
    if (matches.length > 0) {
        // Valid swap
        await processMatches();
    } else {
        // Invalid swap, swap back
        const tempBack = grid[gem1.row][gem1.col];
        grid[gem1.row][gem1.col] = grid[gem2.row][gem2.col];
        grid[gem2.row][gem2.col] = tempBack;
        renderGrid();
        gameSounds.playError();
        currentCombo = 0;
        updateComboDisplay();
    }

    isProcessing = false;
}

// Find all matches on the board
function findAllMatches() {
    const matches = [];

    // Check horizontal matches
    for (let row = 0; row < GRID_SIZE; row++) {
        for (let col = 0; col < GRID_SIZE - 2; col++) {
            const gemType = grid[row][col];
            let matchLength = 1;
            
            while (col + matchLength < GRID_SIZE && grid[row][col + matchLength] === gemType) {
                matchLength++;
            }

            if (matchLength >= 3) {
                for (let i = 0; i < matchLength; i++) {
                    matches.push({ row, col: col + i });
                }
                col += matchLength - 1;
            }
        }
    }

    // Check vertical matches
    for (let col = 0; col < GRID_SIZE; col++) {
        for (let row = 0; row < GRID_SIZE - 2; row++) {
            const gemType = grid[row][col];
            let matchLength = 1;
            
            while (row + matchLength < GRID_SIZE && grid[row + matchLength][col] === gemType) {
                matchLength++;
            }

            if (matchLength >= 3) {
                for (let i = 0; i < matchLength; i++) {
                    matches.push({ row: row + i, col });
                }
                row += matchLength - 1;
            }
        }
    }

    // Remove duplicates
    const uniqueMatches = [];
    const seen = new Set();
    for (const match of matches) {
        const key = `${match.row},${match.col}`;
        if (!seen.has(key)) {
            seen.add(key);
            uniqueMatches.push(match);
        }
    }

    return uniqueMatches;
}

// Process matches and update score
async function processMatches() {
    let hasMatches = true;
    
    while (hasMatches) {
        const matches = findAllMatches();
        
        if (matches.length === 0) {
            hasMatches = false;
            currentCombo = 0;
            updateComboDisplay();
            break;
        }

        // Increment combo
        currentCombo++;
        if (currentCombo > maxCombo) {
            maxCombo = currentCombo;
        }
        updateComboDisplay();

        // Calculate score
        const baseScore = matches.length * 10;
        const comboBonus = currentCombo > 1 ? (currentCombo - 1) * 20 : 0;
        const matchScore = baseScore + comboBonus;
        score += matchScore;
        totalMatches++;

        updateScore();

        // Play sound
        if (currentCombo > 1) {
            gameSounds.playAchievement();
        } else {
            gameSounds.playSuccess();
        }

        // Animate matched gems
        await animateMatches(matches);

        // Remove matched gems
        for (const match of matches) {
            grid[match.row][match.col] = null;
        }

        // Apply gravity
        applyGravity();

        // Fill empty spaces
        fillEmptySpaces();

        // Render and wait
        renderGrid();
        await sleep(300);
    }
}

// Animate matched gems
async function animateMatches(matches) {
    for (const match of matches) {
        const index = match.row * GRID_SIZE + match.col;
        const gemElement = boardElement.children[index];
        gemElement.classList.add('matching');
    }
    await sleep(500);
}

// Apply gravity to gems
function applyGravity() {
    for (let col = 0; col < GRID_SIZE; col++) {
        let emptyRow = GRID_SIZE - 1;
        
        for (let row = GRID_SIZE - 1; row >= 0; row--) {
            if (grid[row][col] !== null) {
                if (row !== emptyRow) {
                    grid[emptyRow][col] = grid[row][col];
                    grid[row][col] = null;
                }
                emptyRow--;
            }
        }
    }
    
    // Check if board is still playable
    if (!hasValidMoves()) {
        console.log('No valid moves - shuffling board');
        shuffleBoard();
    }
}

// Fill empty spaces with new gems
function fillEmptySpaces() {
    for (let row = 0; row < GRID_SIZE; row++) {
        for (let col = 0; col < GRID_SIZE; col++) {
            if (grid[row][col] === null) {
                grid[row][col] = getRandomGem();
            }
        }
    }
}

// Update score display
function updateScore() {
    scoreDisplay.textContent = score;
    matchesDisplay.textContent = totalMatches;
}

// Update combo display
function updateComboDisplay() {
    comboDisplay.textContent = currentCombo;
    if (currentCombo > 1) {
        comboDisplay.style.color = '#FF5722';
        comboDisplay.style.transform = 'scale(1.2)';
        setTimeout(() => {
            comboDisplay.style.transform = 'scale(1)';
        }, 200);
    } else {
        comboDisplay.style.color = '#333';
    }
}

// Check if any valid moves are possible
function hasValidMoves() {
    for (let row = 0; row < GRID_SIZE; row++) {
        for (let col = 0; col < GRID_SIZE; col++) {
            // Try swapping with right neighbor
            if (col < GRID_SIZE - 1) {
                // Simulate swap
                const temp = grid[row][col];
                grid[row][col] = grid[row][col + 1];
                grid[row][col + 1] = temp;
                
                // Check if this creates a match
                const matches = findAllMatches();
                
                // Swap back
                grid[row][col + 1] = grid[row][col];
                grid[row][col] = temp;
                
                if (matches.length > 0) return true;
            }
            
            // Try swapping with bottom neighbor
            if (row < GRID_SIZE - 1) {
                // Simulate swap
                const temp = grid[row][col];
                grid[row][col] = grid[row + 1][col];
                grid[row + 1][col] = temp;
                
                // Check if this creates a match
                const matches = findAllMatches();
                
                // Swap back
                grid[row + 1][col] = grid[row][col];
                grid[row][col] = temp;
                
                if (matches.length > 0) return true;
            }
        }
    }
    return false;
}

// Shuffle board when no moves available
function shuffleBoard() {
    const allGems = [];
    for (let row = 0; row < GRID_SIZE; row++) {
        for (let col = 0; col < GRID_SIZE; col++) {
            allGems.push(grid[row][col]);
        }
    }
    
    // Shuffle array
    for (let i = allGems.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [allGems[i], allGems[j]] = [allGems[j], allGems[i]];
    }
    
    // Rebuild grid
    let idx = 0;
    for (let row = 0; row < GRID_SIZE; row++) {
        for (let col = 0; col < GRID_SIZE; col++) {
            grid[row][col] = allGems[idx++];
        }
    }
    
    renderGrid();
    gameSounds.playMove();
}

// Start timer
function startTimer() {
    updateTimerDisplay();
    
    timerInterval = setInterval(() => {
        timeRemaining--;
        updateTimerDisplay();

        if (timeRemaining <= 0) {
            endGame();
        }
    }, 1000);
}

// Update timer display
function updateTimerDisplay() {
    const percentage = (timeRemaining / GAME_DURATION) * 100;
    timerBar.style.width = percentage + '%';
    timerBar.textContent = timeRemaining + 's';

    // Change color based on time remaining
    timerBar.classList.remove('warning', 'danger');
    if (timeRemaining <= 10) {
        timerBar.classList.add('danger');
    } else if (timeRemaining <= 20) {
        timerBar.classList.add('warning');
    }

    // Play warning sound at 10 seconds
    if (timeRemaining === 10) {
        gameSounds.playError();
    }
}

// End game
function endGame() {
    gameActive = false;
    clearInterval(timerInterval);
    
    gameSounds.playGameOver();

    // Show game over screen
    document.getElementById('finalScoreText').textContent = score;
    document.getElementById('finalMatchesText').textContent = totalMatches;
    document.getElementById('finalComboText').textContent = maxCombo;
    gameOverScreen.style.display = 'flex';

    // Submit score and redirect
    setTimeout(() => {
        submitScore();
    }, 2000);
}

// Submit score to database
function submitScore() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/pages/game_result.php';

    const gameTypeInput = document.createElement('input');
    gameTypeInput.type = 'hidden';
    gameTypeInput.name = 'game_type';
    gameTypeInput.value = 'gem_match';
    form.appendChild(gameTypeInput);

    const scoreInput = document.createElement('input');
    scoreInput.type = 'hidden';
    scoreInput.name = 'score';
    scoreInput.value = score;
    form.appendChild(scoreInput);

    const durationInput = document.createElement('input');
    durationInput.type = 'hidden';
    durationInput.name = 'duration';
    durationInput.value = GAME_DURATION - timeRemaining;
    form.appendChild(durationInput);

    document.body.appendChild(form);
    form.submit();
}

// Utility function to sleep
function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

// Start game when page loads
window.addEventListener('load', () => {
    initGame();
    
    // Add global mouse move listener for drag updates
    document.addEventListener('mousemove', handleMouseMove);
});
