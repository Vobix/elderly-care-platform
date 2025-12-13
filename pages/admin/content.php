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
        // GAMES MANAGEMENT
        if ($action === 'add_game') {
            $name = trim($_POST['name'] ?? '');
            $code = trim($_POST['code'] ?? '');
            $description = trim($_POST['description'] ?? '');
            
            if (empty($name) || empty($code)) {
                throw new Exception('Invalid Content. Please check all the fields are filled with correct value.'); // M5
            }
            
            $stmt = $pdo->prepare("INSERT INTO games (name, code, description) VALUES (?, ?, ?)");
            $stmt->execute([$name, $code, $description]);
            
            $stmt = $pdo->prepare("INSERT INTO admin_actions (admin_user_id, action_type, description) VALUES (?, 'add_game', ?)");
            $stmt->execute([$_SESSION['user_id'], "Added game: {$name}"]);
            
            $message = "Content updated successfully."; // M3
            $messageType = 'success';
            
        } elseif ($action === 'edit_game') {
            $gameId = intval($_POST['game_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $code = trim($_POST['code'] ?? '');
            $description = trim($_POST['description'] ?? '');
            
            if (empty($name) || empty($code) || !$gameId) {
                throw new Exception('Invalid Content. Please check all the fields are filled with correct value.'); // M5
            }
            
            $stmt = $pdo->prepare("UPDATE games SET name = ?, code = ?, description = ? WHERE game_id = ?");
            $stmt->execute([$name, $code, $description, $gameId]);
            
            $stmt = $pdo->prepare("INSERT INTO admin_actions (admin_user_id, action_type, description) VALUES (?, 'edit_game', ?)");
            $stmt->execute([$_SESSION['user_id'], "Edited game: {$name}"]);
            
            $message = "Content updated successfully."; // M3
            $messageType = 'success';
            
        } elseif ($action === 'delete_game') {
            $gameId = intval($_POST['game_id'] ?? 0);
            
            if (!$gameId) {
                throw new Exception('Invalid game ID');
            }
            
            // Get game name before deletion
            $stmt = $pdo->prepare("SELECT name FROM games WHERE game_id = ?");
            $stmt->execute([$gameId]);
            $gameName = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("DELETE FROM games WHERE game_id = ?");
            $stmt->execute([$gameId]);
            
            $stmt = $pdo->prepare("INSERT INTO admin_actions (admin_user_id, action_type, description) VALUES (?, 'delete_game', ?)");
            $stmt->execute([$_SESSION['user_id'], "Deleted game: {$gameName}"]);
            
            $message = "Content updated successfully."; // M3
            $messageType = 'success';
            
        // QUESTIONNAIRES MANAGEMENT
        } elseif ($action === 'add_questionnaire') {
            $name = trim($_POST['name'] ?? '');
            $shortCode = trim($_POST['short_code'] ?? '');
            $type = trim($_POST['type'] ?? '');
            $version = trim($_POST['version'] ?? '1.0');
            
            if (empty($name) || empty($shortCode) || empty($type)) {
                throw new Exception('Invalid Content. Please check all the fields are filled with correct value.'); // M5
            }
            
            $stmt = $pdo->prepare("INSERT INTO questionnaires (name, short_code, type, version) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $shortCode, $type, $version]);
            
            $stmt = $pdo->prepare("INSERT INTO admin_actions (admin_user_id, action_type, description) VALUES (?, 'add_questionnaire', ?)");
            $stmt->execute([$_SESSION['user_id'], "Added questionnaire: {$name}"]);
            
            $message = "Content updated successfully."; // M3
            $messageType = 'success';
            
        } elseif ($action === 'edit_questionnaire') {
            $questionnaireId = intval($_POST['questionnaire_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $shortCode = trim($_POST['short_code'] ?? '');
            $type = trim($_POST['type'] ?? '');
            $version = trim($_POST['version'] ?? '1.0');
            
            if (empty($name) || empty($shortCode) || empty($type) || !$questionnaireId) {
                throw new Exception('Invalid Content. Please check all the fields are filled with correct value.'); // M5
            }
            
            $stmt = $pdo->prepare("UPDATE questionnaires SET name = ?, short_code = ?, type = ?, version = ? WHERE questionnaire_id = ?");
            $stmt->execute([$name, $shortCode, $type, $version, $questionnaireId]);
            
            $stmt = $pdo->prepare("INSERT INTO admin_actions (admin_user_id, action_type, description) VALUES (?, 'edit_questionnaire', ?)");
            $stmt->execute([$_SESSION['user_id'], "Edited questionnaire: {$name}"]);
            
            $message = "Content updated successfully."; // M3
            $messageType = 'success';
            
        } elseif ($action === 'delete_questionnaire') {
            $questionnaireId = intval($_POST['questionnaire_id'] ?? 0);
            
            if (!$questionnaireId) {
                throw new Exception('Invalid questionnaire ID');
            }
            
            $stmt = $pdo->prepare("SELECT name FROM questionnaires WHERE questionnaire_id = ?");
            $stmt->execute([$questionnaireId]);
            $questionnaireName = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("DELETE FROM questionnaires WHERE questionnaire_id = ?");
            $stmt->execute([$questionnaireId]);
            
            $stmt = $pdo->prepare("INSERT INTO admin_actions (admin_user_id, action_type, description) VALUES (?, 'delete_questionnaire', ?)");
            $stmt->execute([$_SESSION['user_id'], "Deleted questionnaire: {$questionnaireName}"]);
            
            $message = "Content updated successfully."; // M3
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
    <title><?php echo $pageTitle; ?> | Elder Care Admin</title>
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
                                <button onclick="showEditGameForm(<?php echo $game['game_id']; ?>, '<?php echo htmlspecialchars($game['name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($game['code'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($game['description'] ?? '', ENT_QUOTES); ?>')" class="btn btn-sm btn-info">Edit</button>
                                <button onclick="confirmDeleteGame(<?php echo $game['game_id']; ?>, '<?php echo htmlspecialchars($game['name'], ENT_QUOTES); ?>')" class="btn btn-sm btn-danger">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- QUESTIONNAIRES SECTION -->
            <div class="section-card">
                <h2>📝 Manage Questionnaires</h2>
                <button onclick="showAddQuestionnaireForm()" class="btn btn-primary">+ Add New Questionnaire</button>
                
                <div id="addQuestionnaireForm" style="display: none; margin-top: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 6px;">
                    <h3>Add New Questionnaire</h3>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to add this questionnaire?')"> <!-- C1 -->
                        <input type="hidden" name="action" value="add_questionnaire">
                        <div style="margin-bottom: 1rem;">
                            <label>Name: <input type="text" name="name" required style="width: 100%; padding: 0.5rem;"></label>
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <label>Short Code: <input type="text" name="short_code" required style="width: 100%; padding: 0.5rem;"></label>
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <label>Type: <input type="text" name="type" required style="width: 100%; padding: 0.5rem;"></label>
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <label>Version: <input type="text" name="version" value="1.0" style="width: 100%; padding: 0.5rem;"></label>
                        </div>
                        <button type="submit" class="btn btn-success">Add Questionnaire</button>
                        <button type="button" onclick="hideAddQuestionnaireForm()" class="btn btn-secondary">Cancel</button>
                    </form>
                </div>

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
                                <button onclick="showEditQuestionnaireForm(<?php echo $q['questionnaire_id']; ?>, '<?php echo htmlspecialchars($q['name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($q['short_code'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($q['type'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($q['version'], ENT_QUOTES); ?>')" class="btn btn-sm btn-info">Edit</button>
                                <button onclick="confirmDeleteQuestionnaire(<?php echo $q['questionnaire_id']; ?>, '<?php echo htmlspecialchars($q['name'], ENT_QUOTES); ?>')" class="btn btn-sm btn-danger">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Hidden forms for edit/delete -->
    <form id="editGameForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="edit_game">
        <input type="hidden" name="game_id" id="edit_game_id">
        <input type="hidden" name="name" id="edit_game_name">
        <input type="hidden" name="code" id="edit_game_code">
        <input type="hidden" name="description" id="edit_game_description">
    </form>

    <form id="editQuestionnaireForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="edit_questionnaire">
        <input type="hidden" name="questionnaire_id" id="edit_q_id">
        <input type="hidden" name="name" id="edit_q_name">
        <input type="hidden" name="short_code" id="edit_q_code">
        <input type="hidden" name="type" id="edit_q_type">
        <input type="hidden" name="version" id="edit_q_version">
    </form>

    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" id="deleteAction">
        <input type="hidden" name="game_id" id="deleteGameId">
        <input type="hidden" name="questionnaire_id" id="deleteQuestionnaireId">
    </form>

    <?php include __DIR__ . '/../../_footer.php'; ?>
    <script src="/assets/js/admin.js"></script>
    <script>
    function showAddGameForm() { document.getElementById('addGameForm').style.display = 'block'; }
    function hideAddGameForm() { document.getElementById('addGameForm').style.display = 'none'; }
    function showAddQuestionnaireForm() { document.getElementById('addQuestionnaireForm').style.display = 'block'; }
    function hideAddQuestionnaireForm() { document.getElementById('addQuestionnaireForm').style.display = 'none'; }
    
    function showEditGameForm(id, name, code, desc) {
        const newName = prompt('Game Name:', name);
        const newCode = prompt('Game Code:', code);
        const newDesc = prompt('Description:', desc);
        
        if (newName && newCode && confirm('Are you sure you want to update this game?')) {
            document.getElementById('edit_game_id').value = id;
            document.getElementById('edit_game_name').value = newName;
            document.getElementById('edit_game_code').value = newCode;
            document.getElementById('edit_game_description').value = newDesc;
            document.getElementById('editGameForm').submit();
        }
    }
    
    function showEditQuestionnaireForm(id, name, code, type, version) {
        const newName = prompt('Questionnaire Name:', name);
        const newCode = prompt('Short Code:', code);
        const newType = prompt('Type:', type);
        const newVersion = prompt('Version:', version);
        
        if (newName && newCode && newType && confirm('Are you sure you want to update this questionnaire?')) {
            document.getElementById('edit_q_id').value = id;
            document.getElementById('edit_q_name').value = newName;
            document.getElementById('edit_q_code').value = newCode;
            document.getElementById('edit_q_type').value = newType;
            document.getElementById('edit_q_version').value = newVersion;
            document.getElementById('editQuestionnaireForm').submit();
        }
    }
    
    function confirmDeleteGame(id, name) {
        if (confirm(`Are you sure you want to delete "${name}"?\n\nThis action cannot be undone.`)) {
            document.getElementById('deleteAction').value = 'delete_game';
            document.getElementById('deleteGameId').value = id;
            document.getElementById('deleteForm').submit();
        }
    }
    
    function confirmDeleteQuestionnaire(id, name) {
        if (confirm(`Are you sure you want to delete "${name}"?\n\nThis action cannot be undone.`)) {
            document.getElementById('deleteAction').value = 'delete_questionnaire';
            document.getElementById('deleteQuestionnaireId').value = id;
            document.getElementById('deleteForm').submit();
        }
    }
    </script>
</body>
</html>
