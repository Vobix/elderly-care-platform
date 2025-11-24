// Number Memory Game Logic

let currentLevel = 1;
let bestScore = 0;
let currentNumber = '';
let numDigits;
let gameStartTime;
let totalAttempts = 0;
let correctAnswers = 0;

function startGame() {
    currentLevel = 1;
    bestScore = 0;
    totalAttempts = 0;
    correctAnswers = 0;
    gameStartTime = Date.now();
    numDigits = CONFIG.starting_digits;
    
    document.getElementById('start-btn').style.display = 'none';
    document.getElementById('message').textContent = '';
    document.getElementById('digits').textContent = numDigits;
    
    nextLevel();
}

function nextLevel() {
    document.getElementById('next-btn').style.display = 'none';
    document.getElementById('input-area').style.display = 'none';
    document.getElementById('number-input').value = '';
    document.getElementById('message').textContent = 'Memorize this number...';
    document.getElementById('message').className = 'message';
    
    // Generate random number with current digit count
    currentNumber = generateNumber(numDigits);
    
    // Display the number
    document.getElementById('number-display').textContent = currentNumber;
    document.getElementById('level').textContent = currentLevel;
    document.getElementById('digits').textContent = numDigits;
    
    // Calculate display time (base time + time per digit)
    const displayTime = CONFIG.display_time + (numDigits * CONFIG.per_digit_time);
    
    // Hide number and show input after display time
    setTimeout(() => {
        document.getElementById('number-display').textContent = '';
        document.getElementById('input-area').style.display = 'block';
        document.getElementById('number-input').focus();
        document.getElementById('message').textContent = 'What was the number?';
    }, displayTime);
}

function generateNumber(digits) {
    let number = '';
    // First digit should not be 0
    number += Math.floor(Math.random() * 9) + 1;
    
    // Rest of the digits
    for (let i = 1; i < digits; i++) {
        number += Math.floor(Math.random() * 10);
    }
    
    return number;
}

function submitAnswer() {
    const userAnswer = document.getElementById('number-input').value.trim();
    totalAttempts++;
    
    if (userAnswer === currentNumber) {
        // Correct!
        correctAnswers++;
        currentLevel++;
        numDigits++;
        
        if (numDigits > bestScore) {
            bestScore = numDigits;
            document.getElementById('best').textContent = bestScore;
        }
        
        document.getElementById('message').textContent = `✅ Correct! Moving to ${numDigits} digits...`;
        document.getElementById('message').className = 'message correct';
        document.getElementById('next-btn').style.display = 'inline-block';
        document.getElementById('submit-btn').disabled = true;
    } else {
        // Wrong - game over
        document.getElementById('message').textContent = `❌ Wrong! The number was ${currentNumber}`;
        document.getElementById('message').className = 'message incorrect';
        document.getElementById('submit-btn').disabled = true;
        
        setTimeout(() => endGame(), 2000);
    }
}

// Allow Enter key to submit
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('number-input')?.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            submitAnswer();
        }
    });
});

function endGame() {
    const duration = (Date.now() - gameStartTime) / 1000;
    const score = (numDigits - 1) * 100; // Score based on max digits reached
    const accuracy = (correctAnswers / totalAttempts) * 100;
    
    document.getElementById('final-score').value = score;
    document.getElementById('final-duration').value = duration;
    document.getElementById('final-attempts').value = totalAttempts;
    document.getElementById('final-accuracy').value = accuracy;
    
    document.getElementById('result-form').submit();
}
