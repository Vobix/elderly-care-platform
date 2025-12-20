/**
 * Tetris Game Logic
 * Classic block-stacking puzzle game
 */

const COLS = 10;
const ROWS = 20;
const BLOCK_SIZE = 30;
const COLORS = [
    null,
    '#00FFFF', // I - Cyan
    '#0000FF', // J - Blue
    '#FFA500', // L - Orange
    '#FFFF00', // O - Yellow
    '#00FF00', // S - Green
    '#FF00FF', // T - Purple
    '#FF0000'  // Z - Red
];

// Tetromino shapes
const SHAPES = [
    [], // Empty
    [[1,1,1,1]], // I
    [[1,0,0],[1,1,1]], // J
    [[0,0,1],[1,1,1]], // L
    [[1,1],[1,1]], // O
    [[0,1,1],[1,1,0]], // S
    [[0,1,0],[1,1,1]], // T
    [[1,1,0],[0,1,1]]  // Z
];

let canvas, ctx, nextCanvas, nextCtx;
let board = [];
let score = 0;
let lines = 0;
let level = 1;
let currentPiece = null;
let nextPiece = null;
let gameOver = false;
let isPaused = false;
let dropInterval = 1000;
let lastDropTime = 0;
let animationId = null;
let clearingLines = false;
let linesToClear = [];
let clearAnimationProgress = 0;
let gameStartTime;

// Initialize game
function init() {
    canvas = document.getElementById('gameCanvas');
    ctx = canvas.getContext('2d');
    nextCanvas = document.getElementById('nextPieceCanvas');
    nextCtx = nextCanvas.getContext('2d');
    
    // Initialize board
    board = Array.from({ length: ROWS }, () => Array(COLS).fill(0));
    
    // Start game
    score = 0;
    lines = 0;
    level = 1;
    gameOver = false;
    isPaused = false;
    dropInterval = 1000;
    gameStartTime = Date.now();
    
    updateDisplay();
    
    // Create first piece
    currentPiece = createPiece();
    nextPiece = createPiece();
    
    // Draw next piece
    drawNextPiece();
    
    // Start game loop
    lastDropTime = Date.now();
    gameLoop();
    
    // Add keyboard controls
    document.addEventListener('keydown', handleKeyPress);
}

function createPiece() {
    const shapeIndex = Math.floor(Math.random() * (SHAPES.length - 1)) + 1;
    return {
        shape: SHAPES[shapeIndex],
        color: shapeIndex,
        x: Math.floor(COLS / 2) - Math.floor(SHAPES[shapeIndex][0].length / 2),
        y: 0
    };
}

function gameLoop(timestamp) {
    if (gameOver || isPaused) {
        if (!gameOver && isPaused) {
            animationId = requestAnimationFrame(gameLoop);
        }
        return;
    }
    
    const now = Date.now();
    const deltaTime = now - lastDropTime;
    
    if (deltaTime > dropInterval) {
        moveDown();
        lastDropTime = now;
    }
    
    draw();
    animationId = requestAnimationFrame(gameLoop);
}

function draw() {
    // Clear canvas
    ctx.fillStyle = '#000';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    
    // Draw board with line clear animation
    for (let y = 0; y < ROWS; y++) {
        for (let x = 0; x < COLS; x++) {
            if (board[y][x]) {
                // Apply line clear animation
                if (linesToClear.includes(y)) {
                    ctx.save();
                    
                    // Flash effect - alternating bright white
                    if (clearAnimationProgress < 0.3) {
                        const flash = Math.sin(clearAnimationProgress * 50) * 0.5 + 0.5;
                        ctx.globalAlpha = 1;
                        ctx.fillStyle = flash > 0.5 ? '#FFFFFF' : COLORS[board[y][x]];
                        ctx.fillRect(x * BLOCK_SIZE, y * BLOCK_SIZE, BLOCK_SIZE, BLOCK_SIZE);
                    } else {
                        // Fade out with explosion effect
                        ctx.globalAlpha = 1 - ((clearAnimationProgress - 0.3) / 0.7);
                        drawBlock(ctx, x, y, board[y][x]);
                        
                        // Expanding white flash
                        const expansionProgress = (clearAnimationProgress - 0.3) / 0.7;
                        const brightness = 1 - expansionProgress;
                        ctx.fillStyle = `rgba(255, 255, 255, ${brightness * 0.8})`;
                        const expansion = expansionProgress * BLOCK_SIZE * 0.3;
                        ctx.fillRect(
                            x * BLOCK_SIZE - expansion / 2, 
                            y * BLOCK_SIZE - expansion / 2, 
                            BLOCK_SIZE + expansion, 
                            BLOCK_SIZE + expansion
                        );
                    }
                    
                    ctx.restore();
                } else {
                    drawBlock(ctx, x, y, board[y][x]);
                }
            }
        }
    }
    
    // Draw ghost piece (where piece will land)
    if (currentPiece && !clearingLines) {
        drawGhostPiece();
    }
    
    // Draw current piece
    if (currentPiece && !clearingLines) {
        drawPiece(ctx, currentPiece);
    }
    
    // Draw grid
    ctx.strokeStyle = '#333';
    ctx.lineWidth = 1;
    for (let x = 0; x <= COLS; x++) {
        ctx.beginPath();
        ctx.moveTo(x * BLOCK_SIZE, 0);
        ctx.lineTo(x * BLOCK_SIZE, ROWS * BLOCK_SIZE);
        ctx.stroke();
    }
    for (let y = 0; y <= ROWS; y++) {
        ctx.beginPath();
        ctx.moveTo(0, y * BLOCK_SIZE);
        ctx.lineTo(COLS * BLOCK_SIZE, y * BLOCK_SIZE);
        ctx.stroke();
    }
}

