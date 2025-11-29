<?php
/**
 * Scoring Strategy Factory
 * 
 * Creates appropriate scoring strategy based on questionnaire type.
 * Ensures each questionnaire uses its validated clinical algorithm.
 */

require_once __DIR__ . '/ScoringStrategy.php';
require_once __DIR__ . '/GDS15Strategy.php';
require_once __DIR__ . '/PHQ9Strategy.php';
require_once __DIR__ . '/GAD7Strategy.php';
require_once __DIR__ . '/WHO5Strategy.php';
require_once __DIR__ . '/PSS4Strategy.php';
require_once __DIR__ . '/PSQIStrategy.php';

class ScoringStrategyFactory {
    
    /**
     * Create appropriate scoring strategy for questionnaire type
     * 
     * @param string $questionnaireType Type of questionnaire
     * @return ScoringStrategy Scoring strategy instance
     * @throws Exception If questionnaire type is unknown
     */
    public static function create(string $questionnaireType): ScoringStrategy {
        return match(strtolower($questionnaireType)) {
            'wellbeing' => new WHO5Strategy(),
            'depression' => new GDS15Strategy(),
            'mood' => new PHQ9Strategy(),
            'anxiety' => new GAD7Strategy(),
            'stress' => new PSS4Strategy(),
            'sleep' => new PSQIStrategy(),
            default => throw new Exception("Unknown questionnaire type: $questionnaireType")
        };
    }
    
    /**
     * Get all available questionnaire types
     * 
     * @return array List of questionnaire types with names
     */
    public static function getAvailableTypes(): array {
        return [
            'wellbeing' => 'WHO-5 Well-Being Index',
            'depression' => 'Geriatric Depression Scale (GDS-15)',
            'mood' => 'Patient Health Questionnaire (PHQ-9)',
            'anxiety' => 'Generalized Anxiety Disorder Scale (GAD-7)',
            'stress' => 'Perceived Stress Scale (PSS-4)',
            'sleep' => 'Pittsburgh Sleep Quality Index (PSQI)'
        ];
    }
    
    /**
     * Check if questionnaire type is valid
     * 
     * @param string $questionnaireType Type to check
     * @return bool True if valid
     */
    public static function isValidType(string $questionnaireType): bool {
        return in_array(strtolower($questionnaireType), [
            'wellbeing', 'depression', 'mood', 'anxiety', 'stress', 'sleep'
        ]);
    }
}
