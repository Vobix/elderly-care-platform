<?php
/**
 * UC07: Manage System Content
 * Add/Edit/Delete games, questionnaires, and difficulty presets
 */
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../../database/config.php';

$pageTitle = "Manage Content";
$message = '';
$messageType = '';

// Handle content actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        // CLEAR LEADERBOARD
        if ($action === 'clear_leaderboard') {
            $gameId = intval($_POST['game_id'] ?? 0);
            
            if (!$gameId) {
                throw new Exception('Invalid game ID');
            }
            
            // Get game name
            $stmt = $pdo->prepare("SELECT name FROM games WHERE game_id = ?");
            $stmt->execute([$gameId]);
            $gameName = $stmt->fetchColumn();
            
            // Delete leaderboard data
            $stmt = $pdo->prepare("DELETE FROM user_game_stats WHERE game_id = ?");
            $stmt->execute([$gameId]);
            
            // Log action
            $stmt = $pdo->prepare("INSERT INTO admin_actions (admin_user_id, action_type, description) VALUES (?, 'clear_leaderboard', ?)");
            $stmt->execute([$_SESSION['user_id'], "Cleared leaderboard for: {$gameName}"]);
            
            $message = "Leaderboard cleared successfully."; // M3
            $messageType = 'success';
        }
        
    } catch (Exception $e) {
        error_log("Content management error: " . $e->getMessage());
        $message = $e->getMessage() === 'Invalid Content. Please check all the fields are filled with correct value.' 
            ? $e->getMessage() 
            : "Content update failed. Please try again."; // M4
        $messageType = 'error';
    }
}

// Get all games
try {
    $stmt = $pdo->query("SELECT * FROM games ORDER BY name");
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $games = [];
}

// Get all questionnaires
try {
    $stmt = $pdo->query("SELECT * FROM questionnaires ORDER BY name");
    $questionnaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $questionnaires = [];
}

$contentMessage = "Content list loaded successfully."; // M1
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | Mind Mosaic Admin</title>
    <link rel="stylesheet" href="/assets/css/header.css">
    <link rel="stylesheet" href="/assets/css/footer.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
    <?php include __DIR__ . '/../../_header.php'; ?>

    <main class="admin-container">
        <div class="admin-sidebar">
            <h2>Admin Panel</h2>
            <nav class="admin-nav">
                <a href="/pages/admin/index.php" class="nav-item">
                    <span class="icon">📊</span> Dashboard
                </a>
                <a href="/pages/admin/users.php" class="nav-item">
                    <span class="icon">👥</span> User Management
                </a>
                <a href="/pages/admin/content.php" class="nav-item active">
                    <span class="icon">🎮</span> Manage Content
                </a>
                <a href="/pages/admin/analytics.php" class="nav-item">
                    <span class="icon">📈</span> System Analytics
                </a>
                <hr>
                <a href="/pages/insights/dashboard.php" class="nav-item">
                    <span class="icon">👤</span> User View
                </a>
                <a href="/pages/account/logout.php" class="nav-item">
                    <span class="icon">🚪</span> Logout
                </a>
            </nav>
        </div>

        <div class="admin-content">
            <div class="admin-header">
                <h1>Manage System Content</h1>
                <p><?php echo htmlspecialchars($contentMessage); ?></p>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <!-- GAMES SECTION -->
            <div class="section-card">
                <h2>🎮 Manage Games</h2>
                <button onclick="showAddGameForm()" class="btn btn-primary">+ Add New Game</button>
                
                <div id="addGameForm" style="display: none; margin-top: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 6px;">
                    <h3>Add New Game</h3>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to add this game?')"> <!-- C1 -->
                        <input type="hidden" name="action" value="add_game">
                        <div style="margin-bottom: 1rem;">
                            <label>Game Name: <input type="text" name="name" required style="width: 100%; padding: 0.5rem;"></label>
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <label>Game Code: <input type="text" name="code" required style="width: 100%; padding: 0.5rem;"></label>
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <label>Description: <textarea name="description" style="width: 100%; padding: 0.5rem;" rows="3"></textarea></label>
                        </div>
                        <button type="submit" class="btn btn-success">Add Game</button>
                        <button type="button" onclick="hideAddGameForm()" class="btn btn-secondary">Cancel</button>
                    </form>
                </div>

                <table class="data-table" style="margin-top: 1rem;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Description</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($games as $game): ?>
                        <tr>
                            <td><?php echo $game['game_id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($game['name']); ?></strong></td>
                            <td><code><?php echo htmlspecialchars($game['code']); ?></code></td>
                            <td><?php echo htmlspecialchars($game['description'] ?? '-'); ?></td>
                            <td><?php echo date('M d, Y', strtotime($game['created_at'])); ?></td>
                            <td>
                                <button onclick="confirmClearLeaderboard(<?php echo $game['game_id']; ?>, '<?php echo htmlspecialchars($game['name'], ENT_QUOTES); ?>')" class="btn btn-sm btn-warning">Clear Leaderboard</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- QUESTIONNAIRES SECTION -->
            <div class="section-card">
                <h2>📝 Questionnaires (View Only)</h2>

                <table class="data-table" style="margin-top: 1rem;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Short Code</th>
                            <th>Type</th>
                            <th>Version</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($questionnaires as $q): ?>
                        <tr>
                            <td><?php echo $q['questionnaire_id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($q['name']); ?></strong></td>
                            <td><code><?php echo htmlspecialchars($q['short_code']); ?></code></td>
                            <td><?php echo htmlspecialchars($q['type']); ?></td>
                            <td><?php echo htmlspecialchars($q['version']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($q['created_at'])); ?></td>
                            <td>
                                <span style="color: #999;">View Only</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../../_footer.php'; ?>
    <script src="/assets/js/admin.js"></script>
    <script>
    function confirmClearLeaderboard(id, name) {
        if (confirm(`Are you sure you want to clear the leaderboard for "${name}"?\n\nThis will delete all user statistics for this game. This action cannot be undone.`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="clear_leaderboard">
                <input type="hidden" name="game_id" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
    </script>
</body>
</html>
