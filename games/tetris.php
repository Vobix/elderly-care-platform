<?php
/**
 * Tetris Game
 * Classic block-stacking puzzle game with single and multiplayer modes
 */

$page_title = "Tetris";
require_once __DIR__ . '/../pages/account/auth.php';
require_once __DIR__ . '/../_header.php';

$user_id = $_SESSION['user_id'];
?>

<link rel="stylesheet" href="/assets/css/game-common.css">
<link rel="stylesheet" href="/assets/css/tetris.css">

<!-- Mode Selection Screen -->
<div class="mode-selection" id="modeSelection">
    <div class="game-header">
        <h1>🟦 Tetris</h1>
        <p>Choose your game mode</p>
    </div>
    
    <div class="mode-buttons">
        <div class="mode-btn" onclick="selectMode('single')">
            <div class="icon">👤</div>
            <h3>Singleplayer</h3>
            <p>Play solo and compete for high scores</p>
        </div>
        
        <div class="mode-btn" onclick="selectMode('multiplayer')">
            <div class="icon">👥</div>
            <h3>Hot-Seat Multiplayer</h3>
            <p>Survival mode - last player standing wins!</p>
        </div>
    </div>
</div>

<!-- Controls Modal for Multiplayer -->
<div class="controls-modal" id="controlsModal">
    <div class="controls-content">
        <h2 style="text-align: center;">🎮 Multiplayer Controls</h2>
        <p style="text-align: center; color: #666;">Survival Mode: Last player standing wins!</p>
        
        <div class="player-controls">
            <div class="player-section p1">
                <h3 style="text-align: center; color: #2196F3;">Player 1 (Blue)</h3>
                <div class="control-row">
                    <span>Move Left:</span>
                    <span class="key-display">A</span>
                </div>
                <div class="control-row">
                    <span>Move Right:</span>
                    <span class="key-display">D</span>
                </div>
                <div class="control-row">
                    <span>Rotate:</span>
                    <span class="key-display">W</span>
                </div>
                <div class="control-row">
                    <span>Soft Drop:</span>
                    <span class="key-display">S</span>
                </div>
                <div class="control-row">
                    <span>Hard Drop:</span>
                    <span class="key-display">Shift</span>
                </div>
            </div>
            
            <div class="player-section p2">
                <h3 style="text-align: center; color: #F44336;">Player 2 (Red)</h3>
                <div class="control-row">
                    <span>Move Left:</span>
                    <span class="key-display">←</span>
                </div>
                <div class="control-row">
                    <span>Move Right:</span>
                    <span class="key-display">→</span>
                </div>
                <div class="control-row">
                    <span>Rotate:</span>
                    <span class="key-display">↑</span>
                </div>
                <div class="control-row">
                    <span>Soft Drop:</span>
                    <span class="key-display">↓</span>
                </div>
                <div class="control-row">
                    <span>Hard Drop:</span>
                    <span class="key-display">Space</span>
                </div>
            </div>
        </div>
        
        <div style="text-align: center;">
            <button class="start-game-btn" onclick="startMultiplayerGame()">🚀 Start Game!</button>
        </div>
    </div>
</div>

<!-- Game Container -->

