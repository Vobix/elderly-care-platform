/**
 * Tetris - Singleplayer and Hot-Seat Multiplayer
 * Survival Mode for Multiplayer
 */

// Game constants
const COLS = 10;
const ROWS = 20;
const BLOCK_SIZE = 30;

// Tetromino shapes
const SHAPES = [
    [[1,1,1,1]], // I
    [[1,1],[1,1]], // O
    [[0,1,0],[1,1,1]], // T
    [[1,0,0],[1,1,1]], // J
    [[0,0,1],[1,1,1]], // L
    [[0,1,1],[1,1,0]], // S
    [[1,1,0],[0,1,1]]  // Z
];

const COLORS = ['#00F0F0', '#F0F000', '#A000F0', '#0000F0', '#F0A000', '#00F000', '#F00000'];

// Game mode
let isMultiplayer = false;
let gameStartTime = null;

// Player 1 (Singleplayer or Multiplayer)
let player1 = {
    canvas: null,
    ctx: null,
    nextCanvas: null,
    nextCtx: null,
    grid: null,
    currentPiece: null,
    nextPiece: null,
    score: 0,
    lines: 0,
    level: 1,
    gameOver: false,
    dropCounter: 0,
    dropInterval: 1000,
    lastTime: 0,
    clearingLines: false,
    linesToClear: [],
    clearAnimationProgress: 0
};

// Player 2 (Multiplayer only)
let player2 = {
    canvas: null,
    ctx: null,
    nextCanvas: null,
    nextCtx: null,
    grid: null,
    currentPiece: null,
    nextPiece: null,
    score: 0,
    lines: 0,
    level: 1,
    gameOver: false,
    dropCounter: 0,
    dropInterval: 1000,
    lastTime: 0,
    clearingLines: false,
    linesToClear: [],
    clearAnimationProgress: 0
};

// Initialize singleplayer
function initSingleplayer() {
    isMultiplayer = false;
    player1.canvas = document.getElementById('gameCanvas');
    player1.ctx = player1.canvas.getContext('2d');
    player1.nextCanvas = document.getElementById('nextPieceCanvas');
    player1.nextCtx = player1.nextCanvas.getContext('2d');
    
    resetPlayer(player1);
    gameStartTime = Date.now();
    requestAnimationFrame(gameLoop);
}

// Initialize multiplayer
function initMultiplayer() {
    isMultiplayer = true;
    
    player1.canvas = document.getElementById('gameCanvas1');
    player1.ctx = player1.canvas.getContext('2d');
    player1.nextCanvas = document.getElementById('nextCanvas1');
    player1.nextCtx = player1.nextCanvas.getContext('2d');
    
    player2.canvas = document.getElementById('gameCanvas2');
    player2.ctx = player2.canvas.getContext('2d');
    player2.nextCanvas = document.getElementById('nextCanvas2');
    player2.nextCtx = player2.nextCanvas.getContext('2d');
    
    resetPlayer(player1);
    resetPlayer(player2);
    gameStartTime = Date.now();
    requestAnimationFrame(gameLoop);
}

// Reset player state
function resetPlayer(player) {
    player.grid = createGrid();
    player.score = 0;
    player.lines = 0;
    player.level = 1;
    player.gameOver = false;
    player.dropCounter = 0;
    player.dropInterval = 1000;
    player.lastTime = 0;
    player.nextPiece = randomPiece();
    player.currentPiece = randomPiece();
}

// Create empty grid
function createGrid() {
    return Array(ROWS).fill().map(() => Array(COLS).fill(0));
}

// Random piece
function randomPiece() {
    const shapeIndex = Math.floor(Math.random() * SHAPES.length);
    return {
        shape: SHAPES[shapeIndex],
        color: COLORS[shapeIndex],
        x: Math.floor((COLS - SHAPES[shapeIndex][0].length) / 2),
        y: 0
    };
}

// Game loop
function gameLoop(time = 0) {
    if (!isMultiplayer) {
        updatePlayer(player1, time, 1);
        drawPlayer(player1, 1);
    } else {
        const bothOver = player1.gameOver && player2.gameOver;
        
        if (!bothOver) {
            if (!player1.gameOver) {
                updatePlayer(player1, time, 1);
                drawPlayer(player1, 1);
            }
            if (!player2.gameOver) {
                updatePlayer(player2, time, 2);
                drawPlayer(player2, 2);
            }
        } else {
            // Both players defeated - shouldn't happen in survival mode
            return;
        }
    }
    
    requestAnimationFrame(gameLoop);
}

