<?php
/**
 * Questionnaire Selection Page
 * Shows available mental health assessments for users to choose from
 */

$page_title = "Select Assessment";
require_once __DIR__ . '/../account/auth.php';
require_once __DIR__ . '/../../_header.php';
?>

<link rel="stylesheet" href="/assets/css/questionnaire.css">

<div style="max-width: 900px; margin: 0 auto; padding: 20px;">
    <h1 style="text-align: center; font-size: 36px; margin-bottom: 10px;">📋 Mental Health Assessments</h1>
    <p style="text-align: center; font-size: 18px; color: #666; margin-bottom: 40px;">
        Take validated clinical questionnaires to track your mental wellness over time.
    </p>
    
    <div class="alert" style="background: #fff3cd; border: 2px solid #ffc107; padding: 20px; border-radius: 10px; margin-bottom: 30px;">
        <strong>ℹ️ Note:</strong> This questionnaire uses validated clinical assessment questions. Your responses are confidential and help track your mental wellness over time.
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
        <!-- Well-Being Assessment -->
        <a href="questionnaire.php?type=wellbeing" class="assessment-card" style="background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%); text-decoration: none; color: white;">
            <div style="font-size: 48px; margin-bottom: 15px;">😊</div>
            <h3 style="margin: 0 0 10px 0; font-size: 22px;">Well-Being (WHO-5)</h3>
            <p style="margin: 0; opacity: 0.9; font-size: 14px;">Measures overall well-being and quality of life over the past two weeks.</p>
            <div style="margin-top: 15px; font-size: 13px; opacity: 0.8;">
                <strong>📝 5 questions</strong> • ⏱️ 2 minutes
            </div>
        </a>

        <!-- Depression Screening -->
        <a href="questionnaire.php?type=depression" class="assessment-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); text-decoration: none; color: white;">
            <div style="font-size: 48px; margin-bottom: 15px;">🧠</div>
            <h3 style="margin: 0 0 10px 0; font-size: 22px;">Depression Screening (GDS-15)</h3>
            <p style="margin: 0; opacity: 0.9; font-size: 14px;">Screens for depression symptoms in older adults.</p>
            <div style="margin-top: 15px; font-size: 13px; opacity: 0.8;">
                <strong>📝 15 questions</strong> • ⏱️ 3-5 minutes
            </div>
        </a>

        <!-- Mood Assessment -->
        <a href="questionnaire.php?type=mood" class="assessment-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); text-decoration: none; color: white;">
            <div style="font-size: 48px; margin-bottom: 15px;">💙</div>
            <h3 style="margin: 0 0 10px 0; font-size: 22px;">Mood Assessment (PHQ-9)</h3>
            <p style="margin: 0; opacity: 0.9; font-size: 14px;">Patient Health Questionnaire for mood and depression screening.</p>
            <div style="margin-top: 15px; font-size: 13px; opacity: 0.8;">
                <strong>📝 9 questions</strong> • ⏱️ 2-3 minutes
            </div>
        </a>

        <!-- Anxiety Screening -->
        <a href="questionnaire.php?type=anxiety" class="assessment-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); text-decoration: none; color: white;">
            <div style="font-size: 48px; margin-bottom: 15px;">😰</div>
            <h3 style="margin: 0 0 10px 0; font-size: 22px;">Anxiety Screening (GAD-7)</h3>
            <p style="margin: 0; opacity: 0.9; font-size: 14px;">Generalized Anxiety Disorder scale measures anxiety symptoms.</p>
            <div style="margin-top: 15px; font-size: 13px; opacity: 0.8;">
                <strong>📝 7 questions</strong> • ⏱️ 2 minutes
            </div>
        </a>

        <!-- Stress Assessment -->
        <a href="questionnaire.php?type=stress" class="assessment-card" style="background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); text-decoration: none; color: #333;">
            <div style="font-size: 48px; margin-bottom: 15px;">😓</div>
            <h3 style="margin: 0 0 10px 0; font-size: 22px;">Stress Assessment (PSS-4)</h3>
            <p style="margin: 0; opacity: 0.9; font-size: 14px;">Perceived Stress Scale assesses your stress levels.</p>
            <div style="margin-top: 15px; font-size: 13px; opacity: 0.8;">
                <strong>📝 4 questions</strong> • ⏱️ 1-2 minutes
            </div>
        </a>

        <!-- Sleep Quality -->
        <a href="questionnaire.php?type=sleep" class="assessment-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); text-decoration: none; color: white;">
            <div style="font-size: 48px; margin-bottom: 15px;">😴</div>
            <h3 style="margin: 0 0 10px 0; font-size: 22px;">Sleep Quality (PSQI)</h3>
            <p style="margin: 0; opacity: 0.9; font-size: 14px;">Pittsburgh Sleep Quality Index evaluates your sleep patterns.</p>
            <div style="margin-top: 15px; font-size: 13px; opacity: 0.8;">
                <strong>📝 6 questions</strong> • ⏱️ 2-3 minutes
            </div>
        </a>
    </div>

    <div style="text-align: center; margin-top: 40px;">
        <a href="../insights/questionnaire_insights.php" class="btn btn-secondary">← View History</a>
    </div>
</div>

<style>
.assessment-card {
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.3s;
    display: block;
}

.assessment-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}
</style>

<?php require_once __DIR__ . '/../../_footer.php'; ?>