<?php
/**
 * Questionnaire Page - GDS, Sleep, Stress assessments
 */

$page_title = "Health Questionnaire";
require_once __DIR__ . '/../account/auth.php';
require_once __DIR__ . '/../../_header.php';

$questionnaire_type = $_GET['type'] ?? 'wellness';

// Define questions based on validated mental health scales
// Questions are randomized from larger pools of validated assessment items

// GDS-15 (Geriatric Depression Scale) - Yes/No format
$gds_questions = [
    'Are you basically satisfied with your life?',
    'Have you dropped many of your activities and interests?',
    'Do you feel that your life is empty?',
    'Do you often get bored?',
    'Are you in good spirits most of the time?',
    'Are you afraid that something bad is going to happen to you?',
    'Do you feel happy most of the time?',
    'Do you often feel helpless?',
    'Do you prefer to stay at home, rather than going out and doing new things?',
    'Do you feel you have more problems with memory than most?',
    'Do you think it is wonderful to be alive now?',
    'Do you feel pretty worthless the way you are now?',
    'Do you feel full of energy?',
    'Do you feel that your situation is hopeless?',
    'Do you think that most people are better off than you are?',
    'Do you frequently get upset over little things?',
    'Do you frequently feel like crying?',
    'Do you have trouble concentrating?',
    'Do you enjoy getting up in the morning?',
    'Do you prefer to avoid social gatherings?'
];

// PHQ-9 (Patient Health Questionnaire) - Depression screening
$phq9_questions = [
    'Little interest or pleasure in doing things',
    'Feeling down, depressed, or hopeless',
    'Trouble falling or staying asleep, or sleeping too much',
    'Feeling tired or having little energy',
    'Poor appetite or overeating',
    'Feeling bad about yourself - or that you are a failure or have let yourself or your family down',
    'Trouble concentrating on things, such as reading the newspaper or watching television',
    'Moving or speaking so slowly that other people could have noticed. Or the opposite - being so fidgety or restless that you have been moving around a lot more than usual',
    'Thoughts that you would be better off dead, or of hurting yourself in some way'
];

// GAD-7 (Generalized Anxiety Disorder) - Anxiety screening
$gad7_questions = [
    'Feeling nervous, anxious, or on edge',
    'Not being able to stop or control worrying',
    'Worrying too much about different things',
    'Trouble relaxing',
    'Being so restless that it\'s hard to sit still',
    'Becoming easily annoyed or irritable',
    'Feeling afraid as if something awful might happen'
];

// PSS-4 (Perceived Stress Scale) - Stress assessment
$pss4_questions = [
    'In the last month, how often have you felt that you were unable to control the important things in your life?',
    'In the last month, how often have you felt confident about your ability to handle your personal problems?',
    'In the last month, how often have you felt that things were going your way?',
    'In the last month, how often have you felt difficulties were piling up so high that you could not overcome them?'
];

// WHO-5 (Well-Being Index) - General wellbeing
$who5_questions = [
    'I have felt cheerful and in good spirits',
    'I have felt calm and relaxed',
    'I have felt active and vigorous',
    'I woke up feeling fresh and rested',
    'My daily life has been filled with things that interest me'
];

// Sleep Quality Questions (Pittsburgh Sleep Quality Index inspired)
$sleep_questions = [
    'During the past month, how would you rate your sleep quality overall?',
    'During the past month, how often have you had trouble falling asleep?',
    'During the past month, how often have you had to take medicine to help you sleep?',
    'During the past month, how often have you had trouble staying awake during daytime activities?',
    'During the past month, how much of a problem has it been for you to keep up enthusiasm to get things done?'
];

$questionnaires = [
    'depression' => [
        'title' => 'Depression Screening (GDS-15 Based)',
        'description' => 'These questions are based on the validated Geriatric Depression Scale. Answer honestly about how you\'ve felt recently.',
        'questions' => array_rand(array_flip($gds_questions), 10), // Random 10 from GDS pool
        'format' => 'yes_no'
    ],
    'mood' => [
        'title' => 'Mood Assessment (PHQ-9 Based)',
        'description' => 'Over the last 2 weeks, how often have you been bothered by the following problems?',
        'questions' => array_rand(array_flip($phq9_questions), 7), // Random 7 from PHQ-9
        'format' => 'frequency'
    ],
    'anxiety' => [
        'title' => 'Anxiety Screening (GAD-7 Based)',
        'description' => 'Over the last 2 weeks, how often have you been bothered by the following?',
        'questions' => array_rand(array_flip($gad7_questions), 5), // Random 5 from GAD-7
        'format' => 'frequency'
    ],
    'stress' => [
        'title' => 'Stress Assessment (PSS-4)',
        'description' => 'These questions ask about your feelings and thoughts during the last month.',
        'questions' => $pss4_questions, // All 4 PSS questions
        'format' => 'frequency'
    ],
    'wellbeing' => [
        'title' => 'Well-Being Index (WHO-5)',
        'description' => 'Please indicate for each of the five statements which is closest to how you have been feeling over the last two weeks.',
        'questions' => $who5_questions, // All 5 WHO questions
        'format' => 'scale'
    ],
    'sleep' => [
        'title' => 'Sleep Quality Assessment (PSQI Based)',
        'description' => 'The following questions relate to your usual sleep habits during the past month.',
        'questions' => $sleep_questions, // All sleep questions
        'format' => 'frequency'
    ]
];