// Update player
function updatePlayer(player, time, playerNum) {
    if (player.gameOver) return;
    
    const deltaTime = time - player.lastTime;
    player.lastTime = time;
    
    if (player.clearingLines) {
        player.clearAnimationProgress += 0.025;
        if (player.clearAnimationProgress >= 1) {
            completeClearLines(player, playerNum);
        }
        return;
    }
    
    player.dropCounter += deltaTime;
    if (player.dropCounter > player.dropInterval) {
        player.dropCounter = 0;
        if (!moveDown(player)) {
            merge(player);
            checkLines(player, playerNum);
            player.currentPiece = player.nextPiece;
            player.nextPiece = randomPiece();
            
            if (collision(player, player.currentPiece)) {
                playerGameOver(player, playerNum);
            }
        }
    }
}

// Draw player
function drawPlayer(player, playerNum) {
    player.ctx.fillStyle = '#000';
    player.ctx.fillRect(0, 0, player.canvas.width, player.canvas.height);
    
    drawGrid(player);
    
    if (player.clearingLines) {
        drawClearAnimation(player);
    }
    
    if (!player.gameOver && player.currentPiece) {
        drawGhost(player);
        drawPiece(player.ctx, player.currentPiece);
    }
    
    drawNextPiece(player);
}

// Draw grid
function drawGrid(player) {
    for (let row = 0; row < ROWS; row++) {
        for (let col = 0; col < COLS; col++) {
            if (player.grid[row][col]) {
                player.ctx.fillStyle = player.grid[row][col];
                player.ctx.fillRect(col * BLOCK_SIZE, row * BLOCK_SIZE, BLOCK_SIZE - 1, BLOCK_SIZE - 1);
            }
        }
    }
}

// Draw piece
function drawPiece(ctx, piece) {
    ctx.fillStyle = piece.color;
    piece.shape.forEach((row, dy) => {
        row.forEach((value, dx) => {
            if (value) {
                ctx.fillRect(
                    (piece.x + dx) * BLOCK_SIZE,
                    (piece.y + dy) * BLOCK_SIZE,
                    BLOCK_SIZE - 1,
                    BLOCK_SIZE - 1
                );
            }
        });
    });
}

// Draw ghost piece
function drawGhost(player) {
    const ghost = { ...player.currentPiece };
    while (!collision(player, { ...ghost, y: ghost.y + 1 })) {
        ghost.y++;
    }
    
    player.ctx.save();
    ghost.shape.forEach((row, dy) => {
        row.forEach((value, dx) => {
            if (value) {
                const x = (ghost.x + dx) * BLOCK_SIZE;
                const y = (ghost.y + dy) * BLOCK_SIZE;
                
                // Fill with semi-transparent color
                player.ctx.globalAlpha = 0.2;
                player.ctx.fillStyle = player.currentPiece.color;
                player.ctx.fillRect(x, y, BLOCK_SIZE, BLOCK_SIZE);
                
                // Thick dashed border for better visibility
                player.ctx.globalAlpha = 0.7;
                player.ctx.strokeStyle = player.currentPiece.color;
                player.ctx.lineWidth = 3;
                player.ctx.setLineDash([5, 3]);
                player.ctx.strokeRect(x + 1, y + 1, BLOCK_SIZE - 2, BLOCK_SIZE - 2);
                player.ctx.setLineDash([]);
            }
        });
    });
    player.ctx.restore();
}

// Draw next piece
function drawNextPiece(player) {
    player.nextCtx.fillStyle = '#fff';
    player.nextCtx.fillRect(0, 0, player.nextCanvas.width, player.nextCanvas.height);
    
    if (player.nextPiece) {
        const size = isMultiplayer ? 30 : 30;
        const offsetX = (player.nextCanvas.width - player.nextPiece.shape[0].length * size) / 2;
        const offsetY = (player.nextCanvas.height - player.nextPiece.shape.length * size) / 2;
        
        player.nextCtx.fillStyle = player.nextPiece.color;
        player.nextPiece.shape.forEach((row, dy) => {
            row.forEach((value, dx) => {
                if (value) {
                    player.nextCtx.fillRect(
                        offsetX + dx * size,
                        offsetY + dy * size,
                        size - 1,
                        size - 1
                    );
                }
            });
        });
    }
}

