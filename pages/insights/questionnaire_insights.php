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

<link rel="stylesheet" href="/assets/css/questionnaire_insights.css">

<div class="insights-container">
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
        <a href="/pages/emotion/questionnaire_selection.php" class="btn">📝 Take New Assessment</a>
    </div>
</div>

<?php require_once __DIR__ . '/../../_footer.php'; ?>
