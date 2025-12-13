<?php
/**
 * Baseline Questionnaire Selection
 * First-time users must select and complete one baseline assessment
 */

session_start();
require_once __DIR__ . '/../account/auth.php';

// Check if user has already completed baseline
require_once __DIR__ . '/../../database/config.php';

try {
    $stmt = $pdo->prepare("SELECT baseline_id FROM baseline_assessments WHERE user_id = ? LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $hasBaseline = $stmt->fetchColumn();
    
    if ($hasBaseline) {
        // Already completed baseline, redirect to dashboard
        header("Location: /pages/insights/dashboard.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Baseline check error: " . $e->getMessage());
}

// Prevent navigation away from this page
$_SESSION['must_complete_baseline'] = true;

require_once __DIR__ . '/../../_header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Baseline Assessment - Elderly Care Platform</title>
    <link rel="stylesheet" href="/assets/css/questionnaire.css">
    <style>
        .selection-container {
            max-width: 900px;
            margin: 50px auto;
            padding: 30px;
        }
        
        .welcome-message {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .welcome-message h1 {
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .welcome-message p {
            font-size: 18px;
            color: #666;
            line-height: 1.6;
        }
        
        .assessment-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }
        
        .assessment-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 3px solid transparent;
        }
        
        .assessment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            border-color: #667eea;
        }
        
        .assessment-card.selected {
            border-color: #667eea;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .assessment-card h3 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .assessment-card .subtitle {
            font-size: 14px;
            opacity: 0.8;
            margin-bottom: 15px;
        }
        
        .assessment-card ul {
            list-style: none;
            padding: 0;
            margin: 15px 0;
        }
        
        .assessment-card li {
            padding: 8px 0;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .assessment-card.selected li {
            border-bottom-color: rgba(255,255,255,0.2);
        }
        
        .assessment-card li:last-child {
            border-bottom: none;
        }
        
        .start-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 18px;
            border-radius: 10px;
            cursor: pointer;
            margin: 30px auto;
            display: block;
            transition: all 0.3s ease;
        }
        
        .start-btn:hover:not(:disabled) {
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        
        .start-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .info-note {
            background: #f0f4ff;
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
            text-align: center;
            color: #555;
        }
        
        .info-note strong {
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="selection-container">
        <div class="welcome-message">
            <h1>🌟 Welcome to Your Mental Health Journey</h1>
            <p>To personalize your experience, please complete a brief mental health screening.<br>
            Choose the assessment that best fits your needs.</p>
        </div>
        
        <div class="assessment-cards">
            <div class="assessment-card" data-type="PHQ9" onclick="selectAssessment('PHQ9', this)">
                <h3>😔 PHQ-9</h3>
                <div class="subtitle">Depression Screening</div>
                <ul>
                    <li>📝 9 questions</li>
                    <li>⏱️ 2-3 minutes</li>
                    <li>🎯 Measures depression symptoms</li>
                    <li>✅ Most commonly used</li>
                </ul>
            </div>
            
            <div class="assessment-card" data-type="GAD7" onclick="selectAssessment('GAD7', this)">
                <h3>😰 GAD-7</h3>
                <div class="subtitle">Anxiety Screening</div>
                <ul>
                    <li>📝 7 questions</li>
                    <li>⏱️ 2 minutes</li>
                    <li>🎯 Measures anxiety symptoms</li>
                    <li>✅ Quick and reliable</li>
                </ul>
            </div>
            
            <div class="assessment-card" data-type="GDS15" onclick="selectAssessment('GDS15', this)">
                <h3>👴 GDS-15</h3>
                <div class="subtitle">Geriatric Depression</div>
                <ul>
                    <li>📝 15 questions</li>
                    <li>⏱️ 3-5 minutes</li>
                    <li>🎯 Designed for older adults</li>
                    <li>✅ Age-appropriate screening</li>
                </ul>
            </div>
        </div>
        
        <button class="start-btn" id="startBtn" disabled onclick="startAssessment()">
            🎮 Start Assessment
        </button>
        
        <div class="info-note">
            <strong>📌 Important:</strong> This is a one-time baseline assessment. Your responses are confidential 
            and will help us provide better support tailored to your needs.
        </div>
    </div>

    <script>
        let selectedType = null;
        
        function selectAssessment(type, card) {
            // Remove selected from all cards
            document.querySelectorAll('.assessment-card').forEach(c => {
                c.classList.remove('selected');
            });
            
            // Add selected to clicked card
            card.classList.add('selected');
            selectedType = type;
            
            // Enable start button
            document.getElementById('startBtn').disabled = false;
        }
        
        function startAssessment() {
            if (selectedType) {
                window.location.href = `/pages/emotion/questionnaire.php?type=${selectedType}&baseline=1`;
            }
        }
        
        // Warn if user tries to navigate away
        window.addEventListener('beforeunload', function(e) {
            e.preventDefault();
            e.returnValue = 'You must complete the baseline assessment before accessing the dashboard.';
            return e.returnValue;
        });
        
        // Prevent back button navigation
        history.pushState(null, null, location.href);
        window.onpopstate = function() {
            history.go(1);
        };
    </script>
</body>
</html>