// Draw line clear animation
function drawClearAnimation(player) {
    const progress = player.clearAnimationProgress;
    
    player.linesToClear.forEach(row => {
        if (progress < 0.3) {
            const flash = Math.sin(progress * 40) * 0.5 + 0.5;
            player.ctx.fillStyle = `rgba(255, 255, 255, ${flash})`;
        } else {
            const fade = 1 - ((progress - 0.3) / 0.7);
            const expand = 1 + ((progress - 0.3) / 0.7) * 0.5;
            
            player.ctx.save();
            player.ctx.translate(player.canvas.width / 2, row * BLOCK_SIZE + BLOCK_SIZE / 2);
            player.ctx.scale(expand, 1);
            player.ctx.translate(-player.canvas.width / 2, -(row * BLOCK_SIZE + BLOCK_SIZE / 2));
            
            player.ctx.fillStyle = `rgba(255, 255, 255, ${fade})`;
        }
        
        player.ctx.fillRect(0, row * BLOCK_SIZE, player.canvas.width, BLOCK_SIZE);
        
        if (progress >= 0.3) {
            player.ctx.restore();
        }
    });
}

// Check for collision
function collision(player, piece = null) {
    const checkPiece = piece || player.currentPiece;
    
    for (let dy = 0; dy < checkPiece.shape.length; dy++) {
        for (let dx = 0; dx < checkPiece.shape[dy].length; dx++) {
            if (checkPiece.shape[dy][dx]) {
                const newX = checkPiece.x + dx;
                const newY = checkPiece.y + dy;
                
                if (newX < 0 || newX >= COLS || newY >= ROWS) {
                    return true;
                }
                if (newY >= 0 && player.grid[newY][newX]) {
                    return true;
                }
            }
        }
    }
    return false;
}

// Move piece down
function moveDown(player) {
    player.currentPiece.y++;
    if (collision(player)) {
        player.currentPiece.y--;
        return false;
    }
    return true;
}

// Merge piece into grid
function merge(player) {
    player.currentPiece.shape.forEach((row, dy) => {
        row.forEach((value, dx) => {
            if (value) {
                const gridY = player.currentPiece.y + dy;
                const gridX = player.currentPiece.x + dx;
                if (gridY >= 0) {
                    player.grid[gridY][gridX] = player.currentPiece.color;
                }
            }
        });
    });
}

// Check for complete lines
function checkLines(player, playerNum) {
    const linesToClear = [];
    
    for (let row = ROWS - 1; row >= 0; row--) {
        if (player.grid[row].every(cell => cell !== 0)) {
            linesToClear.push(row);
        }
    }
    
    if (linesToClear.length > 0) {
        player.clearingLines = true;
        player.linesToClear = linesToClear;
        player.clearAnimationProgress = 0;
        gameSounds.playLineClear(linesToClear.length);
    }
}

// Complete line clearing
function completeClearLines(player, playerNum) {
    player.linesToClear.sort((a, b) => a - b);
    
    for (const row of player.linesToClear) {
        player.grid.splice(row, 1);
        player.grid.unshift(Array(COLS).fill(0));
    }
    
    const linesCleared = player.linesToClear.length;
    player.lines += linesCleared;
    player.score += [0, 100, 300, 500, 800][linesCleared] * player.level;
    
    const newLevel = Math.floor(player.lines / 10) + 1;
    if (newLevel > player.level) {
        player.level = newLevel;
        gameSounds.playLevelUp();
    }
    
    // Update drop speed based on score (5% faster per 100 points)
    const scoreMultiplier = Math.floor(player.score / 100);
    const speedReduction = scoreMultiplier * 0.05; // 5% per 100 points
    const baseSpeed = 1000 - (player.level - 1) * 100;
    player.dropInterval = Math.max(100, baseSpeed * (1 - speedReduction));
    
    player.clearingLines = false;
    player.linesToClear = [];
    
    updateUI(playerNum);
}

// Update UI
function updateUI(playerNum) {
    const player = playerNum === 1 ? player1 : player2;
    const suffix = isMultiplayer ? playerNum : '';
    
    document.getElementById(`scoreDisplay${suffix}`).textContent = player.score;
    document.getElementById(`linesDisplay${suffix}`).textContent = player.lines;
    document.getElementById(`levelDisplay${suffix}`).textContent = player.level;
}

