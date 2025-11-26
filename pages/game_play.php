<?php
/**
 * Game Play Page
 * Dynamically loads and plays the selected game
 * 
 * Messages:
 * M2: Your game is loading. Please wait…
 * M5: Unable to load the game. Please try again later.
 * 
 * Constraints:
 * C2: Allowed Difficulty Levels = {Easy, Medium, Hard}
 * C4: Valid Game Launch Action = Difficulty button (Easy/Medium/Hard)
 */

$page_title = "Play Game";
require_once __DIR__ . '/account/auth.php';

$user_id = $_SESSION['user_id'];
$game_type = $_GET['game'] ?? '';
$difficulty = $_GET['difficulty'] ?? 'medium';

// M2: Game loading message
$msg_game_loading = "Your game is loading. Please wait…";
// M5: Game load failed message
$msg_game_load_failed = "Unable to load the game. Please try again later.";

// C4: Validate game type (only games with difficulty selection)
$valid_games = ['memory', 'attention', 'reaction', 'puzzle'];
if (!in_array($game_type, $valid_games)) {
    header("Location: games.php");
    exit();
}

// C2: Validate difficulty levels - {Easy, Medium, Hard}
$valid_difficulties = ['easy', 'medium', 'hard'];
if (!in_array($difficulty, $valid_difficulties)) {
    $difficulty = 'medium';
}

// Game titles
$game_titles = [
    'memory' => 'Memory Match',
    'attention' => 'Attention Focus',
    'reaction' => 'Reaction Time',
    'puzzle' => 'Puzzle Solver'
];

$page_title = $game_titles[$game_type];

// Include the specific game logic
$game_file = __DIR__ . "/../games/{$game_type}.php";
if (file_exists($game_file)) {
    // M2: Show loading message before including game
    echo '<div id="game-loading" style="text-align: center; padding: 50px; font-size: 20px; color: #666;">';
    echo '<div style="font-size: 48px; margin-bottom: 20px;">⏳</div>';
    echo $msg_game_loading;
    echo '</div>';
    echo '<script>setTimeout(() => document.getElementById("game-loading").remove(), 500);</script>';
    
    include $game_file;
} else {
    // M5: Game load failed error
    require_once __DIR__ . '/../_header.php';
    echo '<div class="alert alert-error">' . $msg_game_load_failed . '</div>';
    echo '<a href="games.php" class="btn btn-primary">Back to Games</a>';
    require_once __DIR__ . '/../_footer.php';
    exit();
}
?>
