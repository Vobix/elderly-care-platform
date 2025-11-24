// Verbal Memory Game Logic

let score = 0;
let lives = 3;
let wordsShown = 0;
let seenWords = [];
let currentWord = '';
let gameStartTime;
let totalAttempts = 0;
let correctAnswers = 0;
let availableWords = [];
let usedNewWords = [];

function startGame() {
    score = 0;
    lives = 3;
    wordsShown = 0;
    seenWords = [];
    totalAttempts = 0;
    correctAnswers = 0;
    usedNewWords = [];
    gameStartTime = Date.now();
    
    // Create a copy of the word pool
    availableWords = [...WORD_POOL];
    shuffleArray(availableWords);
    
    document.getElementById('start-btn').style.display = 'none';
    document.getElementById('button-area').style.display = 'flex';
    document.getElementById('message').textContent = '';
    
    updateDisplay();
    showNextWord();
}

function showNextWord() {
    wordsShown++;
    totalAttempts++;
    
    // Decide if showing a new word or a seen word
    const showSeenWord = seenWords.length > 0 && Math.random() > CONFIG.new_word_probability;
    
    if (showSeenWord) {
        // Show a random word from seen words
        currentWord = seenWords[Math.floor(Math.random() * seenWords.length)];
    } else {
        // Show a new word
        if (availableWords.length === 0) {
            // Ran out of words, reshuffle
            availableWords = [...WORD_POOL].filter(word => !seenWords.includes(word));
            shuffleArray(availableWords);
        }
        currentWord = availableWords.pop();
        seenWords.push(currentWord);
        usedNewWords.push(currentWord);
    }
    
    document.getElementById('word-display').textContent = currentWord;
    updateDisplay();
}

function makeChoice(isSeen) {
    const wasActuallySeen = !usedNewWords.includes(currentWord);
    const isCorrect = isSeen === wasActuallySeen;
    
    if (isCorrect) {
        score += 10;
        correctAnswers++;
        showFeedback('✓', 'correct');
    } else {
        lives--;
        showFeedback('✗', 'incorrect');
        
        if (lives <= 0) {
            endGame();
            return;
        }
    }
    
    // Remove from new words list if we just saw it
    if (usedNewWords.includes(currentWord)) {
        usedNewWords = usedNewWords.filter(w => w !== currentWord);
    }
    
    updateDisplay();
    
    setTimeout(() => {
        showNextWord();
    }, 400);
}

function showFeedback(symbol, className) {
    const wordDisplay = document.getElementById('word-display');
    wordDisplay.textContent = symbol;
    wordDisplay.style.color = className === 'correct' ? '#4CAF50' : '#f44336';
    
    setTimeout(() => {
        wordDisplay.style.color = '#009688';
    }, 400);
}

function updateDisplay() {
    document.getElementById('score').textContent = score;
    document.getElementById('lives').textContent = lives;
    document.getElementById('words-shown').textContent = wordsShown;
}

function shuffleArray(array) {
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
}

function endGame() {
    const duration = (Date.now() - gameStartTime) / 1000;
    const accuracy = (correctAnswers / totalAttempts) * 100;
    
    document.getElementById('final-score').value = score;
    document.getElementById('final-duration').value = duration;
    document.getElementById('final-attempts').value = totalAttempts;
    document.getElementById('final-accuracy').value = accuracy;
    
    document.getElementById('result-form').submit();
}
