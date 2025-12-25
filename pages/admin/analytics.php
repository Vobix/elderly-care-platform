<?php
/**
 * UC08: View System Analytics
 * User activity, game performance, mood trends - all anonymized (C1)
 */
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../../database/config.php';

$pageTitle = "System Analytics";

// Get filter parameters
$dateRange = $_GET['range'] ?? '30';
$gameFilter = $_GET['game'] ?? 'all';

try {
    // USER ACTIVITY ANALYTICS
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as active FROM users WHERE last_login_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $activeUsers7Days = $stmt->fetch(PDO::FETCH_ASSOC)['active'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as active FROM users WHERE last_login_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $activeUsers30Days = $stmt->fetch(PDO::FETCH_ASSOC)['active'];
    
    // Login frequency (anonymized - just counts)
    $stmt = $pdo->prepare("
        SELECT DATE(last_login_at) as login_date, COUNT(*) as login_count
        FROM users
        WHERE last_login_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
        GROUP BY DATE(last_login_at)
        ORDER BY login_date DESC
        LIMIT 30
    ");
    $stmt->execute([$dateRange]);
    $loginData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Engagement levels
    $stmt = $pdo->query("
        SELECT 
            SUM(CASE WHEN total_games >= 10 THEN 1 ELSE 0 END) as high_engagement,
            SUM(CASE WHEN total_games BETWEEN 5 AND 9 THEN 1 ELSE 0 END) as medium_engagement,
            SUM(CASE WHEN total_games BETWEEN 1 AND 4 THEN 1 ELSE 0 END) as low_engagement,
            SUM(CASE WHEN total_games = 0 THEN 1 ELSE 0 END) as no_engagement
        FROM (
            SELECT u.user_id, COUNT(gs.session_id) as total_games
            FROM users u
            LEFT JOIN game_sessions gs ON u.user_id = gs.user_id
            GROUP BY u.user_id
        ) as user_engagement
    ");
    $engagement = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // GAME PERFORMANCE ANALYTICS
    $stmt = $pdo->query("
        SELECT g.name, COUNT(gs.session_id) as play_count
        FROM games g
        LEFT JOIN game_sessions gs ON g.game_id = gs.game_id
        GROUP BY g.game_id, g.name
        ORDER BY play_count DESC
    ");
    $gamePlays = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Completion rates (anonymized)
    $stmt = $pdo->query("
        SELECT g.name, 
               COUNT(gs.session_id) as total_sessions,
               COUNT(CASE WHEN gs.ended_at IS NOT NULL THEN 1 END) as completed_sessions,
               ROUND(COUNT(CASE WHEN gs.ended_at IS NOT NULL THEN 1 END) * 100.0 / COUNT(gs.session_id), 2) as completion_rate
        FROM games g
        LEFT JOIN game_sessions gs ON g.game_id = gs.game_id
        GROUP BY g.game_id, g.name
        ORDER BY completion_rate DESC
    ");
    $completionRates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Performance distribution (anonymized aggregates)
    if ($gameFilter !== 'all') {
        $stmt = $pdo->prepare("
            SELECT 
                FLOOR(score/10)*10 as score_range,
                COUNT(*) as count
            FROM game_scores sc
            JOIN game_sessions gs ON sc.session_id = gs.session_id
            JOIN games g ON gs.game_id = g.game_id
            WHERE g.code = ?
            GROUP BY score_range
            ORDER BY score_range
        ");
        $stmt->execute([$gameFilter]);
        $scoreDistribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $scoreDistribution = [];
    }
    
    // MOOD TRENDS (anonymized aggregates)
    $stmt = $pdo->prepare("
        SELECT DATE(created_at) as mood_date, 
               AVG(mood_value) as avg_mood,
               COUNT(*) as log_count
        FROM mood_logs
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
        GROUP BY DATE(created_at)
        ORDER BY mood_date DESC
        LIMIT 30
    ");
    $stmt->execute([$dateRange]);
    $moodTrends = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Mood distribution (anonymized)
    $stmt = $pdo->query("
        SELECT mood_value, COUNT(*) as count
        FROM mood_logs
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY mood_value
        ORDER BY mood_value
    ");
    $moodDistribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Questionnaire completion stats
    $stmt = $pdo->query("
        SELECT q.name, COUNT(qr.result_id) as completion_count
        FROM questionnaires q
        LEFT JOIN questionnaire_responses qr ON q.questionnaire_id = qr.questionnaire_id
        GROUP BY q.questionnaire_id, q.name
        ORDER BY completion_count DESC
    ");
    $questionnaireStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $analyticsMessage = "Analytics categories loaded successfully."; // M1
    
} catch (PDOException $e) {
    error_log("Analytics error: " . $e->getMessage());
    $analyticsMessage = "Unable to load analytics. Please try again."; // M5
    // Initialize empty arrays
    $totalUsers = $activeUsers7Days = $activeUsers30Days = 0;
    $loginData = $gamePlays = $completionRates = $scoreDistribution = $moodTrends = $moodDistribution = $questionnaireStats = [];
    $engagement = ['high_engagement' => 0, 'medium_engagement' => 0, 'low_engagement' => 0, 'no_engagement' => 0];
}

// Get list of games for filter
try {
    $stmt = $pdo->query("SELECT game_id, name, code FROM games ORDER BY name");
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $games = [];
}
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
                <a href="/pages/admin/content.php" class="nav-item">
                    <span class="icon">🎮</span> Manage Content
                </a>
                <a href="/pages/admin/analytics.php" class="nav-item active">
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
                <h1>System Analytics</h1>
                <p><?php echo htmlspecialchars($analyticsMessage); ?></p>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <form method="GET" class="filter-form">
                    <select name="range">
                        <option value="7" <?php echo $dateRange === '7' ? 'selected' : ''; ?>>Last 7 days</option>
                        <option value="30" <?php echo $dateRange === '30' ? 'selected' : ''; ?>>Last 30 days</option>
                        <option value="90" <?php echo $dateRange === '90' ? 'selected' : ''; ?>>Last 90 days</option>
                    </select>
                    <select name="game">
                        <option value="all">All Games</option>
                        <?php foreach ($games as $game): ?>
                        <option value="<?php echo htmlspecialchars($game['code']); ?>" <?php echo $gameFilter === $game['code'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($game['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="/pages/admin/analytics.php" class="btn btn-secondary">Reset</a>
                </form>
                <p class="info-message">Filters applied. Updating analytics… <!-- M6 --></p>
            </div>

            <!-- USER ACTIVITY ANALYTICS -->
            <div class="section-card">
                <h2>👥 User Activity Analytics</h2>
                <p class="info-message">User activity analytics displayed. <!-- M2 --></p>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-details">
                            <h3><?php echo number_format($totalUsers); ?></h3>
                            <p>Total Users</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📅</div>
                        <div class="stat-details">
                            <h3><?php echo number_format($activeUsers7Days); ?></h3>
                            <p>Active (7 days)</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📆</div>
                        <div class="stat-details">
                            <h3><?php echo number_format($activeUsers30Days); ?></h3>
                            <p>Active (30 days)</p>
                        </div>
                    </div>
                </div>

                <h3>Engagement Levels</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Engagement Level</th>
                            <th>Users</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="badge badge-success">High (10+ games)</span></td>
                            <td><?php echo number_format($engagement['high_engagement']); ?></td>
                            <td><?php echo $totalUsers > 0 ? round($engagement['high_engagement'] * 100 / $totalUsers, 1) : 0; ?>%</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-info">Medium (5-9 games)</span></td>
                            <td><?php echo number_format($engagement['medium_engagement']); ?></td>
                            <td><?php echo $totalUsers > 0 ? round($engagement['medium_engagement'] * 100 / $totalUsers, 1) : 0; ?>%</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-warning">Low (1-4 games)</span></td>
                            <td><?php echo number_format($engagement['low_engagement']); ?></td>
                            <td><?php echo $totalUsers > 0 ? round($engagement['low_engagement'] * 100 / $totalUsers, 1) : 0; ?>%</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-danger">None (0 games)</span></td>
                            <td><?php echo number_format($engagement['no_engagement']); ?></td>
                            <td><?php echo $totalUsers > 0 ? round($engagement['no_engagement'] * 100 / $totalUsers, 1) : 0; ?>%</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- GAME PERFORMANCE ANALYTICS -->
            <div class="section-card">
                <h2>🎮 Game Performance Analytics</h2>
                <p class="info-message">Game performance analytics displayed. <!-- M3 --></p>
                
                <h3>Most Played Games</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Game</th>
                            <th>Total Plays</th>
                            <th>Popularity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $maxPlays = !empty($gamePlays) ? max(array_column($gamePlays, 'play_count')) : 1;
                        foreach ($gamePlays as $game): 
                            $percentage = $maxPlays > 0 ? ($game['play_count'] * 100 / $maxPlays) : 0;
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($game['name']); ?></strong></td>
                            <td><?php echo number_format($game['play_count']); ?></td>
                            <td>
                                <div style="background: #e9ecef; height: 20px; border-radius: 4px; overflow: hidden;">
                                    <div style="background: #007bff; height: 100%; width: <?php echo $percentage; ?>%;"></div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <h3>Completion Rates</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Game</th>
                            <th>Total Sessions</th>
                            <th>Completed</th>
                            <th>Completion Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($completionRates as $rate): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($rate['name']); ?></strong></td>
                            <td><?php echo number_format($rate['total_sessions']); ?></td>
                            <td><?php echo number_format($rate['completed_sessions']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $rate['completion_rate'] >= 80 ? 'success' : ($rate['completion_rate'] >= 50 ? 'warning' : 'danger'); ?>">
                                    <?php echo number_format($rate['completion_rate'], 1); ?>%
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if (!empty($scoreDistribution)): ?>
                <h3>Score Distribution (Selected Game)</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Score Range</th>
                            <th>Number of Players</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($scoreDistribution as $dist): ?>
                        <tr>
                            <td><?php echo number_format($dist['score_range']); ?> - <?php echo number_format($dist['score_range'] + 9); ?></td>
                            <td><?php echo number_format($dist['count']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <!-- MOOD TRENDS -->
            <div class="section-card">
                <h2>😊 Global Mood Trends</h2>
                <p class="info-message">Mood trend analytics displayed. <!-- M4 --></p>
                <p class="help-text">C1: All data is aggregated and anonymized to protect user privacy.</p>
                
                <h3>Mood Distribution (Last 30 Days)</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Mood Level</th>
                            <th>Count</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalMoodLogs = array_sum(array_column($moodDistribution, 'count'));
                        foreach ($moodDistribution as $mood): 
                            $percentage = $totalMoodLogs > 0 ? ($mood['count'] * 100 / $totalMoodLogs) : 0;
                        ?>
                        <tr>
                            <td><span class="mood-indicator mood-<?php echo $mood['mood_value']; ?>"><?php echo $mood['mood_value']; ?>/5</span></td>
                            <td><?php echo number_format($mood['count']); ?></td>
                            <td><?php echo number_format($percentage, 1); ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <h3>Average Mood Trend</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Average Mood</th>
                            <th>Log Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($moodTrends as $trend): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($trend['mood_date'])); ?></td>
                            <td><span class="mood-indicator mood-<?php echo round($trend['avg_mood']); ?>"><?php echo number_format($trend['avg_mood'], 2); ?></span></td>
                            <td><?php echo number_format($trend['log_count']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- QUESTIONNAIRE STATS -->
            <div class="section-card">
                <h2>📝 Questionnaire Completion Stats</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Questionnaire</th>
                            <th>Completions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($questionnaireStats as $stat): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($stat['name']); ?></strong></td>
                            <td><?php echo number_format($stat['completion_count']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../../_footer.php'; ?>
    <script src="/assets/js/admin.js"></script>
</body>
</html>
