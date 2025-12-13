<?php
/**
 * Questionnaire Insights Page
 * Shows user's baseline assessment, risk levels, and questionnaire history
 */

$page_title = "Questionnaire Insights";
require_once __DIR__ . '/../account/auth.php';
require_once __DIR__ . '/../../_header.php';
require_once __DIR__ . '/../../database/config.php';

$user_id = $_SESSION['user_id'];

// Get baseline assessment
$baseline = null;
try {
    $stmt = $pdo->prepare("
        SELECT ba.*, q.name as questionnaire_name, q.short_code, q.type
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

// Get all questionnaire history
$questionnaire_history = [];
try {
    $stmt = $pdo->prepare("
        SELECT 
            qr.result_id,
            qr.score,
            qr.interpretation,
            qr.completed_at,
            q.name as questionnaire_name,
            q.short_code,
            q.type
        FROM questionnaire_responses qr
        JOIN questionnaires q ON qr.questionnaire_id = q.questionnaire_id
        WHERE qr.user_id = ?
        ORDER BY qr.completed_at DESC
    ");
    $stmt->execute([$user_id]);
    $questionnaire_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Decode interpretation for each
    foreach ($questionnaire_history as &$result) {
        if ($result['interpretation']) {
            $result['interpretation_data'] = json_decode($result['interpretation'], true);
        }
    }
} catch (PDOException $e) {
    error_log("Questionnaire history fetch error: " . $e->getMessage());
}

// Get statistics by questionnaire type
$stats_by_type = [];
try {
    $stmt = $pdo->prepare("
        SELECT 
            q.type,
            q.name as questionnaire_name,
            COUNT(*) as times_taken,
            AVG(qr.score) as avg_score,
            MIN(qr.score) as best_score,
            MAX(qr.score) as worst_score,
            MAX(qr.completed_at) as last_taken
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

// Helper function for risk badge
function getRiskBadge($category) {
    $badges = [
        'low' => '<span class="badge badge-success">✓ Low Risk</span>',
        'moderate' => '<span class="badge badge-warning">⚠ Moderate Risk</span>',
        'high' => '<span class="badge badge-danger">⚠⚠ High Risk</span>',
        'critical' => '<span class="badge badge-critical">⚠⚠⚠ Critical Risk</span>'
    ];
    return $badges[$category] ?? '<span class="badge">Unknown</span>';
}
?>

<link rel="stylesheet" href="/assets/css/questionnaire_history.css">
<style>
.insights-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.baseline-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 15px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.baseline-section h2 {
    margin: 0 0 10px 0;
    font-size: 28px;
}

.baseline-section p {
    opacity: 0.9;
    margin-bottom: 20px;
}

.baseline-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.baseline-stat {
    background: rgba(255, 255, 255, 0.2);
    padding: 20px;
    border-radius: 10px;
    backdrop-filter: blur(10px);
}

.baseline-stat h3 {
    margin: 0 0 5px 0;
    font-size: 32px;
    font-weight: bold;
}

.baseline-stat p {
    margin: 0;
    font-size: 14px;
    opacity: 0.9;
}

.badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    margin: 5px 5px 5px 0;
}

.badge-success {
    background: #10b981;
    color: white;
}

.badge-warning {
    background: #f59e0b;
    color: white;
}

.badge-danger {
    background: #ef4444;
    color: white;
}

.badge-critical {
    background: #dc2626;
    color: white;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-left: 4px solid #667eea;
}

.stat-card h3 {
    margin: 0 0 15px 0;
    color: #333;
    font-size: 16px;
}

.stat-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.stat-row:last-child {
    border-bottom: none;
}

.history-section {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.history-item {
    background: #f9fafb;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 15px;
    border-left: 4px solid #667eea;
    transition: transform 0.2s;
}

.history-item:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.history-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.history-header h3 {
    margin: 0;
    color: #333;
    font-size: 18px;
}

.history-date {
    color: #666;
    font-size: 14px;
}

.score-display {
    font-size: 36px;
    font-weight: bold;
    color: #667eea;
    margin: 10px 0;
}

.interpretation {
    background: white;
    padding: 15px;
    border-radius: 8px;
    margin-top: 10px;
}

.no-data {
    text-align: center;
    padding: 40px;
    color: #666;
}

.cta-section {
    background: #f0f4ff;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    margin-top: 20px;
}

.btn {
    display: inline-block;
    padding: 12px 24px;
    background: #667eea;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: background 0.3s;
}

.btn:hover {
    background: #5568d3;
}
</style>

<div class="insights-container">
    <h1>📊 Your Mental Wellness Insights</h1>
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

    <!-- Complete History -->
    <div class="history-section">
        <h2>📝 Complete Assessment History</h2>
        <p style="color: #666; margin-bottom: 20px;">All your questionnaire results in chronological order</p>

        <?php if (!empty($questionnaire_history)): ?>
            <?php foreach ($questionnaire_history as $result): ?>
            <div class="history-item">
                <div class="history-header">
                    <h3><?php echo htmlspecialchars($result['questionnaire_name']); ?></h3>
                    <span class="history-date"><?php echo date('F j, Y - g:i A', strtotime($result['completed_at'])); ?></span>
                </div>
                
                <div style="display: flex; gap: 20px; align-items: center;">
                    <div>
                        <div class="score-display"><?php echo $result['score']; ?></div>
                        <div style="color: #666; font-size: 14px;">Score</div>
                    </div>
                    
                    <?php if (isset($result['interpretation_data'])): ?>
                    <div style="flex: 1;">
                        <div>
                            <?php if (isset($result['interpretation_data']['emoji'])): ?>
                                <span style="font-size: 24px;"><?php echo $result['interpretation_data']['emoji']; ?></span>
                            <?php endif; ?>
                            <?php if (isset($result['interpretation_data']['percentage'])): ?>
                                <strong><?php echo $result['interpretation_data']['percentage']; ?>%</strong> of maximum score
                            <?php endif; ?>
                        </div>
                        
                        <?php if (isset($result['interpretation_data']['message'])): ?>
                        <div class="interpretation">
                            <strong>Interpretation:</strong><br>
                            <?php echo htmlspecialchars($result['interpretation_data']['message']); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-data">
                <h3>📋 No Assessment History Yet</h3>
                <p>You haven't completed any questionnaires yet. Start tracking your mental wellness today!</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Call to Action -->
    <div class="cta-section">
        <h3>Ready to track your progress?</h3>
        <p style="color: #666; margin-bottom: 15px;">Take a new assessment to see how you're doing</p>
        <a href="/pages/emotion/questionnaire.php" class="btn">📝 Take New Assessment</a>
    </div>
</div>

<?php require_once __DIR__ . '/../../_footer.php'; ?>
