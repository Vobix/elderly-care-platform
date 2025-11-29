<?php
/**
 * PSQI Scoring Strategy
 * Pittsburgh Sleep Quality Index
 * 
 * Clinical validation: Sleep quality assessment
 * Reference: Buysse et al., 1989
 * 
 * Scoring: Sum component scores (0-3 per component, 0-21 total)
 * Note: PSQI typically uses 7 components derived from 19 items.
 * This simplified version sums item scores directly.
 * 
 * Interpretation:
 * - 0-5: Good sleep quality
 * - 6-10: Poor sleep quality
 * - 11-21: Severe sleep disturbance
 */

require_once __DIR__ . '/ScoringStrategy.php';

class PSQIStrategy implements ScoringStrategy {
    
    public function calculateScore(array $responses): int {
        // PSQI: Sum component scores
        // In full PSQI, responses are converted to 7 components (0-3 each)
        // This simplified version sums responses directly
        return array_sum(array_map('intval', $responses));
    }
    
    public function interpret(int $score): array {
        if ($score <= 5) {
            return [
                'level' => 'good',
                'color' => '#28a745',
                'emoji' => '😴',
                'message' => 'Good sleep quality',
                'recommendation' => 'Your sleep quality is healthy. Maintain good sleep habits: consistent schedule, comfortable environment, avoid screens before bed, and limit caffeine.',
                'severity' => 'none'
            ];
        } elseif ($score <= 10) {
            return [
                'level' => 'poor',
                'color' => '#ffc107',
                'emoji' => '😪',
                'message' => 'Poor sleep quality',
                'recommendation' => 'Consider improving sleep hygiene: maintain a regular sleep schedule, create a dark and quiet bedroom, avoid alcohol and caffeine before bed, and establish a relaxing bedtime routine.',
                'severity' => 'mild'
            ];
        } else {
            return [
                'level' => 'severe',
                'color' => '#dc3545',
                'emoji' => '😫',
                'message' => 'Severe sleep disturbance',
                'recommendation' => 'Consult a healthcare provider about your sleep difficulties. Chronic poor sleep affects physical and mental health. A provider can evaluate for sleep disorders and recommend treatment.',
                'severity' => 'severe'
            ];
        }
    }
    
    public function getMaxScore(): int {
        return 21; // 7 components × 3 points max
    }
    
    public function getName(): string {
        return "Pittsburgh Sleep Quality Index (PSQI)";
    }
    
    public function getReference(): string {
        return "Buysse, D.J., Reynolds, C.F., Monk, T.H., Berman, S.R., & Kupfer, D.J. (1989). The Pittsburgh Sleep Quality Index: A new instrument for psychiatric practice and research. Psychiatry Research, 28(2), 193-213.";
    }
}