function drawBlock(context, x, y, colorIndex) {
    const px = x * BLOCK_SIZE;
    const py = y * BLOCK_SIZE;
    
    context.fillStyle = COLORS[colorIndex];
    context.fillRect(px + 1, py + 1, BLOCK_SIZE - 2, BLOCK_SIZE - 2);
    
    // Add highlight
    context.fillStyle = 'rgba(255,255,255,0.3)';
    context.fillRect(px + 1, py + 1, BLOCK_SIZE - 2, BLOCK_SIZE / 4);
}

function drawPiece(context, piece) {
    piece.shape.forEach((row, dy) => {
        row.forEach((value, dx) => {
            if (value) {
                drawBlock(context, piece.x + dx, piece.y + dy, piece.color);
            }
        });
    });
}

function drawNextPiece() {
    nextCtx.fillStyle = '#f0f0f0';
    nextCtx.fillRect(0, 0, nextCanvas.width, nextCanvas.height);
    
    if (nextPiece) {
        const offsetX = (4 - nextPiece.shape[0].length) / 2;
        const offsetY = (4 - nextPiece.shape.length) / 2;
        
        nextPiece.shape.forEach((row, dy) => {
            row.forEach((value, dx) => {
                if (value) {
                    const px = (offsetX + dx) * BLOCK_SIZE;
                    const py = (offsetY + dy) * BLOCK_SIZE;
                    nextCtx.fillStyle = COLORS[nextPiece.color];
                    nextCtx.fillRect(px + 1, py + 1, BLOCK_SIZE - 2, BLOCK_SIZE - 2);
                }
            });
        });
    }
}

function drawGhostPiece() {
    const ghost = {...currentPiece, shape: currentPiece.shape};
    
    // Find landing position
    while (!collision(ghost)) {
        ghost.y++;
    }
    ghost.y--;
    
    // Draw ghost with enhanced visibility
    ctx.save();
    ghost.shape.forEach((row, dy) => {
        row.forEach((value, dx) => {
            if (value) {
                const x = (ghost.x + dx) * BLOCK_SIZE;
                const y = (ghost.y + dy) * BLOCK_SIZE;
                
                // Fill with semi-transparent color
                ctx.globalAlpha = 0.2;
                ctx.fillStyle = COLORS[ghost.color];
                ctx.fillRect(x, y, BLOCK_SIZE, BLOCK_SIZE);
                
                // Thick dashed border for better visibility
                ctx.globalAlpha = 0.7;
                ctx.strokeStyle = COLORS[ghost.color];
                ctx.lineWidth = 3;
                ctx.setLineDash([5, 3]);
                ctx.strokeRect(x + 1, y + 1, BLOCK_SIZE - 2, BLOCK_SIZE - 2);
                ctx.setLineDash([]);
            }
        });
    });
    ctx.restore();
}

function collision(piece = currentPiece) {
    for (let dy = 0; dy < piece.shape.length; dy++) {
        for (let dx = 0; dx < piece.shape[dy].length; dx++) {
            if (piece.shape[dy][dx]) {
                const newX = piece.x + dx;
                const newY = piece.y + dy;
                
                if (newX < 0 || newX >= COLS || newY >= ROWS) {
                    return true;
                }
                
                if (newY >= 0 && board[newY][newX]) {
                    return true;
                }
            }
        }
    }
    return false;
}

function handleKeyPress(e) {
    if (gameOver) return;
    
    if (e.key === 'p' || e.key === 'P') {
        togglePause();
        return;
    }
    
    if (isPaused) return;
    
    switch(e.key) {
        case 'ArrowLeft':
            e.preventDefault();
            moveLeft();
            break;
        case 'ArrowRight':
            e.preventDefault();
            moveRight();
            break;
        case 'ArrowDown':
            e.preventDefault();
            moveDown();
            break;
        case 'ArrowUp':
            e.preventDefault();
            rotate();
            break;
        case ' ':
            e.preventDefault();
            hardDrop();
            break;
    }
}

