// Card Flip Pattern Matching Game Logic

let currentLevel = 1;
let currentDifficulty = 'easy';
let gridRows, gridCols, totalPairs;
let cards = [];
let flippedCards = [];
let matchedPairs = 0;
let moves = 0;
let maxMoves = 30;
let gameStartTime;
let canFlip = true;
let bestScore = 0;

// Card symbols (emojis)
const SYMBOLS = [
    '🍎', '🍊', '🍋', '🍌', '🍉', '🍇', '🍓', '🍑',
    '🥝', '🍍', '🥥', '🥭', '🍒', '🍈', '🫐', '🍏',
    '🌺', '🌸', '🌼', '🌻', '🌷', '🌹', '🏵️', '💐',
    '⭐', '🌟', '✨', '💫', '🌙', '☀️', '🌈', '🔥'
];

function startGame(difficulty) {
    currentDifficulty = difficulty;
    currentLevel = 1;
    matchedPairs = 0;
    moves = 0;
    gameStartTime = Date.now();
    
    // Set grid size based on difficulty
    switch(difficulty) {
        case 'easy':
            gridRows = 4;
            gridCols = 4;
            maxMoves = 35;
            break;
        case 'medium':
            gridRows = 4;
            gridCols = 5;
            maxMoves = 40;
            break;
        case 'hard':
            gridRows = 4;
            gridCols = 6;
            maxMoves = 45;
            break;
    }
    
    totalPairs = (gridRows * gridCols) / 2;
    
    document.getElementById('difficulty-selector').style.display = 'none';
    document.getElementById('card-grid').style.display = 'grid';
    document.getElementById('moves-info').style.display = 'block';
    document.getElementById('restart-btn').style.display = 'inline-block';
    
    updateDisplay();
    createBoard();
}

function createBoard() {
    const grid = document.getElementById('card-grid');
    grid.innerHTML = '';
    grid.style.gridTemplateColumns = `repeat(${gridCols}, 1fr)`;
    
    // Generate card pairs
    cards = [];
    const selectedSymbols = SYMBOLS.slice(0, totalPairs);
    const cardSymbols = [...selectedSymbols, ...selectedSymbols];
    
    // Shuffle cards
    shuffleArray(cardSymbols);
    
    // Create card elements
    cardSymbols.forEach((symbol, index) => {
        const card = document.createElement('div');
        card.className = 'card';
        card.dataset.symbol = symbol;
        card.dataset.index = index;
        
        card.innerHTML = `
            <div class="card-front">?</div>
            <div class="card-back">${symbol}</div>
        `;
        
        card.onclick = () => flipCard(card, symbol, index);
        grid.appendChild(card);
        cards.push({ element: card, symbol, index, matched: false });
    });
    
    // Preview cards briefly
    setTimeout(() => {
        cards.forEach(card => card.element.classList.add('flipped'));
    }, 300);
    
    setTimeout(() => {
        cards.forEach(card => card.element.classList.remove('flipped'));
        canFlip = true;
        document.getElementById('message').textContent = 'Match all pairs!';
    }, CONFIG.show_time + 300);
}

function flipCard(cardElement, symbol, index) {
    if (!canFlip) return;
    if (cardElement.classList.contains('matched')) return;
    if (cardElement.classList.contains('flipped')) return;
    if (flippedCards.length >= 2) return;
    
    // Flip the card
    cardElement.classList.add('flipped');
    flippedCards.push({ element: cardElement, symbol, index });
    
    if (flippedCards.length === 2) {
        moves++;
        updateDisplay();
        checkMatch();
    }
}

function checkMatch() {
    canFlip = false;
    
    const [card1, card2] = flippedCards;
    
    if (card1.symbol === card2.symbol && card1.index !== card2.index) {
        // Match found!
        setTimeout(() => {
            card1.element.classList.add('matched');
            card2.element.classList.add('matched');
            cards.find(c => c.index === card1.index).matched = true;
            cards.find(c => c.index === card2.index).matched = true;
            
            matchedPairs++;
            updateDisplay();
            
            flippedCards = [];
            canFlip = true;
            
            // Check if game is won
            if (matchedPairs === totalPairs) {
                setTimeout(() => winLevel(), 500);
            }
        }, 500);
    } else {
        // No match
        setTimeout(() => {
            card1.element.classList.remove('flipped');
            card2.element.classList.remove('flipped');
            flippedCards = [];
            canFlip = true;
            
            // Check if out of moves
            if (moves >= maxMoves) {
                gameOver();
            }
        }, 1000);
    }
}

function winLevel() {
    const movesLeft = maxMoves - moves;
    const score = (matchedPairs * 100) + (movesLeft * 10);
    
    if (score > bestScore) {
        bestScore = score;
        document.getElementById('best').textContent = bestScore;
    }
    
    document.getElementById('game-over-title').textContent = '🎉 Level Complete!';
    document.getElementById('game-over-message').textContent = 
        `You matched all ${totalPairs} pairs in ${moves} moves! Score: ${score}`;
    document.getElementById('game-over-overlay').style.display = 'flex';
}

function gameOver() {
    canFlip = false;
    document.getElementById('game-over-title').textContent = '😢 Out of Moves!';
    document.getElementById('game-over-message').textContent = 
        `You ran out of moves! Try again or choose a different difficulty.`;
    document.getElementById('game-over-overlay').style.display = 'flex';
    document.querySelector('.game-over-content button').textContent = 'Try Again';
}

function nextLevel() {
    currentLevel++;
    matchedPairs = 0;
    moves = 0;
    flippedCards = [];
    canFlip = false;
    
    // Decrease max moves slightly for more challenge
    maxMoves = Math.max(20, maxMoves - 2);
    
    document.getElementById('game-over-overlay').style.display = 'none';
    updateDisplay();
    createBoard();
}

function restartGame() {
    matchedPairs = 0;
    moves = 0;
    flippedCards = [];
    canFlip = false;
    updateDisplay();
    createBoard();
}

function updateDisplay() {
    document.getElementById('level').textContent = currentLevel;
    document.getElementById('pairs').textContent = `${matchedPairs}/${totalPairs}`;
    document.getElementById('moves').textContent = moves;
    document.getElementById('moves-remaining').textContent = maxMoves - moves;
    
    const remaining = maxMoves - moves;
    if (remaining <= 5) {
        document.getElementById('moves-remaining').style.color = '#f44336';
    } else if (remaining <= 10) {
        document.getElementById('moves-remaining').style.color = '#FF9800';
    } else {
        document.getElementById('moves-remaining').style.color = '#667eea';
    }
}

function shuffleArray(array) {
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
    return array;
}

function endGame() {
    const duration = (Date.now() - gameStartTime) / 1000;
    const totalScore = bestScore;
    const accuracy = (matchedPairs / totalPairs) * 100;
    
    document.getElementById('final-difficulty').value = currentDifficulty;
    document.getElementById('final-score').value = totalScore;
    document.getElementById('final-duration').value = duration;
    document.getElementById('final-attempts').value = currentLevel;
    document.getElementById('final-accuracy').value = accuracy;
    
    document.getElementById('result-form').submit();
}

// Allow ending game from overlay
document.addEventListener('DOMContentLoaded', () => {
    document.querySelector('.game-over-content .secondary')?.addEventListener('click', endGame);
});
