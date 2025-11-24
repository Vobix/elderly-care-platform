// Memory Game JavaScript
// This game requires the server-rendered CONFIG constant to be defined in the page

let currentRound = 0;
let score = 0;
let correctAnswers = 0;
let totalAttempts = 0;
let currentSequence = [];
let startTime;

function startGame() {
    currentRound = 0;
    score = 0;
    correctAnswers = 0;
    totalAttempts = 0;
    startTime = Date.now();
    
    document.getElementById('start-btn').style.display = 'none';
    nextRound();
}

function nextRound() {
    if (currentRound >= MAX_ROUNDS) {
        endGame();
        return;
    }
    
    currentRound++;
    totalAttempts++;
    updateDisplay();
    
    document.getElementById('next-btn').style.display = 'none';
    document.getElementById('submit-btn').style.display = 'none';
    document.getElementById('input-area').style.display = 'none';
    document.getElementById('message').textContent = '';
    
    // Generate random sequence
    currentSequence = [];
    for (let i = 0; i < SEQUENCE_LENGTH; i++) {
        currentSequence.push(Math.floor(Math.random() * 9) + 1);
    }
    
    // Show sequence
    showSequence();
}

function showSequence() {
    const content = document.getElementById('sequence-content');
    content.innerHTML = '<div class="sequence-numbers">' +
        currentSequence.map(num => `<div class="number-box">${num}</div>`).join('') +
        '</div>';
    
    // Hide sequence after memorize time
    setTimeout(() => {
        content.innerHTML = '<p style="font-size: 24px;">❓ What was the sequence?</p>';
        showInputBoxes();
    }, MEMORIZE_TIME);
}

function showInputBoxes() {
    const inputArea = document.getElementById('input-area');
    const inputBoxes = document.getElementById('input-boxes');
    
    inputBoxes.innerHTML = '';
    for (let i = 0; i < SEQUENCE_LENGTH; i++) {
        const input = document.createElement('input');
        input.type = 'number';
        input.className = 'input-box';
        input.min = '1';
        input.max = '9';
        input.id = `input-${i}`;
        input.onkeyup = (e) => {
            if (e.key === 'Enter') checkAnswer();
            if (input.value && i < SEQUENCE_LENGTH - 1) {
                document.getElementById(`input-${i + 1}`).focus();
            }
        };
        inputBoxes.appendChild(input);
    }
    
    inputArea.style.display = 'block';
    document.getElementById('submit-btn').style.display = 'inline-block';
    document.getElementById('input-0').focus();
}

function checkAnswer() {
    const userSequence = [];
    for (let i = 0; i < SEQUENCE_LENGTH; i++) {
        const value = document.getElementById(`input-${i}`).value;
        userSequence.push(parseInt(value) || 0);
    }
    
    const isCorrect = JSON.stringify(userSequence) === JSON.stringify(currentSequence);
    const message = document.getElementById('message');
    
    if (isCorrect) {
        correctAnswers++;
        score += Math.round(100 / MAX_ROUNDS);
        message.textContent = '✅ Correct! Well done!';
        message.className = 'message correct';
    } else {
        message.textContent = '❌ Incorrect. The correct sequence was: ' + currentSequence.join(' ');
        message.className = 'message incorrect';
    }
    
    updateDisplay();
    document.getElementById('submit-btn').style.display = 'none';
    document.getElementById('next-btn').style.display = 'inline-block';
}

function updateDisplay() {
    document.getElementById('current-round').textContent = currentRound;
    document.getElementById('score').textContent = score;
    document.getElementById('correct').textContent = correctAnswers;
}

function endGame() {
    const duration = (Date.now() - startTime) / 1000;
    const accuracy = (correctAnswers / totalAttempts) * 100;
    
    document.getElementById('final-score').value = score;
    document.getElementById('final-duration').value = duration;
    document.getElementById('final-attempts').value = totalAttempts;
    document.getElementById('final-accuracy').value = accuracy;
    
    document.getElementById('result-form').submit();
}
