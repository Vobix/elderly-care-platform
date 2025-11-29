<?php
/**
 * Questionnaire History Page
 * Displays past questionnaire results with trends and detailed breakdowns
 */

$page_title = "Questionnaire History";
require_once __DIR__ . '/../account/auth.php';
require_once __DIR__ . '/../../database/functions.php';
require_once __DIR__ . '/../../_header.php';

$user_id = $_SESSION['user_id'];

// Get all questionnaire results
$all_results = getQuestionnaireResults($user_id);

// Group results by questionnaire type
$results_by_type = [];
foreach ($all_results as $result) {
    $type = $result['questionnaire_type'];
    if (!isset($results_by_type[$type])) {
        $results_by_type[$type] = [];
    }
    $results_by_type[$type][] = $result;
}

// Define questionnaire metadata
$questionnaire_meta = [
    'wellbeing' => [
        'name' => 'WHO-5 Well-Being Index',
        'icon' => '🌟',
        'color' => '#28a745',
        'description' => 'Measures overall well-being and quality of life',
        'max_score' => 25,
        'ranges' => [
            ['min' => 20, 'label' => 'Excellent', 'color' => '#28a745'],
            ['min' => 13, 'label' => 'Good', 'color' => '#20c997'],
            ['min' => 8, 'label' => 'Moderate', 'color' => '#ffc107'],
            ['min' => 0, 'label' => 'Low', 'color' => '#fd7e14']
        ]
    ],
    'depression' => [
        'name' => 'GDS-15 Depression Scale',
        'icon' => '💙',
        'color' => '#17a2b8',
        'description' => 'Screens for depression symptoms in older adults',
        'max_score' => 15,
        'ranges' => [
            ['min' => 10, 'label' => 'Severe', 'color' => '#dc3545'],
            ['min' => 5, 'label' => 'Moderate', 'color' => '#fd7e14'],
            ['min' => 0, 'label' => 'Minimal', 'color' => '#28a745']
        ]
    ],
    'anxiety' => [
        'name' => 'GAD-7 Anxiety Scale',
        'icon' => '😰',
        'color' => '#6f42c1',
        'description' => 'Measures anxiety symptoms and severity',
        'max_score' => 21,
        'ranges' => [
            ['min' => 15, 'label' => 'Severe', 'color' => '#dc3545'],
            ['min' => 10, 'label' => 'Moderate', 'color' => '#fd7e14'],
            ['min' => 5, 'label' => 'Mild', 'color' => '#ffc107'],
            ['min' => 0, 'label' => 'Minimal', 'color' => '#28a745']
        ]
    ],
    'stress' => [
        'name' => 'PSS-4 Stress Scale',
        'icon' => '😓',
        'color' => '#e83e8c',
        'description' => 'Assesses perceived stress levels',
        'max_score' => 16,
        'ranges' => [
            ['min' => 12, 'label' => 'High', 'color' => '#dc3545'],
            ['min' => 8, 'label' => 'Moderate', 'color' => '#ffc107'],
            ['min' => 0, 'label' => 'Low', 'color' => '#28a745']
        ]
    ],
    'sleep' => [
        'name' => 'PSQI Sleep Quality',
        'icon' => '😴',
        'color' => '#20c997',
        'description' => 'Evaluates sleep quality and patterns',
        'max_score' => 21,
        'ranges' => [
            ['min' => 10, 'label' => 'Poor', 'color' => '#dc3545'],
            ['min' => 5, 'label' => 'Fair', 'color' => '#ffc107'],
            ['min' => 0, 'label' => 'Good', 'color' => '#28a745']
        ]
    ]
];

// Function to get range label and color for a score
function getScoreRange($type, $score, $meta) {
    if (!isset($meta[$type])) return ['label' => 'N/A', 'color' => '#6c757d'];
    
    $ranges = $meta[$type]['ranges'];
    foreach ($ranges as $range) {
        if ($score >= $range['min']) {
            return $range;
        }
    }
    return ['label' => 'N/A', 'color' => '#6c757d'];
}