<!-- Game Container -->
<div class="tetris-container" id="gameContainer">
    <div class="game-header">
        <h1 id="gameTitle">🟦 Tetris</h1>
    </div>

    <div class="game-layout" id="gameLayout">
        <!-- Singleplayer Layout -->
        <div class="player-area" id="singlePlayerArea">
            <div class="game-area">
                <canvas id="gameCanvas" class="game-board" width="300" height="600"></canvas>
                
                <div class="side-panel">
                    <div class="stats-box">
                        <div class="stat-item">
                            <span class="stat-label">Score</span>
                            <span class="stat-value" id="scoreDisplay">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Lines</span>
                            <span class="stat-value" id="linesDisplay">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Level</span>
                            <span class="stat-value" id="levelDisplay">1</span>
                        </div>
                    </div>
                    
                    <div class="next-piece-box">
                        <h4>Next Piece</h4>
                        <canvas id="nextPieceCanvas" class="next-piece-canvas" width="120" height="120"></canvas>
                    </div>
                    
                    <div class="controls">
                        <h4>Controls</h4>
                        <p><strong>←/→</strong> Move</p>
                        <p><strong>↑</strong> Rotate</p>
                        <p><strong>↓</strong> Soft Drop</p>
                        <p><strong>Space</strong> Hard Drop</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Player 1 Area (Multiplayer) -->
        <div class="player-area p1" id="player1Area" style="display: none;">
            <div class="player-header p1">👤 Player 1</div>
            
            <div class="multiplayer-game-wrapper">
                <div class="player-controls-sidebar p1">
                    <h4>Controls</h4>
                    <div class="control-item">
                        <span class="key">A</span>
                        <span>Move Left</span>
                    </div>
                    <div class="control-item">
                        <span class="key">D</span>
                        <span>Move Right</span>
                    </div>
                    <div class="control-item">
                        <span class="key">W</span>
                        <span>Rotate</span>
                    </div>
                    <div class="control-item">
                        <span class="key">S</span>
                        <span>Soft Drop</span>
                    </div>
                    <div class="control-item">
                        <span class="key">Shift</span>
                        <span>Hard Drop</span>
                    </div>
                </div>
                
                <div class="game-center">
                    <div class="game-board-wrapper">
                        <canvas id="gameCanvas1" class="game-board" width="300" height="600"></canvas>
                    </div>
                </div>
                
                <div class="player-stats-sidebar">
                    <div class="stats-box">
                        <div class="stat-item">
                            <span class="stat-label">Score</span>
                            <span class="stat-value" id="scoreDisplay1">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Lines</span>
                            <span class="stat-value" id="linesDisplay1">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Level</span>
                            <span class="stat-value" id="levelDisplay1">1</span>
                        </div>
                    </div>
                    <div class="next-piece-box">
                        <h4>Next</h4>
                        <canvas id="nextCanvas1" class="next-piece-canvas" width="90" height="90"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Player 2 Area (Multiplayer) -->
        <div class="player-area p2" id="player2Area" style="display: none;">
            <div class="player-header p2">👤 Player 2</div>
            
            <div class="multiplayer-game-wrapper">
                <div class="player-controls-sidebar p2">
                    <h4>Controls</h4>
                    <div class="control-item">
                        <span class="key">←</span>
                        <span>Move Left</span>
                    </div>
                    <div class="control-item">
                        <span class="key">→</span>
                        <span>Move Right</span>
                    </div>
                    <div class="control-item">
                        <span class="key">↑</span>
                        <span>Rotate</span>
                    </div>
                    <div class="control-item">
                        <span class="key">↓</span>
                        <span>Soft Drop</span>
                    </div>
                    <div class="control-item">
                        <span class="key">Space</span>
                        <span>Hard Drop</span>
                    </div>
                </div>
                
                <div class="game-center">
                    <div class="game-board-wrapper">
                        <canvas id="gameCanvas2" class="game-board" width="300" height="600"></canvas>
                    </div>
                </div>
                
                <div class="player-stats-sidebar">
                    <div class="stats-box">
                        <div class="stat-item">
                            <span class="stat-label">Score</span>
                            <span class="stat-value" id="scoreDisplay2">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Lines</span>
                            <span class="stat-value" id="linesDisplay2">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Level</span>
                            <span class="stat-value" id="levelDisplay2">1</span>
                        </div>
                    </div>
                    <div class="next-piece-box">
                        <h4>Next</h4>
                        <canvas id="nextCanvas2" class="next-piece-canvas" width="90" height="90"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="game-instructions">
        <h3>How to Play</h3>
        <ul id="instructionsList">
            <li>🎮 Use controls to move and rotate falling pieces</li>
            <li>📦 Stack pieces to create complete horizontal lines</li>
            <li>💥 Complete lines disappear and award points</li>
            <li>⚡ Clear multiple lines at once for bonus points!</li>
            <li>📈 Game speeds up as you level up</li>
            <li>🚫 Game ends when blocks reach the top</li>
        </ul>
    </div>
</div>

<script>
let gameMode = null;

function selectMode(mode) {
    gameMode = mode;
    document.getElementById('modeSelection').style.display = 'none';
    
    if (mode === 'multiplayer') {
        // Show controls modal
        document.getElementById('controlsModal').style.display = 'flex';
    } else {
        // Start singleplayer immediately
        startSingleplayerGame();
    }
}

function startSingleplayerGame() {
    document.getElementById('gameContainer').classList.add('active');
    document.getElementById('singlePlayerArea').style.display = 'block';
    document.getElementById('player1Area').style.display = 'none';
    document.getElementById('player2Area').style.display = 'none';
    document.getElementById('gameLayout').classList.remove('multiplayer');
    
    // Initialize singleplayer game
    if (typeof initSingleplayer === 'function') {
        initSingleplayer();
    }
}

function startMultiplayerGame() {
    document.getElementById('controlsModal').style.display = 'none';
    document.getElementById('gameContainer').classList.add('active');
    document.getElementById('singlePlayerArea').style.display = 'none';
    document.getElementById('player1Area').style.display = 'block';
    document.getElementById('player2Area').style.display = 'block';
    document.getElementById('gameLayout').classList.add('multiplayer');
    document.getElementById('gameTitle').textContent = '🟦 Tetris - Survival Mode';
    
    // Update instructions for multiplayer
    document.getElementById('instructionsList').innerHTML = `
        <li>👥 Two players compete on the same computer</li>
        <li>🎮 Each player has their own controls (see above)</li>
        <li>⚔️ Survival mode - last player standing wins!</li>
        <li>💥 Clear lines to stay alive</li>
        <li>🏆 When one player loses, the other wins!</li>
    `;
    
    // Initialize multiplayer game
    if (typeof initMultiplayer === 'function') {
        initMultiplayer();
    }
}
</script>

<script src="/assets/js/game-sounds.js"></script>
<script src="/assets/js/tetris_multiplayer.js"></script>

<script src="/assets/js/game-voice-helper.js"></script>
<?php require_once __DIR__ . '/../_footer.php'; ?>
