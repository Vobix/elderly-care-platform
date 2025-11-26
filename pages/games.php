<?php
/**
 * Games Menu Page
 * Lists all available cognitive games with difficulty selection
 * 
 * Messages:
 * M1: Please choose a game to play
 * 
 * Constraints:
 * C2: Allowed Difficulty Levels = {Easy, Medium, Hard}
 * C4: Valid Game Launch Actions = Difficulty button OR Play Now button
 */

$page_title = "Cognitive Games";
require_once __DIR__ . '/account/auth.php';
require_once __DIR__ . '/../_header.php';
require_once __DIR__ . '/../database/functions.php';

$user_id = $_SESSION['user_id'];

// M1: Message for game selection
$msg_game_list = "Please choose a game to play";

// Get user's game statistics
$game_stats = getUserGameStats($user_id);
$stats_by_game = [];
foreach ($game_stats as $stat) {
    $stats_by_game[$stat['game_type']] = $stat;
}

// Define available games
$games = [
    [
        'id' => 'memory',
        'name' => 'Memory Match',
        'icon' => '🧠',
        'description' => 'Test your memory by remembering sequences of numbers or images.',
        'benefits' => 'Improves short-term memory and concentration',
        'color' => '#4CAF50'
    ],
    [
        'id' => 'attention',
        'name' => 'Attention Focus',
        'icon' => '👁️',
        'description' => 'Find specific patterns or objects among distractions.',
        'benefits' => 'Enhances attention span and focus',
        'color' => '#2196F3'
    ],
    [
        'id' => 'reaction',
        'name' => 'Reaction Time',
        'icon' => '⚡',
        'description' => 'Click as fast as you can when you see the target.',
        'benefits' => 'Improves reflexes and response speed',
        'color' => '#FF9800'
    ],
    [
        'id' => 'puzzle',
        'name' => 'Puzzle Solver',
        'icon' => '🧩',
        'description' => 'Complete patterns and solve visual puzzles.',
        'benefits' => 'Boosts problem-solving and spatial reasoning',
        'color' => '#9C27B0'
    ],
    [
        'id' => 'visual_memory',
        'name' => 'Visual Memory',
        'icon' => '👁️‍🗨️',
        'description' => 'Remember the positions of highlighted squares on a grid.',
        'benefits' => 'Enhances visual memory and spatial awareness',
        'color' => '#00BCD4'
    ],
    [
        'id' => 'number_memory',
        'name' => 'Number Memory',
        'icon' => '🔢',
        'description' => 'Remember increasingly long sequences of numbers.',
        'benefits' => 'Improves working memory and number recall',
        'color' => '#E91E63'
    ],
    [
        'id' => 'verbal_memory',
        'name' => 'Verbal Memory',
        'icon' => '📝',
        'description' => 'Identify whether you\'ve seen each word before.',
        'benefits' => 'Strengthens verbal memory and recognition',
        'color' => '#009688'
    ],
    [
        'id' => 'chimp_test',
        'name' => 'Chimp Test',
        'icon' => '🐵',
        'description' => 'Click numbers in ascending order after they disappear.',
        'benefits' => 'Tests working memory and number sequencing',
        'color' => '#795548'
    ]
];

require_once __DIR__ . '/../_header.php';
?>

<link rel="stylesheet" href="/assets/css/games.css">

<div class="games-header">
    <h1>🎮 Cognitive Training Games</h1>
    <p style="font-size: 18px; color: #666;"><?php echo $msg_game_list; ?></p>
</div>

<div class="games-grid">
    <?php foreach ($games as $game): 
        // New games without difficulty selection
        $no_difficulty_games = ['visual_memory', 'number_memory', 'verbal_memory', 'chimp_test'];
        $has_difficulty = !in_array($game['id'], $no_difficulty_games);
    ?>
        <div class="game-card" style="border-left-color: <?php echo $game['color']; ?>;">
            <div class="game-icon"><?php echo $game['icon']; ?></div>
            <h2 class="game-name"><?php echo $game['name']; ?></h2>
            <p class="game-description"><?php echo $game['description']; ?></p>
            <div class="game-benefits">
                <strong>💡 Benefits:</strong> <?php echo $game['benefits']; ?>
            </div>
            
            <?php if (isset($stats_by_game[$game['id']])): ?>
                <div class="game-stats">
                    <strong>📊 Your Stats:</strong>
                    Played: <?php echo $stats_by_game[$game['id']]['games_played']; ?> times<br>
                    Best Score: <?php echo round($stats_by_game[$game['id']]['best_score']); ?><br>
                    Avg Score: <?php echo round($stats_by_game[$game['id']]['avg_score']); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($has_difficulty): ?>
                <div class="difficulty-selector">
                    <label>Select Difficulty:</label>
                    <div class="difficulty-buttons">
                        <a href="game_play.php?game=<?php echo $game['id']; ?>&difficulty=easy" class="difficulty-btn easy">
                            😊 Easy
                        </a>
                        <a href="game_play.php?game=<?php echo $game['id']; ?>&difficulty=medium" class="difficulty-btn medium">
                            😐 Medium
                        </a>
                        <a href="game_play.php?game=<?php echo $game['id']; ?>&difficulty=hard" class="difficulty-btn hard">
                            😤 Hard
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="difficulty-selector">
                    <a href="/games/<?php echo $game['id']; ?>.php" class="btn btn-success" style="width: 100%; padding: 15px; font-size: 18px; margin-top: 10px;">
                        🎮 Play Now
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/../_footer.php'; ?>