function moveLeft() {
    currentPiece.x--;
    if (collision()) {
        currentPiece.x++;
    } else {
        gameSounds.playMove();
    }
}

function moveRight() {
    currentPiece.x++;
    if (collision()) {
        currentPiece.x--;
    } else {
        gameSounds.playMove();
    }
}

function moveDown() {
    currentPiece.y++;
    if (collision()) {
        currentPiece.y--;
        lockPiece();
        clearLines();
        spawnPiece();
    }
}

function rotate() {
    const rotated = currentPiece.shape[0].map((_, i) =>
        currentPiece.shape.map(row => row[i]).reverse()
    );
    
    const previousShape = currentPiece.shape;
    currentPiece.shape = rotated;
    
    if (collision()) {
        currentPiece.shape = previousShape;
    } else {
        gameSounds.playRotate();
    }
}

function hardDrop() {
    while (!collision()) {
        currentPiece.y++;
    }
    currentPiece.y--;
    gameSounds.playDrop();
    lockPiece();
    clearLines();
    spawnPiece();
    updateDisplay();
}

function lockPiece() {
    currentPiece.shape.forEach((row, dy) => {
        row.forEach((value, dx) => {
            if (value) {
                const y = currentPiece.y + dy;
                const x = currentPiece.x + dx;
                if (y >= 0) {
                    board[y][x] = currentPiece.color;
                }
            }
        });
    });
}

function clearLines() {
    linesToClear = [];
    
    for (let y = 0; y < ROWS; y++) {
        if (board[y].every(cell => cell !== 0)) {
            linesToClear.push(y);
        }
    }
    
    if (linesToClear.length > 0) {
        clearingLines = true;
        clearAnimationProgress = 0;
        
        // Play sound based on number of lines
        gameSounds.playLineClear(linesToClear.length);
        
        // Animate line clearing with dramatic effect
        const animateClear = () => {
            clearAnimationProgress += 0.025; // Slower animation (was 0.05)
            
            if (clearAnimationProgress >= 1) {
                // Remove cleared lines
                linesToClear.sort((a, b) => b - a); // Sort descending
                linesToClear.forEach(y => {
                    board.splice(y, 1);
                    board.unshift(Array(COLS).fill(0));
                });
                
                const linesCleared = linesToClear.length;
                lines += linesCleared;
                
                // Score calculation (Tetris scoring)
                const lineScores = [0, 100, 300, 500, 800];
                score += lineScores[linesCleared] * level;
                
                // Level up every 10 lines
                const newLevel = Math.floor(lines / 10) + 1;
                if (newLevel > level) {
                    level = newLevel;
                    gameSounds.playLevelUp();
                }
                
                // Update drop speed based on score (5% faster per 100 points)
                const scoreMultiplier = Math.floor(score / 100);
                const speedReduction = scoreMultiplier * 0.05; // 5% per 100 points
                const baseSpeed = 1000 - (level - 1) * 100;
                dropInterval = Math.max(100, baseSpeed * (1 - speedReduction));
                
                updateDisplay();
                clearingLines = false;
                linesToClear = [];
            } else {
                requestAnimationFrame(animateClear);
            }
        };
        
        animateClear();
    }
}

function spawnPiece() {
    currentPiece = nextPiece;
    nextPiece = createPiece();
    drawNextPiece();
    
    if (collision()) {
        endGame();
    }
}

function togglePause() {
    isPaused = !isPaused;
    if (!isPaused) {
        lastDropTime = Date.now();
        gameLoop();
    }
}

function endGame() {
    gameOver = true;
    cancelAnimationFrame(animationId);
    
    gameSounds.playGameOver();
    
    // Save score and redirect to result page after sound plays
    setTimeout(() => {
        saveGameScoreAndRedirect();
    }, 1000);
}

function restartGame() {
    document.getElementById('gameOverOverlay').style.display = 'none';
    cancelAnimationFrame(animationId);
    init();
}

function updateDisplay() {
    document.getElementById('scoreDisplay').textContent = score;
    document.getElementById('linesDisplay').textContent = lines;
    document.getElementById('levelDisplay').textContent = level;
}

function saveGameScoreAndRedirect() {
    // Create form to submit to game_result.php
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/pages/game_result.php';
    
    const fields = {
        game_type: 'tetris',
        score: score,
        duration: Math.floor((Date.now() - gameStartTime) / 1000),
        difficulty: 'medium',
        level_reached: level,
        max_score: score
    };
    
    Object.keys(fields).forEach(key => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = fields[key];
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
}

// Start game when page loads
document.addEventListener('DOMContentLoaded', init);
