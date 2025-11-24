<?php
/**
 * Verbal Memory Game
 * Identify whether you've seen words before
 */

require_once __DIR__ . '/../pages/account/auth.php';
require_once __DIR__ . '/../_header.php';

// Single configuration - no difficulty levels
$settings = ['total_words' => 50, 'new_word_probability' => 0.5];
?>

<link rel="stylesheet" href="/assets/css/game-common.css">

<div class="game-container">
    <div class="game-header">
        <h1>📝 Verbal Memory</h1>
        <p>Identify if you've seen each word before</p>
    </div>
    
    <div class="game-info">
        <div class="info-box">
            <div class="label">Score</div>
            <div class="value" id="score">0</div>
        </div>
        <div class="info-box">
            <div class="label">Lives</div>
            <div class="value" id="lives">3</div>
        </div>
        <div class="info-box">
            <div class="label">Words Shown</div>
            <div class="value" id="words-shown">0</div>
        </div>
    </div>
    
    <div class="instructions">
        <h3>How to Play:</h3>
        <ul>
            <li>A word will appear on the screen</li>
            <li>Click "SEEN" if you've seen it before</li>
            <li>Click "NEW" if it's the first time</li>
            <li>You have 3 lives - lose one for each mistake</li>
            <li>Try to get the highest score possible!</li>
        </ul>
    </div>
    
    <div id="game-area" style="margin: 30px auto; text-align: center;">
        <div id="word-display" style="font-size: 56px; font-weight: bold; min-height: 120px; display: flex; align-items: center; justify-content: center; color: #009688; text-transform: uppercase; letter-spacing: 4px;"></div>
        
        <div id="button-area" style="display: none; margin-top: 40px; gap: 20px; justify-content: center;">
            <button class="btn btn-success" onclick="makeChoice(false)" style="font-size: 24px; padding: 20px 60px; min-width: 200px;">🆕 NEW</button>
            <button class="btn btn-primary" onclick="makeChoice(true)" style="font-size: 24px; padding: 20px 60px; min-width: 200px;">👁️ SEEN</button>
        </div>
    </div>
    
    <div class="message" id="message"></div>
    
    <div class="game-buttons">
        <button id="start-btn" class="btn btn-success" onclick="startGame()">🎮 Start Game</button>
    </div>
</div>

<form id="result-form" method="POST" action="/pages/game_result.php" style="display: none;">
    <input type="hidden" name="game_type" value="verbal_memory">
    <input type="hidden" name="difficulty" value="medium">
    <input type="hidden" name="score" id="final-score">
    <input type="hidden" name="duration" id="final-duration">
    <input type="hidden" name="attempts" id="final-attempts">
    <input type="hidden" name="accuracy" id="final-accuracy">
</form>

<script>
// Game configuration from PHP
const CONFIG = <?php echo json_encode($settings); ?>;

// Word pool for the game
const WORD_POOL = [
    'apple', 'banana', 'cherry', 'dragon', 'elephant', 'falcon', 'guitar', 'hammer',
    'island', 'jungle', 'kitten', 'lemon', 'mountain', 'notebook', 'ocean', 'planet',
    'queen', 'river', 'sunset', 'thunder', 'umbrella', 'village', 'window', 'yellow',
    'zebra', 'ancient', 'bridge', 'castle', 'diamond', 'engine', 'forest', 'garden',
    'harbor', 'iceberg', 'jacket', 'kitchen', 'ladder', 'market', 'needle', 'orange',
    'paper', 'quartz', 'rabbit', 'silver', 'temple', 'urban', 'valley', 'wallet',
    'crystal', 'desert', 'eagle', 'flower', 'glacier', 'horizon', 'iron', 'jewel',
    'knight', 'library', 'marble', 'nature', 'orchid', 'palace', 'quarter', 'rocket',
    'shadow', 'tower', 'universe', 'volcano', 'winter', 'youth', 'acoustic', 'balance',
    'compass', 'dancer', 'energy', 'freedom', 'gravity', 'harmony', 'imagine', 'journey',
    'kingdom', 'lantern', 'melody', 'nomad', 'oxygen', 'passport', 'quality', 'rhythm',
    'symphony', 'textile', 'utopia', 'venture', 'wisdom', 'xenon', 'yoga', 'zenith',
    'abstract', 'beacon', 'cipher', 'dynamic', 'elegant', 'fragment', 'genesis', 'habitat',
    'infinite', 'jasmine', 'kinetic', 'legacy', 'mystery', 'nebula', 'orbital', 'paradox',
    'quantum', 'radiant', 'spectrum', 'tangent', 'unified', 'vortex', 'wavelength', 'matrix'
];
</script>
<script src="/assets/js/verbal_memory.js"></script>

<?php require_once __DIR__ . '/../_footer.php'; ?>