// Calculate statistics for each type
$statistics = [];
foreach ($results_by_type as $type => $results) {
    $scores = array_column($results, 'score');
    $statistics[$type] = [
        'count' => count($scores),
        'latest' => $scores[0] ?? 0,
        'average' => count($scores) > 0 ? round(array_sum($scores) / count($scores), 1) : 0,
        'best' => count($scores) > 0 ? (in_array($type, ['depression', 'anxiety', 'stress', 'sleep']) ? min($scores) : max($scores)) : 0,
        'worst' => count($scores) > 0 ? (in_array($type, ['depression', 'anxiety', 'stress', 'sleep']) ? max($scores) : min($scores)) : 0,
        'trend' => count($scores) >= 2 ? ($scores[0] - $scores[1]) : 0
    ];
}
?>

<link rel="stylesheet" href="/assets/css/questionnaire.css">
<style>
.history-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 30px 20px;
}

.page-header {
    text-align: center;
    margin-bottom: 40px;
}

.page-header h1 {
    font-size: 36px;
    color: #2d3748;
    margin-bottom: 10px;
}

.page-header p {
    font-size: 18px;
    color: #666;
}

.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.summary-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-left: 5px solid;
}

.summary-card h3 {
    font-size: 16px;
    color: #666;
    margin: 0 0 10px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.summary-card .score {
    font-size: 48px;
    font-weight: bold;
    margin: 10px 0;
}

.summary-card .label {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    color: white;
    margin-bottom: 10px;
}

.summary-card .stats {
    display: flex;
    justify-content: space-between;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #e0e0e0;
    font-size: 14px;
    color: #666;
}

.summary-card .stat-item {
    text-align: center;
}

.summary-card .stat-value {
    font-weight: bold;
    color: #2d3748;
    display: block;
    font-size: 16px;
}

.trend-indicator {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 14px;
    padding: 4px 10px;
    border-radius: 12px;
    margin-top: 8px;
}

.trend-up {
    background: #d4edda;
    color: #155724;
}

.trend-down {
    background: #f8d7da;
    color: #721c24;
}

.trend-stable {
    background: #e2e3e5;
    color: #383d41;
}

.history-section {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.history-section h2 {
    font-size: 24px;
    color: #2d3748;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.result-timeline {
    position: relative;
    padding-left: 40px;
}

.result-timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 3px;
    background: linear-gradient(to bottom, #667eea, #764ba2);
}

.timeline-item {
    position: relative;
    margin-bottom: 25px;
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.timeline-item:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -33px;
    top: 25px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: white;
    border: 4px solid;
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.timeline-date {
    font-size: 14px;
    color: #666;
}

.timeline-score {
    display: flex;
    align-items: center;
    gap: 15px;
}

.score-badge {
    font-size: 28px;
    font-weight: bold;
}

.score-label {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    color: white;
}

.timeline-details {
    color: #555;
    line-height: 1.6;
}

.view-details-btn {
    margin-top: 15px;
    padding: 8px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    transition: transform 0.2s;
}

.view-details-btn:hover {
    transform: scale(1.05);
}

.chart-container {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 10px;
    margin-top: 20px;
}

.chart {
    display: flex;
    align-items: flex-end;
    justify-content: space-around;
    height: 200px;
    padding: 20px 0;
    border-bottom: 2px solid #dee2e6;
}

.chart-bar {
    flex: 1;
    max-width: 60px;
    background: linear-gradient(to top, #667eea, #764ba2);
    border-radius: 8px 8px 0 0;
    position: relative;
    margin: 0 5px;
    transition: all 0.3s ease;
}

.chart-bar:hover {
    opacity: 0.8;
    transform: translateY(-5px);
}

.chart-bar-label {
    position: absolute;
    bottom: -25px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 11px;
    color: #666;
    white-space: nowrap;
}

.chart-bar-value {
    position: absolute;
    top: -25px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 14px;
    font-weight: bold;
    color: #2d3748;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.empty-state-icon {
    font-size: 72px;
    margin-bottom: 20px;
}

.empty-state h3 {
    font-size: 24px;
    color: #2d3748;
    margin-bottom: 10px;
}

.empty-state p {
    font-size: 16px;
    margin-bottom: 25px;
}

.take-questionnaire-btn {
    display: inline-block;
    padding: 12px 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: transform 0.2s;
}

.take-questionnaire-btn:hover {
    transform: scale(1.05);
}

.export-btn {
    float: right;
    padding: 10px 20px;
    background: #28a745;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
    display: inline-block;
}

.export-btn:hover {
    background: #218838;
}
</style>

<div class="history-container">
    <div class="page-header">
        <h1>📊 Questionnaire History</h1>
        <p>Track your mental health and well-being over time</p>
    </div>

    <?php if (empty($all_results)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">📋</div>
            <h3>No Questionnaires Yet</h3>
            <p>Start tracking your mental health by taking your first assessment</p>
            <a href="questionnaire.php" class="take-questionnaire-btn">Take Questionnaire</a>
        </div>
    <?php else: ?>
        
        <!-- Summary Cards -->
        <div class="summary-cards">
            <?php foreach ($results_by_type as $type => $results): 
                $meta = $questionnaire_meta[$type] ?? null;
                if (!$meta) continue;
                
                $stats = $statistics[$type];
                $range = getScoreRange($type, $stats['latest'], $questionnaire_meta);
                
                // Determine if lower is better (depression, anxiety, stress, sleep)
                $lower_is_better = in_array($type, ['depression', 'anxiety', 'stress', 'sleep']);
                $trend_direction = $stats['trend'] > 0 ? ($lower_is_better ? 'down' : 'up') : 
                                  ($stats['trend'] < 0 ? ($lower_is_better ? 'up' : 'down') : 'stable');
            ?>
                <div class="summary-card" style="border-left-color: <?php echo $meta['color']; ?>;">
                    <h3>
                        <span><?php echo $meta['icon']; ?></span>
                        <?php echo $meta['name']; ?>
                    </h3>
                    <div class="score" style="color: <?php echo $range['color']; ?>;">
                        <?php echo $stats['latest']; ?>
                    </div>
                    <span class="label" style="background: <?php echo $range['color']; ?>;">
                        <?php echo $range['label']; ?>
                    </span>
                    
                    <?php if ($stats['trend'] != 0): ?>
                        <div class="trend-indicator trend-<?php echo $trend_direction; ?>">
                            <?php 
                            if ($trend_direction == 'up') echo '↗️ Improving';
                            elseif ($trend_direction == 'down') echo '↘️ Declining';
                            else echo '→ Stable';
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="stats">
                        <div class="stat-item">
                            <span class="stat-value"><?php echo $stats['count']; ?></span>
                            <div>Taken</div>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value"><?php echo $stats['average']; ?></span>
                            <div>Average</div>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value"><?php echo $stats['best']; ?></span>
                            <div>Best</div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Detailed History by Type -->
        <?php foreach ($results_by_type as $type => $results): 
            $meta = $questionnaire_meta[$type] ?? null;
            if (!$meta) continue;
        ?>
            <div class="history-section">
                <h2>
                    <span><?php echo $meta['icon']; ?></span>
                    <?php echo $meta['name']; ?>
                    <a href="?export=<?php echo $type; ?>" class="export-btn">📥 Export</a>
                </h2>
                <p style="color: #666; margin-bottom: 20px;"><?php echo $meta['description']; ?></p>
                
                <!-- Trend Chart -->
                <div class="chart-container">
                    <h3 style="margin-bottom: 15px; color: #2d3748;">Score Trend</h3>
                    <div class="chart">
                        <?php 
                        $chart_results = array_slice(array_reverse($results), -10); // Last 10 results
                        $max_score = $meta['max_score'];
                        foreach ($chart_results as $result): 
                            $height = ($result['score'] / $max_score) * 100;
                            $date = date('M j', strtotime($result['taken_at']));
                        ?>
                            <div class="chart-bar" style="height: <?php echo $height; ?>%;" title="Score: <?php echo $result['score']; ?>">
                                <span class="chart-bar-value"><?php echo $result['score']; ?></span>
                                <span class="chart-bar-label"><?php echo $date; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Timeline of Results -->
                <div class="result-timeline" style="margin-top: 30px;">
                    <?php foreach ($results as $result): 
                        $range = getScoreRange($type, $result['score'], $questionnaire_meta);
                    ?>
                        <div class="timeline-item">
                            <div style="position: absolute; left: -33px; top: 25px; width: 16px; height: 16px; border-radius: 50%; background: white; border: 4px solid <?php echo $range['color']; ?>;"></div>
                            
                            <div class="timeline-header">
                                <div class="timeline-date">
                                    📅 <?php echo date('F j, Y \a\t g:i A', strtotime($result['taken_at'])); ?>
                                </div>
                                <div class="timeline-score">
                                    <span class="score-badge" style="color: <?php echo $range['color']; ?>;">
                                        <?php echo $result['score']; ?>/<?php echo $meta['max_score']; ?>
                                    </span>
                                    <span class="score-label" style="background: <?php echo $range['color']; ?>;">
                                        <?php echo $range['label']; ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="timeline-details">
                                <?php
                                $percentage = ($result['score'] / $meta['max_score']) * 100;
                                echo "Score represents " . round($percentage, 1) . "% of maximum score. ";
                                
                                // Add interpretation
                                if ($type == 'wellbeing') {
                                    if ($percentage >= 80) echo "Your well-being is excellent!";
                                    elseif ($percentage >= 50) echo "You're maintaining good well-being.";
                                    else echo "Consider self-care activities to improve well-being.";
                                } elseif ($type == 'depression') {
                                    if ($result['score'] >= 10) echo "Significant depressive symptoms detected. Consider professional support.";
                                    elseif ($result['score'] >= 5) echo "Moderate symptoms. Monitor your mood and practice self-care.";
                                    else echo "Minimal depressive symptoms.";
                                } elseif ($type == 'anxiety') {
                                    if ($result['score'] >= 15) echo "Severe anxiety symptoms. Professional consultation recommended.";
                                    elseif ($result['score'] >= 10) echo "Moderate anxiety. Consider relaxation techniques.";
                                    elseif ($result['score'] >= 5) echo "Mild anxiety symptoms.";
                                    else echo "Minimal anxiety symptoms.";
                                } elseif ($type == 'stress') {
                                    if ($result['score'] >= 12) echo "High stress levels. Focus on stress management techniques.";
                                    elseif ($result['score'] >= 8) echo "Moderate stress. Practice relaxation and self-care.";
                                    else echo "Low stress levels. You're managing well!";
                                } elseif ($type == 'sleep') {
                                    if ($result['score'] >= 10) echo "Poor sleep quality. Consider sleep hygiene improvements.";
                                    elseif ($result['score'] >= 5) echo "Fair sleep quality. Some room for improvement.";
                                    else echo "Good sleep quality!";
                                }
                                ?>
                            </div>
                            
                            <button class="view-details-btn" onclick="alert('Detailed responses:\n<?php echo str_replace(['"', "\n"], ['', ' '], $result['responses']); ?>')">
                                View Detailed Responses
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <div style="text-align: center; padding: 40px 0;">
            <a href="questionnaire.php" class="take-questionnaire-btn">📋 Take Another Questionnaire</a>
        </div>
        
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../_footer.php'; ?>
