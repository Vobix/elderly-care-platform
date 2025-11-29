<?php
/**
 * GDS-15 Scoring Strategy
 * Geriatric Depression Scale (15-item version)
 * 
 * Clinical validation: Validated screening tool for depression in elderly populations
 * Reference: Yesavage et al., 1982
 * 
 * Scoring: Count number of "yes" answers indicating depression (0-15)
 * 
 * Interpretation:
 * - 0-4: Normal (no depression)
 * - 5-9: Mild depression
 * - 10-15: Moderate to severe depression
 */

require_once __DIR__ . '/ScoringStrategy.php';

class GDS15Strategy implements ScoringStrategy {
    
    public function calculateScore(array $responses): int {
        // GDS-15: Count "yes" answers (typically coded as 1)
        // Some questions are reverse-scored but that's handled in questionnaire design
        $score = 0;
        
        foreach ($responses as $response) {
            if ($response == 1 || $response === 'yes') {
                $score++;
            }
        }
        
        return $score;
    }
    
    public function interpret(int $score): array {
        if ($score <= 4) {
            return [
                'level' => 'normal',
                'color' => '#28a745',
                'emoji' => '😊',
                'message' => 'No significant depressive symptoms',
                'recommendation' => 'Continue your healthy routines and social connections. Regular exercise, social activities, and hobbies support mental well-being.',
                'severity' => 'none'
            ];
        } elseif ($score <= 9) {
            return [
                'level' => 'mild',
                'color' => '#ffc107',
                'emoji' => '💭',
                'message' => 'Mild depression symptoms detected',
                'recommendation' => 'Consider monitoring your mood daily. Engage in activities you enjoy, maintain social connections, and talk to someone you trust. If symptoms persist, consult a healthcare provider.',
                'severity' => 'mild'
            ];
        } else {
            return [
                'level' => 'moderate-severe',
                'color' => '#dc3545',
                'emoji' => '💙',
                'message' => 'Moderate to severe depression symptoms',
                'recommendation' => 'Please consult a healthcare professional for a comprehensive evaluation and support. Depression is treatable, and professional help can make a significant difference.',
                'severity' => 'severe'
            ];
        }
    }
    
    public function getMaxScore(): int {
        return 15;
    }
    
    public function getName(): string {
        return "Geriatric Depression Scale (GDS-15)";
    }
    
    public function getReference(): string {
        return "Yesavage, J.A., et al. (1982). Development and validation of a geriatric depression screening scale: A preliminary report. Journal of Psychiatric Research, 17(1), 37-49.";
    }
}
