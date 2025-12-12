<?php
/**
 * Questionnaire Results Page
 */

$page_title = "Questionnaire Results";
require_once __DIR__ . '/../account/auth.php';
require_once __DIR__ . '/../../_header.php';
require_once __DIR__ . '/../../database/config.php';
require_once __DIR__ . '/../../database/dao/QuestionnaireDAO.php';
require_once __DIR__ . '/../../services/QuestionnaireService.php';

$user_id = $_SESSION['user_id'];
$isBaseline = isset($_POST['baseline']) || isset($_GET['baseline']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $questionnaire_type = $_POST['questionnaire_type'] ?? 'wellbeing';
    $format = $_POST['format'] ?? 'scale';
    
    // Collect responses
    $responses = [];
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'q') === 0 && is_numeric(substr($key, 1))) {
            $responses[$key] = (int)$value;
        }
    }
    
    // Phase 3: Initialize DAO and pass to Service
    $questionnaireDAO = new QuestionnaireDAO($pdo);
    $questionnaireService = new QuestionnaireService($questionnaireDAO);
    $result = $questionnaireService->scoreQuestionnaire(
        $user_id,
        $questionnaire_type,
        $responses,
        $format
    );
    
    // Check if scoring was successful
    if (!$result['success']) {
        $error_message = $result['message'] ?? 'An error occurred while processing your questionnaire.';
        echo "<div class='alert alert-error'>{$error_message}</div>";
        require_once __DIR__ . '/../../_footer.php';
        exit;
    }
    
    // Extract interpretation data from service (Phase 2: Strategy Pattern)
    $total_score = $result['score'];
    $interpretation_data = $result['interpretation'];
    $max_score = $interpretation_data['max_score'];
    $percentage = $interpretation_data['percentage'];
    
    // Use validated clinical interpretation from strategy
    $interpretation = $interpretation_data['message'];
    $color = $interpretation_data['color'];
    $emoji = $interpretation_data['emoji'];
    $questionnaire_name = $interpretation_data['questionnaire_name'];
    $reference = $interpretation_data['reference'];
    
    // If this is a baseline assessment, save it and mark user as completed
    if ($isBaseline) {
        try {
            // Determine risk category based on score and questionnaire type
            $riskCategory = 'low';
            if ($questionnaire_type === 'PHQ9') {
                if ($total_score >= 20) $riskCategory = 'critical';
                elseif ($total_score >= 15) $riskCategory = 'high';
                elseif ($total_score >= 10) $riskCategory = 'moderate';
            } elseif ($questionnaire_type === 'GAD7') {
                if ($total_score >= 15) $riskCategory = 'critical';
                elseif ($total_score >= 10) $riskCategory = 'high';
                elseif ($total_score >= 5) $riskCategory = 'moderate';
            } elseif ($questionnaire_type === 'GDS15') {
                if ($total_score >= 11) $riskCategory = 'high';
                elseif ($total_score >= 5) $riskCategory = 'moderate';
            }
            
            // Get questionnaire ID
            $stmt = $pdo->prepare("SELECT questionnaire_id FROM questionnaires WHERE type = ?");
            $stmt->execute([$questionnaire_type]);
            $questionnaireId = $stmt->fetchColumn();
            
            // Save baseline assessment
            $stmt = $pdo->prepare("
                INSERT INTO baseline_assessments 
                (user_id, questionnaire_id, score, risk_category, interpretation, responses) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $user_id,
                $questionnaireId,
                $total_score,
                $riskCategory,
                json_encode($interpretation_data),
                json_encode($responses)
            ]);
            
            $baselineId = $pdo->lastInsertId();
            
            // Update user to mark baseline assessment as completed
            $stmt = $pdo->prepare("
                UPDATE users 
                SET has_completed_initial_assessment = 1, 
                    baseline_assessment_id = ? 
                WHERE user_id = ?
            ");
            $stmt->execute([$baselineId, $user_id]);
            
        } catch (PDOException $e) {
            error_log("Baseline assessment save error: " . $e->getMessage());
        }
    }
} else {
    header("Location: questionnaire.php");
    exit();
}
?>

<style>
    .result-container { max-width: 700px; margin: 0 auto; text-align: center; }
    .result-card { background: white; padding: 50px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); margin: 30px 0; }
    .high-contrast .result-card { background: #1a1a1a; border: 3px solid #ffff00; }
    .score-circle { width: 200px; height: 200px; margin: 30px auto; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 48px; font-weight: bold; color: white; position: relative; }
    .emoji { font-size: 96px; margin: 20px 0; }
</style>

<div class="result-container">
    <h1>📋 Questionnaire Results</h1>
    
    <div class="result-card">
        <div class="emoji"><?php echo $emoji; ?></div>
        
        <div class="score-circle" style="background: <?php echo $color; ?>;">
            <?php echo round($percentage); ?>%
        </div>
        
        <h2 style="margin: 30px 0;"><?php echo $interpretation; ?></h2>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 30px 0;">
            <strong>Your Score:</strong> <?php echo $total_score; ?> / <?php echo $max_score; ?><br>
            <strong>Questions:</strong> <?php echo count($answers); ?>
        </div>
        
        <p style="color: #666; line-height: 1.8;">
            Your responses have been saved. Track your progress over time to see how you're doing.
            Remember, these assessments are tools for self-awareness, not medical diagnoses.
        </p>
    </div>
    
    <?php if ($isBaseline): ?>
    <div style="background: #d4edda; color: #155724; padding: 20px; border-radius: 10px; margin: 30px 0; border: 2px solid #c3e6cb;">
        <h3>✅ Baseline Assessment Complete!</h3>
        <p>Thank you for completing your initial wellness assessment. This will help us track your progress over time and provide personalized recommendations.</p>
        <p><strong>Risk Category:</strong> <span style="text-transform: uppercase;"><?php echo $riskCategory; ?></span></p>
    </div>
    <?php endif; ?>
    
    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
        <?php if ($isBaseline): ?>
        <a href="../insights/dashboard.php" class="btn btn-primary">🎉 Get Started</a>
        <?php else: ?>
        <a href="questionnaire.php" class="btn btn-primary">📝 Take Another</a>
        <a href="questionnaire_history.php" class="btn btn-primary">📊 View History</a>
        <a href="../diary.php" class="btn btn-secondary">📔 View Diary</a>
        <a href="../insights/dashboard.php" class="btn btn-success">📊 Dashboard</a>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../_footer.php'; ?>
