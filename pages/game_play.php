<?php
/**
 * Game Play Page
 * Dynamically loads and plays the selected game
 */

$page_title = "Play Game";
require_once __DIR__ . '/account/auth.php';

$user_id = $_SESSION['user_id'];
$game_type = $_GET['game'] ?? '';
$difficulty = $_GET['difficulty'] ?? 'medium';

// Validate game type
$valid_games = ['memory', 'attention', 'reaction', 'puzzle'];
if (!in_array($game_type, $valid_games)) {
    header("Location: games.php");
    exit();
}

// Validate difficulty
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
    include $game_file;
} else {
    // If game file doesn't exist, show error
    require_once __DIR__ . '/../_header.php';
    echo '<div class="alert alert-error">Game not found!</div>';
    echo '<a href="games.php" class="btn btn-primary">Back to Games</a>';
    require_once __DIR__ . '/../_footer.php';
    exit();
}
?>
