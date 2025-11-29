<?php
/**
 * GAD-7 Scoring Strategy
 * Generalized Anxiety Disorder Scale (7-item)
 * 
 * Clinical validation: Validated anxiety disorder screening tool
 * Reference: Spitzer et al., 2006
 * 
 * Scoring: Sum frequency values (0-3 per question, 0-21 total)
 * 0 = Not at all
 * 1 = Several days
 * 2 = More than half the days
 * 3 = Nearly every day
 * 
 * Interpretation:
 * - 0-4: Minimal anxiety
 * - 5-9: Mild anxiety
 * - 10-14: Moderate anxiety
 * - 15-21: Severe anxiety
 */

require_once __DIR__ . '/ScoringStrategy.php';

class GAD7Strategy implements ScoringStrategy {
    
    public function calculateScore(array $responses): int {
        // GAD-7: Sum all frequency values (0-3 per question)
        return array_sum(array_map('intval', $responses));
    }
    
    public function interpret(int $score): array {
        if ($score <= 4) {
            return [
                'level' => 'minimal',
                'color' => '#28a745',
                'emoji' => '😊',
                'message' => 'Minimal anxiety',
                'recommendation' => 'Your anxiety levels appear normal. Continue healthy stress management practices like regular exercise, adequate sleep, and relaxation techniques.',
                'severity' => 'none'
            ];
        } elseif ($score <= 9) {
            return [
                'level' => 'mild',
                'color' => '#17a2b8',
                'emoji' => '💭',
                'message' => 'Mild anxiety',
                'recommendation' => 'Practice relaxation techniques such as deep breathing, meditation, or progressive muscle relaxation. Regular physical activity and good sleep habits can help manage anxiety.',
                'severity' => 'mild'
            ];
        } elseif ($score <= 14) {
            return [
                'level' => 'moderate',
                'color' => '#ffc107',
                'emoji' => '🤔',
                'message' => 'Moderate anxiety',
                'recommendation' => 'Consider consulting a healthcare provider for support. Cognitive behavioral therapy (CBT) and other evidence-based treatments are effective for anxiety management.',
                'severity' => 'moderate'
            ];
        } else {
            return [
                'level' => 'severe',
                'color' => '#dc3545',
                'emoji' => '💙',
                'message' => 'Severe anxiety',
                'recommendation' => 'Please seek professional help for anxiety management. A mental health provider can offer therapy, coping strategies, and if needed, medication to help you feel better.',
                'severity' => 'severe'
            ];
        }
    }
    
    public function getMaxScore(): int {
        return 21; // 7 questions × 3 points max
    }
    
    public function getName(): string {
        return "Generalized Anxiety Disorder Scale (GAD-7)";
    }
    
    public function getReference(): string {
        return "Spitzer, R.L., Kroenke, K., Williams, J.B., & Löwe, B. (2006). A brief measure for assessing generalized anxiety disorder: The GAD-7. Archives of Internal Medicine, 166(10), 1092-1097.";
    }
}
