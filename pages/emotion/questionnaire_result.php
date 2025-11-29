<?php
/**
 * Questionnaire Results Page
 */

$page_title = "Questionnaire Results";
require_once __DIR__ . '/../account/auth.php';
require_once __DIR__ . '/../../_header.php';
require_once __DIR__ . '/../../database/config.php';
require_once __DIR__ . '/../../services/QuestionnaireService.php';

$user_id = $_SESSION['user_id'];

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
    
    // Use QuestionnaireService with Strategy Pattern (Phase 2)
    $questionnaireService = new QuestionnaireService($pdo);
    $result = $questionnaireService->scoreQuestionnaire(
        $user_id,
        $questionnaire_type,
        $responses,
        $format
    );
    
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
    
    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
        <a href="questionnaire.php" class="btn btn-primary">📝 Take Another</a>
        <a href="questionnaire_history.php" class="btn btn-primary">📊 View History</a>
        <a href="../diary.php" class="btn btn-secondary">📔 View Diary</a>
        <a href="../insights/dashboard.php" class="btn btn-success">📊 Dashboard</a>
    </div>
</div>

<?php require_once __DIR__ . '/../../_footer.php'; ?>
