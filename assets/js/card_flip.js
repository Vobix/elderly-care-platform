// Card Flip Pattern Matching Game Logic

let currentDifficulty = 'easy';
let gridRows, gridCols, totalPairs;
let cards = [];
let flippedCards = [];
let matchedPairs = 0;
let movesRemaining = 15;
let score = 1000; // Starting score
let gameStartTime;
let canFlip = true;

// Scoring constants
const PENALTY_PER_MISS = 50; // Points deducted for wrong match
const TIME_BONUS_MULTIPLIER = 10; // Bonus points per second saved

// Card symbols (emojis)
const SYMBOLS = [
    '🍎', '🍊', '🍋', '🍌', '🍉', '🍇', '🍓', '🍑',
    '🥝', '🍍', '🥥', '🥭', '🍒', '🍈', '🫐', '🍏',
    '🌺', '🌸', '🌼', '🌻', '🌷', '🌹', '🏵️', '💐',
    '⭐', '🌟', '✨', '💫', '🌙', '☀️', '🌈', '🔥'
];

function startGame(difficulty) {
    currentDifficulty = difficulty;
    matchedPairs = 0;
    movesRemaining = 15;
    score = 1000;
    gameStartTime = Date.now();
    
    // Set grid size based on difficulty
    switch(difficulty) {
        case 'easy':
            gridRows = 4;
            gridCols = 4;
            break;
        case 'medium':
            gridRows = 4;
            gridCols = 5;
            break;
        case 'hard':
            gridRows = 4;
            gridCols = 6;
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
        checkMatch();
    }
}

function checkMatch() {
    canFlip = false;
    
    const [card1, card2] = flippedCards;
    
    if (card1.symbol === card2.symbol && card1.index !== card2.index) {
        // Match found! No move penalty, no score deduction
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
                setTimeout(() => winGame(), 500);
            }
        }, 500);
    } else {
        // No match - deduct move and points
        movesRemaining--;
        score = Math.max(0, score - PENALTY_PER_MISS); // Score can't go negative
        
        setTimeout(() => {
            card1.element.classList.remove('flipped');
            card2.element.classList.remove('flipped');
            flippedCards = [];
            canFlip = true;
            
            updateDisplay();
            
            // Check if out of moves
            if (movesRemaining <= 0) {
                gameOver();
            }
        }, 1000);
    }
}

function winGame() {
    const duration = (Date.now() - gameStartTime) / 1000; // Time in seconds
    
    // Calculate time bonus: faster = more points
    const timeBonus = Math.max(0, Math.floor((120 - duration) * TIME_BONUS_MULTIPLIER)); // Bonus for finishing under 2 minutes
    const finalScore = score + timeBonus;
    
    document.getElementById('game-over-title').textContent = '🎉 Perfect Match!';
    document.getElementById('game-over-message').textContent = 
        `You matched all ${totalPairs} pairs!\n` +
        `Time: ${duration.toFixed(1)}s\n` +
        `Base Score: ${score}\n` +
        `Time Bonus: +${timeBonus}\n` +
        `Final Score: ${finalScore}`;
    document.getElementById('game-over-overlay').style.display = 'flex';
    
    // Auto-submit after showing message
    setTimeout(() => endGame(finalScore, duration), 2000);
}

function gameOver() {
    canFlip = false;
    const duration = (Date.now() - gameStartTime) / 1000;
    
    document.getElementById('game-over-title').textContent = '😢 Out of Moves!';
    document.getElementById('game-over-message').textContent = 
        `You ran out of moves! You matched ${matchedPairs}/${totalPairs} pairs.\n` +
        `Final Score: ${score}`;
    document.getElementById('game-over-overlay').style.display = 'flex';
    
    // Auto-submit with current score
    setTimeout(() => endGame(score, duration), 2000);
}

function restartGame() {
    matchedPairs = 0;
    movesRemaining = 15;
    score = 1000;
    flippedCards = [];
    canFlip = false;
    gameStartTime = Date.now();
    updateDisplay();
    createBoard();
}

function updateDisplay() {
    document.getElementById('level').textContent = '1'; // Only 1 level
    document.getElementById('pairs').textContent = `${matchedPairs}/${totalPairs}`;
    document.getElementById('moves').textContent = `Score: ${score}`;
    document.getElementById('moves-remaining').textContent = movesRemaining;
    
    // Color code moves remaining
    if (movesRemaining <= 3) {
        document.getElementById('moves-remaining').style.color = '#f44336';
    } else if (movesRemaining <= 7) {
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

function endGame(finalScore, duration) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '../game_result.php';
    
    const fields = {
        'game_id': 'card_flip',
        'score': Math.round(finalScore),
        'duration': Math.round(duration),
        'accuracy': matchedPairs === totalPairs ? '100' : Math.round((matchedPairs / totalPairs) * 100),
        'difficulty': difficulty
    };
    
    for (const [key, value] of Object.entries(fields)) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
    }
    
    document.body.appendChild(form);
    form.submit();
}

// Allow restarting game from overlay
document.addEventListener('DOMContentLoaded', () => {
    document.querySelector('.game-over-content button')?.addEventListener('click', () => {
        document.getElementById('game-over-overlay').style.display = 'none';
        restartGame();
    });
});
