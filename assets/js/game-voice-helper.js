/**
 * Game Voice Assistant Helper
 * Adds voice assistance specifically for game pages
 * Depends on voice-assistant.js being loaded first
 */

document.addEventListener('DOMContentLoaded', function() {
    if (!window.voiceAssistant) return;

    // Game instructions database
    const gameInstructions = {
        'memory': {
            name: 'Memory Match',
            instructions: 'Remember the sequence of numbers shown, then type them back in order. You will have several rounds to test your memory.'
        },
        'reaction': {
            name: 'Reaction Time',
            instructions: 'Wait for the green box to appear, then click it as fast as you can. Your reaction time will be measured.'
        },
        'card_flip': {
            name: 'Card Flip Memory',
            instructions: 'Click cards to reveal them. Find matching pairs by remembering where each card is located.'
        },
        'number_memory': {
            name: 'Number Memory',
            instructions: 'Remember the number sequence shown, then type it back. The sequences will get longer as you progress.'
        },
        'chimp_test': {
            name: 'Chimp Test',
            instructions: 'Numbers will briefly appear on screen. Click them in ascending order from lowest to highest.'
        },
        'tetris': {
            name: 'Tetris',
            instructions: 'Use arrow keys to move and rotate falling blocks. Complete horizontal lines to score points. Left and right arrows move the piece, up arrow rotates, down arrow drops faster.'
        },
        'gem_match': {
            name: 'Gem Match',
            instructions: 'Click adjacent gems to swap them. Match three or more gems of the same color to score points. You have 60 seconds.'
        },
        'attention': {
            name: 'Attention Focus',
            instructions: 'Stay focused and follow the instructions that appear on screen. Test your sustained attention.'
        },
        'puzzle': {
            name: 'Puzzle Solver',
            instructions: 'Solve the puzzle by arranging the pieces correctly. Use logic and spatial reasoning.'
        }
    };

    // Detect which game we're on
    const currentPath = window.location.pathname;
    let currentGame = null;

    for (const gameId in gameInstructions) {
        if (currentPath.includes(gameId)) {
            currentGame = gameId;
            break;
        }
    }

    // If we're on a game page and voice is enabled, announce instructions
    if (currentGame && gameInstructions[currentGame]) {
        const game = gameInstructions[currentGame];
        setTimeout(() => {
            if (window.voiceAssistant.enabled) {
                window.voiceAssistant.speakGameInstructions(game.name, game.instructions);
            }
        }, 800); // Small delay to let page settle
    }

    // Add specific voice assistance for game buttons
    const startButtons = document.querySelectorAll('.start-btn, #start-btn, .btn-start, [class*="start"]');
    startButtons.forEach(btn => {
        btn.setAttribute('data-speak', 'Start game button. Click to begin playing.');
    });

    const submitButtons = document.querySelectorAll('.submit-btn, #submit-btn');
    submitButtons.forEach(btn => {
        btn.setAttribute('data-speak', 'Submit your answer.');
    });

    const nextButtons = document.querySelectorAll('.next-btn, #next-btn');
    nextButtons.forEach(btn => {
        btn.setAttribute('data-speak', 'Next round button. Click to continue.');
    });

    // Help button on game pages
    const helpButtons = document.querySelectorAll('.help-btn, #help-btn');
    helpButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            if (currentGame && gameInstructions[currentGame]) {
                window.voiceAssistant.speak(gameInstructions[currentGame].instructions);
            }
        });
    });

    // Add voice labels to game cards on games.php
    const gameCards = document.querySelectorAll('.game-card');
    gameCards.forEach(card => {
        const icon = card.querySelector('.game-icon');
        const title = card.querySelector('h3');
        const description = card.querySelector('p');
        
        if (title && description) {
            const gameText = `${title.textContent}. ${description.textContent}`;
            card.setAttribute('data-speak', gameText);
        }
    });

    // Add voice labels to difficulty buttons
    const difficultyButtons = document.querySelectorAll('.difficulty-btn, .btn-easy, .btn-medium, .btn-hard');
    difficultyButtons.forEach(btn => {
        if (btn.textContent.includes('Easy')) {
            btn.setAttribute('data-speak', 'Easy difficulty. Suitable for beginners.');
        } else if (btn.textContent.includes('Medium')) {
            btn.setAttribute('data-speak', 'Medium difficulty. A balanced challenge.');
        } else if (btn.textContent.includes('Hard')) {
            btn.setAttribute('data-speak', 'Hard difficulty. For experienced players.');
        }
    });

    // Announce score updates
    const scoreElement = document.querySelector('#score, .score, [class*="score"]');
    if (scoreElement) {
        const observer = new MutationObserver((mutations) => {
            if (window.voiceAssistant.enabled) {
                const newScore = scoreElement.textContent;
                if (newScore && !isNaN(newScore)) {
                    window.voiceAssistant.speak(`Score: ${newScore}`, false);
                }
            }
        });
        observer.observe(scoreElement, { childList: true, characterData: true, subtree: true });
    }
});
