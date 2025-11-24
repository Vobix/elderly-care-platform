// Puzzle Game JavaScript
// This game requires the server-rendered CONFIG constant to be defined in the page

const PATTERNS = [
    ['🔴', '🔵', '🟢', '🟡', '🟣'],
    ['⭐', '🌙', '☀️', '🌟', '✨'],
    ['🍎', '🍊', '🍋', '🍉', '🍇'],
    ['🐶', '🐱', '🐭', '🐹', '🐰'],
    ['❤️', '💚', '💙', '💛', '💜'],
    ['🎵', '🎶', '🎸', '🎹', '🎺']
];

let currentRound = 0;
let score = 0;
let correct = 0;
let currentAnswer = '';
let gameStartTime;
let totalAttempts = 0;

function startGame() {
    currentRound = 0;
    score = 0;
    correct = 0;
    totalAttempts = 0;
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
    totalAttempts++;
    updateDisplay();
    
    document.getElementById('next-btn').style.display = 'none';
    document.getElementById('message').textContent = '';
    
    const patternSet = PATTERNS[Math.floor(Math.random() * PATTERNS.length)];
    const patternLength = 3 + Math.floor(Math.random() * 2);
    const pattern = [];
    
    for (let i = 0; i < patternLength; i++) {
        pattern.push(patternSet[i % patternSet.length]);
    }
    
    const missingIndex = Math.floor(Math.random() * pattern.length);
    currentAnswer = pattern[missingIndex];
    pattern[missingIndex] = '?';
    
    displayPattern(pattern);
    displayOptions(patternSet, currentAnswer);
}

function displayPattern(pattern) {
    const container = document.getElementById('pattern-sequence');
    container.innerHTML = '';
    
    pattern.forEach(item => {
        const box = document.createElement('div');
        box.className = item === '?' ? 'pattern-box missing' : 'pattern-box';
        box.textContent = item;
        container.appendChild(box);
    });
}

function displayOptions(patternSet, correctAnswer) {
    const container = document.getElementById('options-area');
    container.innerHTML = '';
    
    const options = [correctAnswer];
    
    while (options.length < CONFIG.options) {
        const option = patternSet[Math.floor(Math.random() * patternSet.length)];
        if (!options.includes(option)) {
            options.push(option);
        }
    }
    
    options.sort(() => Math.random() - 0.5);
    
    options.forEach(option => {
        const box = document.createElement('div');
        box.className = 'option-box';
        box.textContent = option;
        box.onclick = () => checkAnswer(option, box);
        container.appendChild(box);
    });
}

function checkAnswer(selected, element) {
    document.querySelectorAll('.option-box').forEach(box => box.onclick = null);
    
    const message = document.getElementById('message');
    
    if (selected === currentAnswer) {
        element.style.background = '#d4edda';
        element.style.borderColor = '#28a745';
        message.textContent = '✅ Correct!';
        message.style.color = '#28a745';
        correct++;
        score += Math.round(100 / CONFIG.rounds);
    } else {
        element.style.background = '#f8d7da';
        element.style.borderColor = '#dc3545';
        message.textContent = '❌ Wrong. The answer was ' + currentAnswer;
        message.style.color = '#dc3545';
    }
    
    updateDisplay();
    document.getElementById('next-btn').style.display = 'inline-block';
}

function updateDisplay() {
    document.getElementById('round').textContent = currentRound;
    document.getElementById('score').textContent = score;
    document.getElementById('correct').textContent = correct;
}

function endGame() {
    const duration = (Date.now() - gameStartTime) / 1000;
    const accuracy = (correct / totalAttempts) * 100;
    
    document.getElementById('final-score').value = score;
    document.getElementById('final-duration').value = duration;
    document.getElementById('final-accuracy').value = accuracy;
    document.getElementById('result-form').submit();
}