$current_q = $questionnaires[$questionnaire_type] ?? $questionnaires['wellbeing'];

// Define answer options based on format
$answer_formats = [
    'yes_no' => [
        ['value' => 1, 'label' => 'Yes'],
        ['value' => 0, 'label' => 'No']
    ],
    'frequency' => [
        ['value' => 0, 'label' => 'Not at all'],
        ['value' => 1, 'label' => 'Several days'],
        ['value' => 2, 'label' => 'More than half the days'],
        ['value' => 3, 'label' => 'Nearly every day']
    ],
    'scale' => [
        ['value' => 5, 'label' => 'All of the time'],
        ['value' => 4, 'label' => 'Most of the time'],
        ['value' => 3, 'label' => 'More than half the time'],
        ['value' => 2, 'label' => 'Less than half the time'],
        ['value' => 1, 'label' => 'Some of the time'],
        ['value' => 0, 'label' => 'At no time']
    ]
];

require_once __DIR__ . '/../../_header.php';

// Check if this is a baseline questionnaire
$isBaseline = isset($_GET['baseline']) && $_GET['baseline'] == '1';
?>

<link rel="stylesheet" href="/assets/css/questionnaire.css">

<?php if ($isBaseline): ?>
<style>
    .baseline-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 25px;
        border-radius: 10px;
        margin-bottom: 25px;
        text-align: center;
    }
    .baseline-header h2 {
        margin: 0 0 10px 0;
    }
    .question-card.unanswered {
        border: 2px solid #f44336;
        animation: shake 0.5s;
    }
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-10px); }
        75% { transform: translateX(10px); }
    }
</style>

<div class="baseline-header">
    <h2>📋 Baseline Mental Health Assessment</h2>
    <p>Please answer all questions honestly. Your responses are confidential and will help us provide better support.</p>
</div>

<script>
    // A2: User Navigates Away - prevent navigation during baseline
    window.addEventListener('beforeunload', function(e) {
        e.preventDefault();
        e.returnValue = 'You must complete the baseline assessment before accessing the dashboard.';
        return e.returnValue;
    });
</script>
<?php else: ?>
<div style="background: #fff3cd; border: 2px solid #ffc107; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
    <strong>ℹ️ Note:</strong> This questionnaire uses validated clinical assessment questions. Your responses are confidential and help track your mental wellness over time.
</div>
<?php endif; ?>
<div class="questionnaire-container">
    
    <h1 style="text-align: center;"><?php echo $current_q['title']; ?></h1>
    <p style="text-align: center; color: #666; margin-bottom: 30px;"><?php echo $current_q['description']; ?></p>
    
    <form method="POST" action="questionnaire_result.php" id="questionnaireForm">
        <input type="hidden" name="questionnaire_type" value="<?php echo $questionnaire_type; ?>">
        <input type="hidden" name="format" value="<?php echo $current_q['format']; ?>">
        <?php if (isset($_GET['baseline'])): ?>
        <input type="hidden" name="baseline" value="1">
        <?php endif; ?>
        
        <?php 
        $answers = $answer_formats[$current_q['format']];
        foreach ($current_q['questions'] as $index => $question): 
        ?>
            <div class="question-card" id="question-<?php echo $index; ?>">
                <div class="question"><?php echo ($index + 1) . '. ' . $question; ?></div>
                <div class="answer-options">
                    <?php foreach ($answers as $answer): ?>
                        <div class="answer-option">
                            <input type="radio" id="q<?php echo $index; ?>_<?php echo $answer['value']; ?>" name="q<?php echo $index; ?>" value="<?php echo $answer['value']; ?>" required>
                            <label for="q<?php echo $index; ?>_<?php echo $answer['value']; ?>"><?php echo $answer['label']; ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <div id="error-message" style="display: none; background: #ffebee; color: #c62828; padding: 15px; border-radius: 8px; margin-bottom: 15px; text-align: center; font-weight: bold;">
            ⚠️ Please answer all questions before submitting.
        </div>
        
        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 20px; font-size: 20px;">
            ✅ Submit Assessment
        </button>
    </form>
    
    <?php if (!$isBaseline): ?>
    <div style="text-align: center; margin-top: 20px;">
        <a href="../diary.php" class="btn btn-secondary">← Back to Diary</a>
    </div>
    <?php endif; ?>
</div>

<script>
// A1: Incomplete Answers - validate all questions answered
document.getElementById('questionnaireForm').addEventListener('submit', function(e) {
    const form = this;
    const questionCards = document.querySelectorAll('.question-card');
    let allAnswered = true;
    let firstUnanswered = null;
    
    questionCards.forEach((card, index) => {
        const radios = form.querySelectorAll(`input[name="q${index}"]`);
        const isAnswered = Array.from(radios).some(radio => radio.checked);
        
        card.classList.remove('unanswered');
        
        if (!isAnswered) {
            allAnswered = false;
            card.classList.add('unanswered');
            if (!firstUnanswered) {
                firstUnanswered = card;
            }
        }
    });
    
    if (!allAnswered) {
        e.preventDefault();
        document.getElementById('error-message').style.display = 'block';
        if (firstUnanswered) {
            firstUnanswered.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return false;
    }
    
    // Remove beforeunload handler when form is validly submitted
    window.removeEventListener('beforeunload', arguments.callee);
});
</script>
