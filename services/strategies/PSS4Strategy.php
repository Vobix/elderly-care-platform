<?php
/**
 * PSS-4 Scoring Strategy
 * Perceived Stress Scale (4-item brief version)
 * 
 * Clinical validation: Brief stress assessment tool
 * Reference: Cohen et al., 1983
 * 
 * Scoring: Sum values (0-4 per question, 0-16 total)
 * Questions 2 and 3 are reverse-scored (handled in questionnaire design)
 * 
 * Interpretation:
 * - 0-5: Low stress
 * - 6-10: Moderate stress
 * - 11-16: High stress
 */

require_once __DIR__ . '/ScoringStrategy.php';

class PSS4Strategy implements ScoringStrategy {
    
    public function calculateScore(array $responses): int {
        // PSS-4: Sum all values (reverse scoring handled in questionnaire)
        return array_sum(array_map('intval', $responses));
    }
    
    public function interpret(int $score): array {
        if ($score <= 5) {
            return [
                'level' => 'low',
                'color' => '#28a745',
                'emoji' => '😊',
                'message' => 'Low stress level',
                'recommendation' => 'You\'re managing stress well. Continue your current coping strategies and maintain a healthy work-life balance.',
                'severity' => 'none'
            ];
        } elseif ($score <= 10) {
            return [
                'level' => 'moderate',
                'color' => '#ffc107',
                'emoji' => '💭',
                'message' => 'Moderate stress level',
                'recommendation' => 'Consider stress reduction activities such as regular exercise, meditation, deep breathing, time in nature, or hobbies. Ensure adequate sleep and social support.',
                'severity' => 'mild'
            ];
        } else {
            return [
                'level' => 'high',
                'color' => '#dc3545',
                'emoji' => '💙',
                'message' => 'High stress level',
                'recommendation' => 'Your stress level is high. Consider strategies to manage stress or speak with a mental health professional. Chronic stress can affect physical and mental health.',
                'severity' => 'moderate'
            ];
        }
    }
    
    public function getMaxScore(): int {
        return 16; // 4 questions × 4 points max
    }
    
    public function getName(): string {
        return "Perceived Stress Scale (PSS-4)";
    }
    
    public function getReference(): string {
        return "Cohen, S., Kamarck, T., & Mermelstein, R. (1983). A global measure of perceived stress. Journal of Health and Social Behavior, 24(4), 385-396.";
    }
}
