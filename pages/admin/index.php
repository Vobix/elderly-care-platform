<?php
/**
 * Admin Dashboard
 * Main entry point for admin panel
 */
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../../database/config.php';

$pageTitle = "Admin Dashboard";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | Mind Mosaic</title>
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
                <a href="/pages/admin/index.php" class="nav-item active">
                    <span class="icon">📊</span> Dashboard
                </a>
                <a href="/pages/admin/users.php" class="nav-item">
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
                <h1>Dashboard Overview</h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</p>
            </div>

            <?php
            // Get quick stats
            try {
                // Total users
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
                $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                // Active users (logged in last 7 days)
                $stmt = $pdo->query("SELECT COUNT(*) as active FROM users WHERE last_login_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
                $activeUsers = $stmt->fetch(PDO::FETCH_ASSOC)['active'];
                
                // Total games played
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM game_sessions");
                $totalGames = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                // Total mood logs
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM mood_logs");
                $totalMoods = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                // Total questionnaires completed
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM questionnaire_responses");
                $totalQuestionnaires = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                // Users who completed baseline assessment
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE has_completed_initial_assessment = 1");
                $completedBaseline = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
            } catch (PDOException $e) {
                error_log("Dashboard stats error: " . $e->getMessage());
                $totalUsers = $activeUsers = $totalGames = $totalMoods = $totalQuestionnaires = $completedBaseline = 0;
            }
            ?>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-details">
                        <h3><?php echo number_format($totalUsers); ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-details">
                        <h3><?php echo number_format($activeUsers); ?></h3>
                        <p>Active (7 days)</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">🎮</div>
                    <div class="stat-details">
                        <h3><?php echo number_format($totalGames); ?></h3>
                        <p>Games Played</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">😊</div>
                    <div class="stat-details">
                        <h3><?php echo number_format($totalMoods); ?></h3>
                        <p>Mood Logs</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">📝</div>
                    <div class="stat-details">
                        <h3><?php echo number_format($totalQuestionnaires); ?></h3>
                        <p>Questionnaires</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">🎯</div>
                    <div class="stat-details">
                        <h3><?php echo number_format($completedBaseline); ?></h3>
                        <p>Baseline Assessments</p>
                    </div>
                </div>
            </div>

            <div class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-buttons">
                    <a href="/pages/admin/users.php" class="btn btn-primary">
                        <span class="icon">👥</span> Manage Users
                    </a>
                    <a href="/pages/admin/content.php" class="btn btn-primary">
                        <span class="icon">🎮</span> Manage Content
                    </a>
                    <a href="/pages/admin/analytics.php" class="btn btn-primary">
                        <span class="icon">📈</span> View Analytics
                    </a>
                </div>
            </div>

            <?php
            // Recent admin actions
            try {
                $stmt = $pdo->prepare("
                    SELECT aa.*, u.username as admin_name, tu.username as target_name
                    FROM admin_actions aa
                    JOIN users u ON aa.admin_user_id = u.user_id
                    LEFT JOIN users tu ON aa.target_user_id = tu.user_id
                    ORDER BY aa.created_at DESC
                    LIMIT 10
                ");
                $stmt->execute();
                $recentActions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $recentActions = [];
            }
            ?>

            <?php if (!empty($recentActions)): ?>
            <div class="recent-activity">
                <h2>Recent Admin Activity</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Admin</th>
                            <th>Action</th>
                            <th>Target</th>
                            <th>Description</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentActions as $action): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($action['admin_name']); ?></td>
                            <td><span class="badge"><?php echo htmlspecialchars($action['action_type']); ?></span></td>
                            <td><?php echo $action['target_name'] ? htmlspecialchars($action['target_name']) : '-'; ?></td>
                            <td><?php echo htmlspecialchars($action['description']); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($action['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include __DIR__ . '/../../_footer.php'; ?>
    <script src="/assets/js/admin.js"></script>
</body>
</html>
