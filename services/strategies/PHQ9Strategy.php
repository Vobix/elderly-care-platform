<?php
/**
 * PHQ-9 Scoring Strategy
 * Patient Health Questionnaire (9-item depression scale)
 * 
 * Clinical validation: Validated primary care depression screening tool
 * Reference: Kroenke et al., 2001
 * 
 * Scoring: Sum frequency values (0-3 per question, 0-27 total)
 * 0 = Not at all
 * 1 = Several days
 * 2 = More than half the days
 * 3 = Nearly every day
 * 
 * Interpretation:
 * - 0-4: Minimal or no depression
 * - 5-9: Mild depression
 * - 10-14: Moderate depression
 * - 15-19: Moderately severe depression
 * - 20-27: Severe depression
 */

require_once __DIR__ . '/ScoringStrategy.php';

class PHQ9Strategy implements ScoringStrategy {
    
    public function calculateScore(array $responses): int {
        // PHQ-9: Sum all frequency values (0-3 per question)
        return array_sum(array_map('intval', $responses));
    }
    
    public function interpret(int $score): array {
        if ($score <= 4) {
            return [
                'level' => 'minimal',
                'color' => '#28a745',
                'emoji' => '😊',
                'message' => 'Minimal or no depression',
                'recommendation' => 'Your mental health appears good. Continue healthy habits like regular exercise, adequate sleep, balanced diet, and social connections.',
                'severity' => 'none'
            ];
        } elseif ($score <= 9) {
            return [
                'level' => 'mild',
                'color' => '#17a2b8',
                'emoji' => '💭',
                'message' => 'Mild depression',
                'recommendation' => 'Consider lifestyle changes: increase physical activity, improve sleep hygiene, engage in social activities, and practice stress management. Monitor your symptoms.',
                'severity' => 'mild'
            ];
        } elseif ($score <= 14) {
            return [
                'level' => 'moderate',
                'color' => '#ffc107',
                'emoji' => '🤔',
                'message' => 'Moderate depression',
                'recommendation' => 'Consider consulting a healthcare provider for guidance. Treatment options may include counseling, lifestyle changes, or medication. Early intervention is beneficial.',
                'severity' => 'moderate'
            ];
        } elseif ($score <= 19) {
            return [
                'level' => 'moderately-severe',
                'color' => '#fd7e14',
                'emoji' => '💙',
                'message' => 'Moderately severe depression',
                'recommendation' => 'Please seek professional help from a mental health provider. Treatment with counseling and/or medication is often recommended at this level.',
                'severity' => 'severe'
            ];
        } else {
            return [
                'level' => 'severe',
                'color' => '#dc3545',
                'emoji' => '❤️',
                'message' => 'Severe depression',
                'recommendation' => 'Urgent: Please contact a mental health professional immediately. If you have thoughts of self-harm, call a crisis helpline or go to the nearest emergency room.',
                'severity' => 'critical'
            ];
        }
    }
    
    public function getMaxScore(): int {
        return 27; // 9 questions × 3 points max
    }
    
    public function getName(): string {
        return "Patient Health Questionnaire (PHQ-9)";
    }
    
    public function getReference(): string {
        return "Kroenke, K., Spitzer, R.L., & Williams, J.B. (2001). The PHQ-9: Validity of a brief depression severity measure. Journal of General Internal Medicine, 16(9), 606-613.";
    }
}
