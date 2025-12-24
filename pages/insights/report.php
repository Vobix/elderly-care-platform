<?php
/**
 * Weekly Summary Report
 */

$page_title = "Weekly Report";
require_once __DIR__ . '/../account/auth.php';
require_once __DIR__ . '/../../_header.php';
require_once __DIR__ . '/../../database/functions.php';

$user_id = $_SESSION['user_id'];

// Get data for the last 7 days
global $pdo;

/* ---------------------------------------------------------
   Questionnaire Data (Baseline + Stats by Type)
   --------------------------------------------------------- */

// Baseline assessment (same idea as in questionnaire_insights.php)
$baseline = null;
try {
    $stmt = $pdo->prepare("
        SELECT ba.*, q.name AS questionnaire_name
        FROM baseline_assessments ba
        JOIN questionnaires q ON ba.questionnaire_id = q.questionnaire_id
        WHERE ba.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $baseline = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($baseline && $baseline['interpretation']) {
        $baseline['interpretation_data'] = json_decode($baseline['interpretation'], true);
    }
} catch (PDOException $e) {
    error_log("Baseline fetch error: " . $e->getMessage());
}

// Statistics by questionnaire type
$stats_by_type = [];
try {
    $stmt = $pdo->prepare("
        SELECT 
            q.type,
            q.name AS questionnaire_name,
            COUNT(*) AS times_taken,
            AVG(qr.score) AS avg_score,
            MIN(qr.score) AS best_score,
            MAX(qr.score) AS worst_score,
            MAX(qr.completed_at) AS last_taken
        FROM questionnaire_responses qr
        JOIN questionnaires q ON qr.questionnaire_id = q.questionnaire_id
        WHERE qr.user_id = ?
        GROUP BY q.type, q.name
    ");
    $stmt->execute([$user_id]);
    $stats_by_type = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Stats fetch error: " . $e->getMessage());
}

// 2. History (last 7 days only)
$recent_assessments = [];
try {
    $stmt = $pdo->prepare("
        SELECT 
            qr.score,
            qr.interpretation,
            qr.completed_at,
            q.name AS questionnaire_name
        FROM questionnaire_responses qr
        JOIN questionnaires q ON qr.questionnaire_id = q.questionnaire_id
        WHERE qr.user_id = ?
          AND qr.completed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ORDER BY qr.completed_at DESC
    ");
    $stmt->execute([$user_id]);
    
    // 🔴 THIS IS THE IMPORTANT PART:
    // Force associative arrays so 'questionnaire_name', 'score',
    // and 'completed_at' really exist as keys.
    $recent_assessments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($recent_assessments as &$row) {
        if (!empty($row['interpretation'])) {
            $row['interpretation_data'] = json_decode($row['interpretation'], true);
        } else {
            $row['interpretation_data'] = [];
        }
    }
} catch (PDOException $e) {
    error_log("Assessment fetch error: " . $e->getMessage());
}


/* ---------------------------------------------------------
   Mood & Game Data
   --------------------------------------------------------- */

// Mood data
$stmt = $pdo->prepare("
    SELECT * 
    FROM mood_logs 
    WHERE user_id = ? 
      AND entry_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
    ORDER BY entry_date DESC
");
$stmt->execute([$user_id]);
$week_moods = $stmt->fetchAll();

// Game data - join with games and game_scores tables
$stmt = $pdo->prepare("
    SELECT 
        gs.session_id,
        gs.user_id,
        gs.game_id,
        g.code AS game_type,
        gsc.score,
        gsc.created_at,
        TIMESTAMPDIFF(SECOND, gs.started_at, gs.ended_at) AS duration_seconds,
        gs.started_at
    FROM game_sessions gs
    JOIN games g ON gs.game_id = g.game_id
    JOIN game_scores gsc ON gs.session_id = gsc.session_id
    WHERE gs.user_id = ?
      AND gsc.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
");
$stmt->execute([$user_id]);
$week_games = $stmt->fetchAll();

// Calculate simple weekly aggregates
$avg_mood       = !empty($week_moods) ? array_sum(array_column($week_moods, 'mood_value')) / count($week_moods) : 0;
$avg_game_score = !empty($week_games) ? array_sum(array_column($week_games, 'score')) / count($week_games) : 0;
$total_game_time = !empty($week_games) ? array_sum(array_column($week_games, 'duration_seconds')) : 0;

// Mood correlation with games
$correlation_insight = "";
if (!empty($week_moods) && !empty($week_games)) {
    if ($avg_mood >= 4 && $avg_game_score >= 75) {
        $correlation_insight = "🌟 Great week! Your positive mood correlates with excellent game performance.";
    } elseif ($avg_mood < 3 && $avg_game_score < 60) {
        $correlation_insight = "💙 Lower mood may affect cognitive performance. Consider self-care activities.";
    } else {
        $correlation_insight = "👍 Keep maintaining balance between emotional wellness and cognitive activities.";
    }
}

/* ---------------------------------------------------------
   Build per-day mood + game series (for comparison chart)
   --------------------------------------------------------- */

// 1. Aggregate average mood per day
$moodSums   = [];
$moodCounts = [];

foreach ($week_moods as $row) {
    $d = $row['entry_date']; // DATE
    if (!isset($moodSums[$d])) {
        $moodSums[$d]   = 0;
        $moodCounts[$d] = 0;
    }
    $moodSums[$d]   += $row['mood_value'];
    $moodCounts[$d] += 1;
}

$moodAvgByDay = [];
foreach ($moodSums as $d => $sum) {
    $moodAvgByDay[$d] = $sum / $moodCounts[$d];
}

// 2. Aggregate average game score per day (ignore null scores)
$scoreSums   = [];
$scoreCounts = [];

foreach ($week_games as $row) {
    if ($row['score'] === null) continue;

    $d = (new DateTime($row['started_at']))->format('Y-m-d');
    if (!isset($scoreSums[$d])) {
        $scoreSums[$d]   = 0;
        $scoreCounts[$d] = 0;
    }
    $scoreSums[$d]   += $row['score'];
    $scoreCounts[$d] += 1;
}

$scoreAvgByDay = [];
foreach ($scoreSums as $d => $sum) {
    $scoreAvgByDay[$d] = $sum / $scoreCounts[$d];
}

// 3. Build aligned arrays for chart
$chart_labels = [];
$chart_mood   = [];
$chart_score  = [];

$endDate   = new DateTime();                    // today
$startDate = (clone $endDate)->modify('-6 days'); // 7-day range

$period = new DatePeriod(
    $startDate,
    new DateInterval('P1D'),
    (clone $endDate)->modify('+1 day')          // inclusive
);

foreach ($period as $date) {
    $d = $date->format('Y-m-d');
    $chart_labels[] = date('M j', strtotime($d));
    $chart_mood[]   = isset($moodAvgByDay[$d])  ? round($moodAvgByDay[$d], 2)  : null;
    $chart_score[]  = isset($scoreAvgByDay[$d]) ? round($scoreAvgByDay[$d], 2) : null;
}
?>

<link rel="stylesheet" href="/assets/css/report.css">

<div class="report-container">
    <div class="report-header">
        <h1 style="margin: 0;">📊 Your Weekly Report</h1>
        <p style="margin-top: 10px; font-size: 18px;">
            <?php echo date('F j', strtotime('-6 days')) . ' - ' . date('F j, Y'); ?>
        </p>
    </div>
    
    <div class="metric-grid">
        <div class="metric-card">
            <div>😊 Average Mood</div>
            <div class="metric-value"><?php echo round($avg_mood, 1); ?>/5</div>
            <div><?php echo count($week_moods); ?> entries</div>
        </div>
        
        <div class="metric-card">
            <div>🎮 Games Played</div>
            <div class="metric-value"><?php echo count($week_games); ?></div>
            <div>Avg Score: <?php echo round($avg_game_score); ?></div>
        </div>
        
        <div class="metric-card">
            <div>⏱️ Total Game Time</div>
            <div class="metric-value"><?php echo round($total_game_time / 60); ?></div>
            <div>minutes</div>
        </div>
    </div>
    
    <?php if ($correlation_insight): ?>
    <div class="insight-box">
        <h3 style="margin-top: 0;">💡 Key Insight</h3>
        <p style="font-size: 18px; line-height: 1.6;"><?php echo $correlation_insight; ?></p>
    </div>
    <?php endif; ?>
    
    <div class="section">
        <h2>📈 Weekly Trends</h2>

        <!-- Mood bar view -->
        <?php if (!empty($week_moods)): ?>
            <h3>Mood Entries</h3>
            <div style="display: flex; gap: 10px; align-items: flex-end; height: 150px; margin: 20px 0;">
                <?php foreach (array_reverse(array_slice($week_moods, 0, 7)) as $mood): ?>
                    <div style="flex: 1; background: linear-gradient(to top, #667eea, #764ba2); border-radius: 5px 5px 0 0; height: <?php echo ($mood['mood_value'] / 5) * 100; ?>%; min-height: 20px; position: relative;">
                        <div style="position: absolute; bottom: -25px; left: 50%; transform: translateX(-50%); font-size: 11px; white-space: nowrap;">
                            <?php echo date('M j', strtotime($mood['entry_date'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No mood data for this week.</p>
        <?php endif; ?>

        <!-- Compare patterns chart -->
        <?php if (!empty($week_moods) && !empty($week_games)): ?>
            <h3 style="margin-top: 40px;">Compare Mood & Game Performance</h3>
            <canvas id="compareChart" height="120"></canvas>
            <p style="margin-top: 10px; font-size: 14px;">
                <?php 
                    echo $correlation_insight 
                        ? htmlspecialchars($correlation_insight) 
                        : 'No clear pattern yet. Keep logging moods and playing games for better insights.';
                ?>
            </p>
        <?php else: ?>
            <p style="margin-top: 30px; font-size: 14px;">
                To compare patterns, please log mood entries and play at least one cognitive game this week.
            </p>
        <?php endif; ?>
    </div>

        <!-- 🧠 Mental Wellness Insights (from questionnaires) -->
    <div class="section">
        <h2>📊 Your Mental Wellness Insights</h2>
        <p style="color: #666; margin-bottom: 30px;">Track your mental health journey and understand your progress over time</p>

        <?php if ($baseline): ?>
        <!-- Baseline Assessment Section -->
        <div class="baseline-section">
            <h2>🎯 Your Baseline Assessment</h2>
            <p>Completed on <?php echo date('F j, Y', strtotime($baseline['completed_at'])); ?></p>
            
            <div class="baseline-grid">
                <div class="baseline-stat">
                    <h3><?php echo $baseline['score']; ?></h3>
                    <p><?php echo htmlspecialchars($baseline['questionnaire_name']); ?> Score</p>
                </div>
                <div class="baseline-stat">
                    <h3><?php echo ucfirst($baseline['risk_category']); ?></h3>
                    <p>Risk Level</p>
                </div>
                <div class="baseline-stat">
                    <h3><?php echo $baseline['interpretation_data']['percentage'] ?? '-'; ?>%</h3>
                    <p>Score Percentage</p>
                </div>
            </div>
            
            <?php if (isset($baseline['interpretation_data']['message'])): ?>
            <div style="background: rgba(255, 255, 255, 0.2); padding: 15px; border-radius: 8px; margin-top: 15px;">
                <strong>Clinical Interpretation:</strong><br>
                <?php echo htmlspecialchars($baseline['interpretation_data']['message']); ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Statistics by Questionnaire Type -->
        <?php if (!empty($stats_by_type)): ?>
        <h2>📈 Your Progress by Assessment Type</h2>
        <div class="stats-grid">
            <?php foreach ($stats_by_type as $stat): ?>
            <div class="stat-card">
                <h3><?php echo htmlspecialchars($stat['questionnaire_name']); ?></h3>
                <div class="stat-row">
                    <span>Times Taken:</span>
                    <strong><?php echo $stat['times_taken']; ?></strong>
                </div>
                <div class="stat-row">
                    <span>Average Score:</span>
                    <strong><?php echo round($stat['avg_score'], 1); ?></strong>
                </div>
                <div class="stat-row">
                    <span>Best Score:</span>
                    <strong style="color: #10b981;"><?php echo $stat['best_score']; ?></strong>
                </div>
                <div class="stat-row">
                    <span>Highest Score:</span>
                    <strong style="color: #ef4444;"><?php echo $stat['worst_score']; ?></strong>
                </div>
                <div class="stat-row">
                    <span>Last Taken:</span>
                    <strong><?php echo date('M j, Y', strtotime($stat['last_taken'])); ?></strong>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    
    <div class="section">
        <h2>🎯 Recommendations</h2>
        <ul style="line-height: 2; padding-left: 25px;">
            <?php if ($avg_mood >= 4): ?>
                <li>✅ Excellent mood maintenance! Keep up your current activities.</li>
            <?php else: ?>
                <li>💙 Try mood-boosting activities like games, social interaction, or exercise.</li>
            <?php endif; ?>
            
            <?php if (count($week_games) >= 5): ?>
                <li>🎮 Great cognitive engagement! You're actively training your brain.</li>
            <?php else: ?>
                <li>🧠 Consider playing more cognitive games to boost mental fitness.</li>
            <?php endif; ?>
            
            <?php if ($avg_game_score >= 75): ?>
                <li>🌟 Outstanding game performance! Challenge yourself with harder difficulties.</li>
            <?php elseif ($avg_game_score >= 60): ?>
                <li>👍 Good progress! Keep practicing to improve your scores.</li>
            <?php else: ?>
                <li>💪 Keep trying! Consistent practice will improve your performance.</li>
            <?php endif; ?>
            
            <li>📋 Take regular wellness questionnaires to track mental health trends.</li>
            <li>📝 Log your mood daily for more accurate insights.</li>
        </ul>
    </div>
    
    <div style="text-align: center; margin-top: 30px;">
        <a href="dashboard.php" class="btn btn-primary">📊 Back to Dashboard</a>
        <a href="../diary.php" class="btn btn-secondary">📔 View Diary</a>
    </div>
</div>

<!-- Chart.js & compare-pattern chart script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (function () {
        const labels    = <?php echo json_encode($chart_labels); ?>;
        const moodData  = <?php echo json_encode($chart_mood); ?>;
        const scoreData = <?php echo json_encode($chart_score); ?>;
        
        const ctx = document.getElementById('compareChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Average Mood (1–5)',
                        data: moodData,
                        yAxisID: 'yMood',
                        borderWidth: 2,
                        tension: 0.3,
                        pointRadius: 4
                    },
                    {
                        label: 'Average Game Score',
                        data: scoreData,
                        yAxisID: 'yScore',
                        borderWidth: 2,
                        tension: 0.3,
                        pointRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    yMood: {
                        position: 'left',
                        suggestedMin: 1,
                        suggestedMax: 5,
                        title: { display: true, text: 'Mood' }
                    },
                    yScore: {
                        position: 'right',
                        title: { display: true, text: 'Game Score' },
                        grid: { drawOnChartArea: false }
                    }
                }
            }
        });
    })();
</script>

<?php require_once __DIR__ . '/../../_footer.php'; ?>
