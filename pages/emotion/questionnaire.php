<?php
/**
 * Questionnaire Page - GDS, Sleep, Stress assessments
 */

$page_title = "Health Questionnaire";
require_once __DIR__ . '/../account/auth.php';
require_once __DIR__ . '/../../_header.php';

$questionnaire_type = $_GET['type'] ?? 'wellbeing';

// Define questions based on validated mental health scales
require_once __DIR__ . '/questionnaire_definitions.php';

$current_q = $questionnaires[$questionnaire_type] ?? $questionnaires['wellbeing'];


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

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-10px);
            }

            75% {
                transform: translateX(10px);
            }
        }
    </style>

    <div class="baseline-header">
        <h2>📋 Baseline Mental Health Assessment</h2>
        <p>Please answer all questions honestly. Your responses are confidential and will help us provide better support.
        </p>
    </div>

    <script>
        // A2: User Navigates Away - prevent navigation during baseline
        window.addEventListener('beforeunload', function (e) {
            e.preventDefault();
            e.returnValue = 'You must complete the baseline assessment before accessing the dashboard.';
            return e.returnValue;
        });
    </script>
<?php else: ?>
    <div class="alert alert-warning">
        <strong>ℹ️ Note:</strong> This questionnaire uses validated clinical assessment questions. Your responses are
        confidential and help track your mental wellness over time.
    </div>
<?php endif; ?>
<div class="questionnaire-container">

    <h1 style="text-align: center;"><?php echo $current_q['title']; ?></h1>
    <p style="text-align: center; margin-bottom: 30px;"><?php echo $current_q['description']; ?></p>

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
                            <input type="radio" id="q<?php echo $index; ?>_<?php echo $answer['value']; ?>"
                                name="q<?php echo $index; ?>" value="<?php echo $answer['value']; ?>" required>
                            <label
                                for="q<?php echo $index; ?>_<?php echo $answer['value']; ?>"><?php echo $answer['label']; ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div id="error-message"
            style="display: none; background: #ffebee; color: #c62828; padding: 15px; border-radius: 8px; margin-bottom: 15px; text-align: center; font-weight: bold;">
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
    document.getElementById('questionnaireForm').addEventListener('submit', function (e) {
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