<?php
/**
 * UC06: User Management
 * View users, user details, deactivate/reactivate users
 */
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../../database/config.php';

$pageTitle = "User Management";
$message = '';
$messageType = '';

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $targetUserId = $_POST['user_id'] ?? 0;
    
    try {
        if ($action === 'deactivate' && $targetUserId) {
            // C1: Confirmation already handled by JavaScript
            $stmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE user_id = ?");
            $stmt->execute([$targetUserId]);
            
            // Log admin action
            $stmt = $pdo->prepare("
                INSERT INTO admin_actions (admin_user_id, action_type, target_user_id, description)
                VALUES (?, 'user_deactivate', ?, 'Deactivated user account')
            ");
            $stmt->execute([$_SESSION['user_id'], $targetUserId]);
            
            $message = "User status has been updated."; // M3
            $messageType = 'success';
            
        } elseif ($action === 'reactivate' && $targetUserId) {
            // C1: Confirmation already handled by JavaScript
            $stmt = $pdo->prepare("UPDATE users SET is_active = 1 WHERE user_id = ?");
            $stmt->execute([$targetUserId]);
            
            // Log admin action
            $stmt = $pdo->prepare("
                INSERT INTO admin_actions (admin_user_id, action_type, target_user_id, description)
                VALUES (?, 'user_reactivate', ?, 'Reactivated user account')
            ");
            $stmt->execute([$_SESSION['user_id'], $targetUserId]);
            
            $message = "User status has been updated."; // M3
            $messageType = 'success';
        }
    } catch (PDOException $e) {
        error_log("User management error: " . $e->getMessage());
        $message = "Action failed. Please try again."; // M5
        $messageType = 'error';
    }
}

// Get filter parameters
$searchTerm = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? 'all';

// Build query
try {
    $sql = "
        SELECT 
            u.user_id,
            u.username,
            u.email,
            u.full_name,
            u.is_active,
            u.is_admin,
            u.created_at,
            u.last_login_at,
            u.has_completed_initial_assessment,
            (SELECT COUNT(*) FROM game_sessions WHERE user_id = u.user_id) as total_games,
            (SELECT COUNT(*) FROM mood_logs WHERE user_id = u.user_id) as total_moods,
            (SELECT COUNT(*) FROM questionnaire_responses WHERE user_id = u.user_id) as total_questionnaires
        FROM users u
        WHERE 1=1
    ";
    
    $params = [];
    
    if ($searchTerm) {
        $sql .= " AND (u.username LIKE ? OR u.email LIKE ? OR u.full_name LIKE ?)";
        $searchParam = "%{$searchTerm}%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    if ($statusFilter === 'active') {
        $sql .= " AND u.is_active = 1";
    } elseif ($statusFilter === 'inactive') {
        $sql .= " AND u.is_active = 0";
    }
    
    $sql .= " ORDER BY u.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($searchTerm || $statusFilter !== 'all') {
        $filterMessage = "Filters applied. Updating list..."; // M4
    } else {
        $filterMessage = "User list loaded successfully."; // M1
    }
    
} catch (PDOException $e) {
    error_log("User list error: " . $e->getMessage());
    $users = [];
    $filterMessage = "";
}
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
                <a href="/pages/admin/users.php" class="nav-item active">
                    <span class="icon">👥</span> User Management
                </a>
                <a href="/pages/admin/content.php" class="nav-item">
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
                <h1>User Management</h1>
                <p>View and manage user accounts</p>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <!-- Search and Filter -->
            <div class="filter-section">
                <form method="GET" class="filter-form">
                    <input type="text" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                    <select name="status">
                        <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="/pages/admin/users.php" class="btn btn-secondary">Clear</a>
                </form>
            </div>

            <?php if ($filterMessage): ?>
            <p class="info-message"><?php echo htmlspecialchars($filterMessage); ?></p>
            <?php endif; ?>

            <!-- Users Table -->
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Full Name</th>
                            <th>Status</th>
                            <th>Games</th>
                            <th>Moods</th>
                            <th>Baseline</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="10" class="text-center">User not found. <!-- M6 --></td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                    <?php if ($user['is_admin']): ?>
                                    <span class="badge badge-admin">Admin</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['full_name'] ?? '-'); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $user['is_active'] ? 'success' : 'danger'; ?>">
                                        <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td><?php echo number_format($user['total_games']); ?></td>
                                <td><?php echo number_format($user['total_moods']); ?></td>
                                <td>
                                    <?php if ($user['has_completed_initial_assessment']): ?>
                                    <span class="badge badge-success">✓</span>
                                    <?php else: ?>
                                    <span class="badge badge-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <a href="/pages/admin/user_details.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-info">
                                        View Details
                                    </a>
                                    <?php if ($user['is_active']): ?>
                                    <button onclick="confirmDeactivate(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')" 
                                            class="btn btn-sm btn-warning">
                                        Deactivate
                                    </button>
                                    <?php else: ?>
                                    <button onclick="confirmReactivate(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')" 
                                            class="btn btn-sm btn-success">
                                        Reactivate
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <p class="help-text">Total users: <?php echo count($users); ?></p>
        </div>
    </main>

    <!-- Hidden form for status changes -->
    <form id="statusForm" method="POST" style="display: none;">
        <input type="hidden" name="action" id="formAction">
        <input type="hidden" name="user_id" id="formUserId">
    </form>

    <?php include __DIR__ . '/../../_footer.php'; ?>
    <script src="/assets/js/admin.js"></script>
</body>
</html>