// Player game over
function playerGameOver(player, playerNum) {
    player.gameOver = true;
    gameSounds.playGameOver();
    
    if (isMultiplayer) {
        // Mark player as defeated
        const playerArea = document.getElementById(`player${playerNum}Area`);
        playerArea.classList.add('defeated');
        
        // Check if other player won
        const otherPlayer = playerNum === 1 ? player2 : player1;
        if (!otherPlayer.gameOver) {
            // Other player wins!
            setTimeout(() => {
                endMultiplayerGame(playerNum === 1 ? 2 : 1);
            }, 1000);
        }
    } else {
        // Singleplayer - redirect to results
        setTimeout(() => {
            submitScore(player1.score, player1.lines, player1.level);
        }, 1000);
    }
}

// End multiplayer game
function endMultiplayerGame(winnerNum) {
    // Redirect to multiplayer result page with both players' stats
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/games/tetris_multiplayer_result.php';
    
    const fields = {
        winner: winnerNum,
        p1_score: player1.score,
        p1_lines: player1.lines,
        p1_level: player1.level,
        p2_score: player2.score,
        p2_lines: player2.lines,
        p2_level: player2.level,
        duration: Math.floor((Date.now() - gameStartTime) / 1000)
    };
    
    for (const [name, value] of Object.entries(fields)) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        form.appendChild(input);
    }
    
    document.body.appendChild(form);
    form.submit();
}

// Submit score
function submitScore(finalScore, finalLines, finalLevel) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/pages/game_result.php';
    
    const fields = {
        game_type: 'tetris',
        score: finalScore,
        duration: Math.floor((Date.now() - gameStartTime) / 1000),
        level_reached: finalLevel,
        max_score: finalScore
    };
    
    for (const [name, value] of Object.entries(fields)) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        form.appendChild(input);
    }
    
    document.body.appendChild(form);
    form.submit();
}

// Keyboard controls
document.addEventListener('keydown', (e) => {
    if (!isMultiplayer) {
        // Singleplayer controls (arrow keys + space)
        if (['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', ' '].includes(e.key)) {
            e.preventDefault();
            handlePlayerInput(player1, e.key, 1);
        }
    } else {
        // Multiplayer controls
        // Player 1: WASD + Shift
        if (['a', 'd', 'w', 's', 'Shift'].includes(e.key)) {
            e.preventDefault();
            handlePlayerInput(player1, e.key, 1);
        }
        // Player 2: Arrow keys + Space
        if (['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', ' '].includes(e.key)) {
            e.preventDefault();
            handlePlayerInput(player2, e.key, 2);
        }
    }
});

// Handle player input
function handlePlayerInput(player, key, playerNum) {
    if (player.gameOver || player.clearingLines) return;
    
    const controls = isMultiplayer ? 
        (playerNum === 1 ? 
            { left: 'a', right: 'd', rotate: 'w', down: 's', drop: 'Shift' } :
            { left: 'ArrowLeft', right: 'ArrowRight', rotate: 'ArrowUp', down: 'ArrowDown', drop: ' ' }
        ) :
        { left: 'ArrowLeft', right: 'ArrowRight', rotate: 'ArrowUp', down: 'ArrowDown', drop: ' ' };
    
    if (key === controls.left) {
        player.currentPiece.x--;
        if (collision(player)) player.currentPiece.x++;
        else gameSounds.playMove();
    } else if (key === controls.right) {
        player.currentPiece.x++;
        if (collision(player)) player.currentPiece.x--;
        else gameSounds.playMove();
    } else if (key === controls.rotate) {
        rotatePiece(player);
    } else if (key === controls.down) {
        moveDown(player);
        updateUI(playerNum);
    } else if (key === controls.drop) {
        hardDrop(player, playerNum);
    }
}

// Rotate piece
function rotatePiece(player) {
    const rotated = player.currentPiece.shape[0].map((_, i) =>
        player.currentPiece.shape.map(row => row[i]).reverse()
    );
    
    const previousShape = player.currentPiece.shape;
    player.currentPiece.shape = rotated;
    
    if (collision(player)) {
        player.currentPiece.shape = previousShape;
    } else {
        gameSounds.playRotate();
    }
}

// Hard drop
function hardDrop(player, playerNum) {
    while (!collision(player, { ...player.currentPiece, y: player.currentPiece.y + 1 })) {
        player.currentPiece.y++;
    }
    
    updateUI(playerNum);
    gameSounds.playDrop();
    
    merge(player);
    checkLines(player, playerNum);
    player.currentPiece = player.nextPiece;
    player.nextPiece = randomPiece();
    
    if (collision(player, player.currentPiece)) {
        playerGameOver(player, playerNum);
    }
}
