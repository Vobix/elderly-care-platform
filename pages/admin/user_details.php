<?php
/**
 * User Details Page
 * Shows detailed user information, game progress, mood history
 */
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../../database/config.php';

$userId = $_GET['id'] ?? 0;

if (!$userId) {
    header("Location: /pages/admin/users.php?error=missing_id");
    exit();
}

try {
    // Get user details
    $stmt = $pdo->prepare("
        SELECT u.*, 
               ba.score as baseline_score,
               ba.risk_category,
               ba.completed_at as baseline_completed_at
        FROM users u
        LEFT JOIN baseline_assessments ba ON u.baseline_assessment_id = ba.assessment_id
        WHERE u.user_id = ?
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        header("Location: /pages/admin/users.php?error=user_not_found"); // M6
        exit();
    }
    
    // Get game stats
    $stmt = $pdo->prepare("
        SELECT g.name, g.code, ugs.times_played, ugs.best_score, ugs.average_score, ugs.last_played_at
        FROM user_game_stats ugs
        JOIN games g ON ugs.game_id = g.game_id
        WHERE ugs.user_id = ?
        ORDER BY ugs.last_played_at DESC
    ");
    $stmt->execute([$userId]);
    $gameStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get mood history (last 30 days)
    $stmt = $pdo->prepare("
        SELECT mood_value, notes, created_at
        FROM mood_logs
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 30
    ");
    $stmt->execute([$userId]);
    $moodHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get questionnaire results
    $stmt = $pdo->prepare("
        SELECT q.name, qr.score, qr.completed_at
        FROM questionnaire_responses qr
        JOIN questionnaires q ON qr.questionnaire_id = q.questionnaire_id
        WHERE qr.user_id = ?
        ORDER BY qr.completed_at DESC
        LIMIT 10
    ");
    $stmt->execute([$userId]);
    $questionnaireResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("User details error: " . $e->getMessage());
    header("Location: /pages/admin/users.php?error=load_failed");
    exit();
}

$pageTitle = "User Details: " . htmlspecialchars($user['username']);
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
                <h1><?php echo htmlspecialchars($user['username']); ?></h1>
                <p>User details loaded successfully <!-- M2 --></p>
                <a href="/pages/admin/users.php" class="btn btn-secondary">← Back to Users</a>
            </div>

            <!-- User Profile Card -->
            <div class="user-profile-card">
                <h2>Profile Information</h2>
                <div class="profile-grid">
                    <div class="profile-item">
                        <strong>User ID:</strong> <?php echo htmlspecialchars($user['user_id']); ?>
                    </div>
                    <div class="profile-item">
                        <strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?>
                    </div>
                    <div class="profile-item">
                        <strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?>
                    </div>
                    <div class="profile-item">
                        <strong>Full Name:</strong> <?php echo htmlspecialchars($user['full_name'] ?? 'Not provided'); ?>
                    </div>
                    <div class="profile-item">
                        <strong>Date of Birth:</strong> <?php echo $user['date_of_birth'] ? date('M d, Y', strtotime($user['date_of_birth'])) : 'Not provided'; ?>
                    </div>
                    <div class="profile-item">
                        <strong>Role:</strong> 
                        <span class="badge badge-<?php echo $user['is_admin'] ? 'admin' : 'info'; ?>">
                            <?php echo $user['is_admin'] ? 'Admin' : ucfirst($user['role']); ?>
                        </span>
                    </div>
                    <div class="profile-item">
                        <strong>Status:</strong>
                        <span class="badge badge-<?php echo $user['is_active'] ? 'success' : 'danger'; ?>">
                            <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </div>
                    <div class="profile-item">
                        <strong>Joined:</strong> <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                    </div>
                    <div class="profile-item">
                        <strong>Last Login:</strong> <?php echo $user['last_login_at'] ? date('M d, Y H:i', strtotime($user['last_login_at'])) : 'Never'; ?>
                    </div>
                </div>
            </div>

            <!-- Baseline Assessment -->
            <?php if ($user['has_completed_initial_assessment']): ?>
            <div class="assessment-card">
                <h2>Baseline Assessment</h2>
                <div class="profile-grid">
                    <div class="profile-item">
                        <strong>Score:</strong> <?php echo number_format($user['baseline_score'], 2); ?>
                    </div>
                    <div class="profile-item">
                        <strong>Risk Category:</strong>
                        <span class="badge badge-risk-<?php echo $user['risk_category']; ?>">
                            <?php echo ucfirst($user['risk_category']); ?>
                        </span>
                    </div>
                    <div class="profile-item">
                        <strong>Completed:</strong> <?php echo date('M d, Y', strtotime($user['baseline_completed_at'])); ?>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="alert alert-warning">
                This user has not completed their baseline assessment yet.
            </div>
            <?php endif; ?>

            <!-- Game Progress -->
            <div class="section-card">
                <h2>Game Progress</h2>
                <?php if (empty($gameStats)): ?>
                <p>No games played yet.</p>
                <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Game</th>
                            <th>Times Played</th>
                            <th>Best Score</th>
                            <th>Average Score</th>
                            <th>Last Played</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($gameStats as $stat): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($stat['name']); ?></strong></td>
                            <td><?php echo number_format($stat['times_played']); ?></td>
                            <td><?php echo number_format($stat['best_score']); ?></td>
                            <td><?php echo number_format($stat['average_score'], 2); ?></td>
                            <td><?php echo date('M d, Y', strtotime($stat['last_played_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <!-- Mood History -->
            <div class="section-card">
                <h2>Mood History (Last 30 Days)</h2>
                <?php if (empty($moodHistory)): ?>
                <p>No mood logs recorded yet.</p>
                <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Mood Level</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($moodHistory as $mood): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($mood['created_at'])); ?></td>
                            <td>
                                <span class="mood-indicator mood-<?php echo $mood['mood_value']; ?>">
                                    <?php echo $mood['mood_value']; ?>/5
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($mood['notes'] ?? '-'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <!-- Questionnaire Results -->
            <div class="section-card">
                <h2>Questionnaire History</h2>
                <?php if (empty($questionnaireResults)): ?>
                <p>No questionnaires completed yet.</p>
                <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Questionnaire</th>
                            <th>Score</th>
                            <th>Completed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($questionnaireResults as $result): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($result['name']); ?></td>
                            <td><?php echo number_format($result['score'], 2); ?></td>
                            <td><?php echo date('M d, Y', strtotime($result['completed_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../../_footer.php'; ?>
    <script src="/assets/js/admin.js"></script>
</body>
</html>
