<?php
/**
 * Questionnaire Results Page
 */

$page_title = "Questionnaire Results";
require_once __DIR__ . '/../account/auth.php';
require_once __DIR__ . '/../../_header.php';
require_once __DIR__ . '/../../database/functions.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $questionnaire_type = $_POST['questionnaire_type'] ?? 'wellbeing';
    $format = $_POST['format'] ?? 'scale';
    
    // Calculate total score
    $total_score = 0;
    $answers = [];
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'q') === 0 && is_numeric(substr($key, 1))) {
            $answers[$key] = (int)$value;
            $total_score += (int)$value;
        }
    }
    
    // Save to database
    $answers_json = json_encode($answers);
    insertQuestionnaireResult($user_id, $questionnaire_type, $total_score, $answers_json);
    
    // Calculate max score based on format and number of questions
    $num_questions = count($answers);
    switch ($format) {
        case 'yes_no':
            $max_score = $num_questions; // 0-1 per question
            break;
        case 'frequency':
            $max_score = $num_questions * 3; // 0-3 per question
            break;
        case 'scale':
            $max_score = $num_questions * 5; // 0-5 per question
            break;
        default:
            $max_score = $num_questions * 5;
    }
    
    $percentage = $max_score > 0 ? ($total_score / $max_score) * 100 : 0;
    
    // Interpretation based on questionnaire type and clinical thresholds
    $interpretations = [
        'wellbeing' => [
            ['min' => 80, 'text' => 'Excellent well-being! You\'re thriving.', 'color' => '#28a745', 'emoji' => '🌟'],
            ['min' => 50, 'text' => 'Good well-being. Keep up your positive habits.', 'color' => '#20c997', 'emoji' => '👍'],
            ['min' => 30, 'text' => 'Moderate well-being. Consider self-care activities.', 'color' => '#ffc107', 'emoji' => '💭'],
            ['min' => 0, 'text' => 'Low well-being. Consider reaching out for support.', 'color' => '#fd7e14', 'emoji' => '💙']
        ],
        'depression' => [
            ['min' => 60, 'text' => 'Indicators suggest possible depression. Please consult a healthcare professional.', 'color' => '#dc3545', 'emoji' => '❤️'],
            ['min' => 40, 'text' => 'Some depressive symptoms detected. Monitor your mood and consider support.', 'color' => '#fd7e14', 'emoji' => '💙'],
            ['min' => 20, 'text' => 'Mild symptoms. Practice self-care and stay connected.', 'color' => '#ffc107', 'emoji' => '💭'],
            ['min' => 0, 'text' => 'Minimal symptoms. You\'re doing well!', 'color' => '#28a745', 'emoji' => '✨']
        ],
        'mood' => [
            ['min' => 60, 'text' => 'Severe symptoms. Please contact a mental health professional.', 'color' => '#dc3545', 'emoji' => '❤️'],
            ['min' => 40, 'text' => 'Moderate symptoms. Consider professional support.', 'color' => '#fd7e14', 'emoji' => '💙'],
            ['min' => 20, 'text' => 'Mild symptoms. Monitor and practice self-care.', 'color' => '#ffc107', 'emoji' => '💭'],
            ['min' => 0, 'text' => 'Minimal symptoms. You\'re managing well!', 'color' => '#28a745', 'emoji' => '😊']
        ],
        'anxiety' => [
            ['min' => 60, 'text' => 'Severe anxiety symptoms. Please seek professional help.', 'color' => '#dc3545', 'emoji' => '❤️'],
            ['min' => 40, 'text' => 'Moderate anxiety. Consider relaxation techniques and support.', 'color' => '#fd7e14', 'emoji' => '💙'],
            ['min' => 20, 'text' => 'Mild anxiety. Practice stress management.', 'color' => '#ffc107', 'emoji' => '🌿'],
            ['min' => 0, 'text' => 'Minimal anxiety. Keep up your coping strategies!', 'color' => '#28a745', 'emoji' => '�']
        ],
        'stress' => [
            ['min' => 70, 'text' => 'High stress levels. Take time to decompress and seek support.', 'color' => '#dc3545', 'emoji' => '🆘'],
            ['min' => 50, 'text' => 'Moderate stress. Practice stress-reduction techniques.', 'color' => '#fd7e14', 'emoji' => '🧘'],
            ['min' => 30, 'text' => 'Some stress present. Continue healthy coping mechanisms.', 'color' => '#ffc107', 'emoji' => '💆'],
            ['min' => 0, 'text' => 'Low stress levels. You\'re managing well!', 'color' => '#28a745', 'emoji' => '😊']
        ],
        'sleep' => [
            ['min' => 60, 'text' => 'Poor sleep quality. Consider sleep hygiene improvements or consult a doctor.', 'color' => '#dc3545', 'emoji' => '😴'],
            ['min' => 40, 'text' => 'Fair sleep quality. Try establishing a better bedtime routine.', 'color' => '#fd7e14', 'emoji' => '🌙'],
            ['min' => 20, 'text' => 'Good sleep quality. Minor improvements could help.', 'color' => '#ffc107', 'emoji' => '😊'],
            ['min' => 0, 'text' => 'Excellent sleep quality! Keep it up!', 'color' => '#28a745', 'emoji' => '✨']
        ]
    ];
    
    $scale = $interpretations[$questionnaire_type] ?? $interpretations['wellbeing'];
    $interpretation = "Assessment completed.";
    $color = '#6c757d';
    $emoji = '📋';
    
    foreach ($scale as $level) {
        if ($percentage >= $level['min']) {
            $interpretation = $level['text'];
            $color = $level['color'];
            $emoji = $level['emoji'];
            break;
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
    
    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
        <a href="questionnaire.php" class="btn btn-primary">📝 Take Another</a>
        <a href="questionnaire_history.php" class="btn btn-primary">📊 View History</a>
        <a href="../diary.php" class="btn btn-secondary">📔 View Diary</a>
        <a href="../insights/dashboard.php" class="btn btn-success">📊 Dashboard</a>
    </div>
</div>

<?php require_once __DIR__ . '/../../_footer.php'; ?>
