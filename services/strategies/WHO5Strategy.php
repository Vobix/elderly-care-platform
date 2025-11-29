<?php
/**
 * WHO-5 Scoring Strategy
 * WHO-5 Well-Being Index
 * 
 * Clinical validation: WHO well-being assessment tool
 * Reference: WHO, 1998
 * 
 * Scoring: Sum values (0-5 per question) × 4 = 0-100 scale
 * 
 * Interpretation:
 * - 50-100: Good well-being
 * - 28-49: Low well-being
 * - 0-27: Poor well-being (screen for depression)
 */

require_once __DIR__ . '/ScoringStrategy.php';

class WHO5Strategy implements ScoringStrategy {
    
    public function calculateScore(array $responses): int {
        // WHO-5: Sum values and multiply by 4 to get 0-100 scale
        $rawScore = array_sum(array_map('intval', $responses));
        return $rawScore * 4;
    }
    
    public function interpret(int $score): array {
        if ($score >= 50) {
            return [
                'level' => 'good',
                'color' => '#28a745',
                'emoji' => '🌟',
                'message' => 'Good well-being',
                'recommendation' => 'Excellent! Keep up your positive mental health habits. Continue activities that bring you joy, maintain social connections, and practice self-care.',
                'severity' => 'none'
            ];
        } elseif ($score >= 28) {
            return [
                'level' => 'low',
                'color' => '#ffc107',
                'emoji' => '💭',
                'message' => 'Low well-being',
                'recommendation' => 'Consider self-care activities, engaging in hobbies, connecting with friends and family, and physical exercise. If this persists, consider talking to a healthcare provider.',
                'severity' => 'mild'
            ];
        } else {
            return [
                'level' => 'poor',
                'color' => '#dc3545',
                'emoji' => '💙',
                'message' => 'Poor well-being',
                'recommendation' => 'Your score suggests poor well-being. Please consider consulting a healthcare professional for evaluation and support. This may indicate depression or other health concerns.',
                'severity' => 'severe'
            ];
        }
    }
    
    public function getMaxScore(): int {
        return 100; // 5 questions × 5 points × 4 multiplier
    }
    
    public function getName(): string {
        return "WHO-5 Well-Being Index";
    }
    
    public function getReference(): string {
        return "World Health Organization (1998). Wellbeing Measures in Primary Health Care/The Depcare Project. WHO Regional Office for Europe: Copenhagen.";
    }
}
