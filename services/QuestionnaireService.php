<?php
/**
 * QuestionnaireService - Business Logic Layer for Mental Health Questionnaires
 * 
 * Handles scoring and interpretation for 6 validated mental health assessments:
 * - WHO-5 (Well-Being Index)
 * - GDS-15 (Geriatric Depression Scale)
 * - PHQ-9 (Patient Health Questionnaire)
 * - GAD-7 (Generalized Anxiety Disorder Scale)
 * - PSS-4 (Perceived Stress Scale)
 * - PSQI (Pittsburgh Sleep Quality Index)
 * 
 * Phase 2: Enhanced with Strategy Pattern for validated clinical scoring
 */

require_once __DIR__ . '/strategies/ScoringStrategyFactory.php';

class QuestionnaireService {
    private $pdo;
    
    // Questionnaire type mapping
    const QUESTIONNAIRE_TYPES = [
        'wellbeing' => 'WHO-5',
        'depression' => 'GDS-15',
        'mood' => 'PHQ-9',
        'anxiety' => 'GAD-7',
        'stress' => 'PSS-4',
        'sleep' => 'PSQI'
    ];
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Score a questionnaire and save results
     * 
     * Uses Strategy Pattern - delegates to appropriate scoring strategy
     * based on questionnaire type (WHO-5, GDS-15, PHQ-9, etc.)
     * 
     * @param int $userId User ID
     * @param string $questionnaireType Type of questionnaire
     * @param array $responses User's responses
     * @param string $format Response format (deprecated - kept for compatibility)
     * @return array ['success' => bool, 'score' => int, 'interpretation' => array, 'response_id' => int]
     */
    public function scoreQuestionnaire($userId, $questionnaireType, $responses, $format = 'scale') {
        try {
            // Phase 2: Use Strategy Pattern for validated clinical scoring
            $strategy = ScoringStrategyFactory::create($questionnaireType);
            
            // Calculate score using validated algorithm
            $score = $strategy->calculateScore($responses);
            
            // Get clinical interpretation
            $interpretation = $strategy->interpret($score);
            
            // Add additional metadata
            $interpretation['max_score'] = $strategy->getMaxScore();
            $interpretation['percentage'] = round(($score / $strategy->getMaxScore()) * 100);
            $interpretation['questionnaire_name'] = $strategy->getName();
            $interpretation['reference'] = $strategy->getReference();
            
            // Save to database
            $responseId = $this->saveResult($userId, $questionnaireType, $responses, $score);
            
            return [
                'success' => true,
                'score' => $score,
                'interpretation' => $interpretation,
                'response_id' => $responseId
            ];
            
        } catch (Exception $e) {
            error_log("QuestionnaireService::scoreQuestionnaire error: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'An error occurred while processing your questionnaire. Please try again.',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Phase 2: Old methods removed - now using Strategy Pattern
     * - calculateScore() → Replaced by Strategy::calculateScore()
     * - interpretScore() → Replaced by Strategy::interpret()
     * - getMaxScore() → Replaced by Strategy::getMaxScore()
     * 
     * All clinical scoring logic now in validated strategy classes
     */
    
    /**
     * [DEPRECATED - Phase 1 method, kept for reference]
     * Calculate score based on questionnaire type
     * 
     * @param string $type Questionnaire type
     * @param array $responses User responses
     * @param string $format Response format
     * @return int Total score
     */
    private function calculateScore_OLD($type, $responses, $format) {
        $score = 0;
        
        // Different scoring methods based on type
        switch ($type) {
            case 'wellbeing': // WHO-5: Sum values × 4 (0-100 scale)
                $score = array_sum($responses) * 4;
                break;
                
            case 'depression': // GDS-15: Count "yes" answers
                if ($format === 'yes_no') {
                    foreach ($responses as $response) {
                        if ($response == 1) $score++; // 1 = yes
                    }
                } else {
                    $score = array_sum($responses);
                }
                break;
                
            case 'mood': // PHQ-9: Sum frequency values (0-3)
            case 'anxiety': // GAD-7: Sum frequency values (0-3)
            case 'stress': // PSS-4: Sum values
            case 'sleep': // PSQI: Sum component scores
                $score = array_sum($responses);
                break;
                
            default:
                $score = array_sum($responses);
        }
        
        return $score;
    }
    
    /**
     * [DEPRECATED - Phase 1 method, kept for reference]
     * Interpret score based on clinical thresholds
     * 
     * @param string $type Questionnaire type
     * @param int $score Total score
     * @param int $numQuestions Number of questions
     * @param string $format Response format
     * @return array Interpretation details
     */
    private function interpretScore_OLD($type, $score, $numQuestions, $format) {
        // Calculate percentage for general reference
        $maxScore = $this->getMaxScore_OLD($type, $numQuestions, $format);
        $percentage = $maxScore > 0 ? ($score / $maxScore) * 100 : 0;
        
        // Type-specific interpretations with clinical thresholds
        switch ($type) {
            case 'wellbeing': // WHO-5
                if ($score >= 50) {
                    return [
                        'level' => 'good',
                        'color' => '#28a745',
                        'emoji' => '🌟',
                        'message' => 'Good well-being',
                        'recommendation' => 'Keep up your positive mental health habits!'
                    ];
                } elseif ($score >= 28) {
                    return [
                        'level' => 'low',
                        'color' => '#ffc107',
                        'emoji' => '💭',
                        'message' => 'Low well-being',
                        'recommendation' => 'Consider self-care activities and reaching out to others.'
                    ];
                } else {
                    return [
                        'level' => 'poor',
                        'color' => '#dc3545',
                        'emoji' => '💙',
                        'message' => 'Poor well-being',
                        'recommendation' => 'Consider consulting a healthcare professional for support.'
                    ];
                }
                
            case 'depression': // GDS-15
                if ($score <= 4) {
                    return [
                        'level' => 'normal',
                        'color' => '#28a745',
                        'emoji' => '😊',
                        'message' => 'No significant depressive symptoms',
                        'recommendation' => 'Continue your healthy routines and social connections.'
                    ];
                } elseif ($score <= 9) {
                    return [
                        'level' => 'mild',
                        'color' => '#ffc107',
                        'emoji' => '💭',
                        'message' => 'Mild depression symptoms',
                        'recommendation' => 'Monitor your mood and consider talking to someone you trust.'
                    ];
                } else {
                    return [
                        'level' => 'moderate-severe',
                        'color' => '#dc3545',
                        'emoji' => '💙',
                        'message' => 'Moderate to severe depression symptoms',
                        'recommendation' => 'Please consult a healthcare professional for evaluation and support.'
                    ];
                }
                
            case 'mood': // PHQ-9
                if ($score <= 4) {
                    return [
                        'level' => 'minimal',
                        'color' => '#28a745',
                        'emoji' => '😊',
                        'message' => 'Minimal or no depression',
                        'recommendation' => 'Your mental health appears good. Keep up healthy habits.'
                    ];
                } elseif ($score <= 9) {
                    return [
                        'level' => 'mild',
                        'color' => '#17a2b8',
                        'emoji' => '💭',
                        'message' => 'Mild depression',
                        'recommendation' => 'Consider lifestyle changes like exercise, sleep, and social activities.'
                    ];
                } elseif ($score <= 14) {
                    return [
                        'level' => 'moderate',
                        'color' => '#ffc107',
                        'emoji' => '🤔',
                        'message' => 'Moderate depression',
                        'recommendation' => 'Consider consulting a healthcare provider for guidance.'
                    ];
                } elseif ($score <= 19) {
                    return [
                        'level' => 'moderately-severe',
                        'color' => '#fd7e14',
                        'emoji' => '💙',
                        'message' => 'Moderately severe depression',
                        'recommendation' => 'Please seek professional help from a mental health provider.'
                    ];
                } else {
                    return [
                        'level' => 'severe',
                        'color' => '#dc3545',
                        'emoji' => '❤️',
                        'message' => 'Severe depression',
                        'recommendation' => 'Urgent: Please contact a mental health professional immediately.'
                    ];
                }
                
            case 'anxiety': // GAD-7
                if ($score <= 4) {
                    return [
                        'level' => 'minimal',
                        'color' => '#28a745',
                        'emoji' => '😊',
                        'message' => 'Minimal anxiety',
                        'recommendation' => 'Your anxiety levels appear normal.'
                    ];
                } elseif ($score <= 9) {
                    return [
                        'level' => 'mild',
                        'color' => '#17a2b8',
                        'emoji' => '💭',
                        'message' => 'Mild anxiety',
                        'recommendation' => 'Practice relaxation techniques and stress management.'
                    ];
                } elseif ($score <= 14) {
                    return [
                        'level' => 'moderate',
                        'color' => '#ffc107',
                        'emoji' => '🤔',
                        'message' => 'Moderate anxiety',
                        'recommendation' => 'Consider consulting a healthcare provider for support.'
                    ];
                } else {
                    return [
                        'level' => 'severe',
                        'color' => '#dc3545',
                        'emoji' => '💙',
                        'message' => 'Severe anxiety',
                        'recommendation' => 'Please seek professional help for anxiety management.'
                    ];
                }
                
            case 'stress': // PSS-4
                if ($score <= 5) {
                    return [
                        'level' => 'low',
                        'color' => '#28a745',
                        'emoji' => '😊',
                        'message' => 'Low stress level',
                        'recommendation' => 'You\'re managing stress well.'
                    ];
                } elseif ($score <= 10) {
                    return [
                        'level' => 'moderate',
                        'color' => '#ffc107',
                        'emoji' => '💭',
                        'message' => 'Moderate stress level',
                        'recommendation' => 'Consider stress reduction activities like exercise or meditation.'
                    ];
                } else {
                    return [
                        'level' => 'high',
                        'color' => '#dc3545',
                        'emoji' => '💙',
                        'message' => 'High stress level',
                        'recommendation' => 'Consider strategies to manage stress or speak with a professional.'
                    ];
                }
                
            case 'sleep': // PSQI
                if ($score <= 5) {
                    return [
                        'level' => 'good',
                        'color' => '#28a745',
                        'emoji' => '😴',
                        'message' => 'Good sleep quality',
                        'recommendation' => 'Your sleep quality is healthy. Maintain good sleep habits.'
                    ];
                } elseif ($score <= 10) {
                    return [
                        'level' => 'poor',
                        'color' => '#ffc107',
                        'emoji' => '😪',
                        'message' => 'Poor sleep quality',
                        'recommendation' => 'Consider improving sleep hygiene (regular schedule, dark room, etc.).'
                    ];
                } else {
                    return [
                        'level' => 'severe',
                        'color' => '#dc3545',
                        'emoji' => '😫',
                        'message' => 'Severe sleep disturbance',
                        'recommendation' => 'Consult a healthcare provider about your sleep difficulties.'
                    ];
                }
                
            default:
                return [
                    'level' => 'unknown',
                    'color' => '#6c757d',
                    'emoji' => '📊',
                    'message' => 'Results recorded',
                    'recommendation' => 'Thank you for completing the questionnaire.'
                ];
        }
    }
    
    /**
     * [DEPRECATED - Phase 1 method, kept for reference]
     * Get maximum possible score
     * 
     * @param string $type Questionnaire type
     * @param int $numQuestions Number of questions
     * @param string $format Response format
     * @return int Maximum score
     */
    private function getMaxScore_OLD($type, $numQuestions, $format) {
        switch ($format) {
            case 'yes_no':
                return $numQuestions; // 0-1 per question
            case 'frequency':
                return $numQuestions * 3; // 0-3 per question
            case 'scale':
                if ($type === 'wellbeing') {
                    return 100; // WHO-5 is 0-100 scale
                }
                return $numQuestions * 5; // 0-5 per question
            default:
                return $numQuestions * 5;
        }
    }
    
    /**
     * Save questionnaire result to database
     * 
     * @param int $userId User ID
     * @param string $questionnaireType Questionnaire type
     * @param array $responses User responses
     * @param int $score Total score
     * @return int Response ID
     */
    private function saveResult($userId, $questionnaireType, $responses, $score) {
        // Get questionnaire ID
        $questionnaireId = $this->getQuestionnaireId($questionnaireType);
        
        if (!$questionnaireId) {
            throw new Exception("Unknown questionnaire type: $questionnaireType");
        }
        
        // Save response
        $stmt = $this->pdo->prepare("
            INSERT INTO questionnaire_responses 
            (user_id, questionnaire_id, responses, score, taken_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $userId,
            $questionnaireId,
            json_encode($responses),
            $score
        ]);
        
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Get questionnaire ID by type
     * 
     * @param string $questionnaireType Questionnaire type
     * @return int|false Questionnaire ID
     */
    private function getQuestionnaireId($questionnaireType) {
        $stmt = $this->pdo->prepare("
            SELECT questionnaire_id 
            FROM questionnaires 
            WHERE short_code = ?
        ");
        $stmt->execute([$questionnaireType]);
        return $stmt->fetchColumn();
    }
    
    /**
     * Get all questionnaire results for a user
     * 
     * @param int $userId User ID
     * @param string|null $questionnaireType Optional filter by type
     * @return array Questionnaire results
     */
    public function getResults($userId, $questionnaireType = null) {
        if ($questionnaireType) {
            $stmt = $this->pdo->prepare("
                SELECT 
                    qr.response_id,
                    qr.score,
                    qr.responses,
                    qr.taken_at,
                    q.short_code as questionnaire_type,
                    q.name as questionnaire_name
                FROM questionnaire_responses qr
                JOIN questionnaires q ON qr.questionnaire_id = q.questionnaire_id
                WHERE qr.user_id = ? AND q.short_code = ?
                ORDER BY qr.taken_at DESC
            ");
            $stmt->execute([$userId, $questionnaireType]);
        } else {
            $stmt = $this->pdo->prepare("
                SELECT 
                    qr.response_id,
                    qr.score,
                    qr.responses,
                    qr.taken_at,
                    q.short_code as questionnaire_type,
                    q.name as questionnaire_name
                FROM questionnaire_responses qr
                JOIN questionnaires q ON qr.questionnaire_id = q.questionnaire_id
                WHERE qr.user_id = ?
                ORDER BY qr.taken_at DESC
            ");
            $stmt->execute([$userId]);
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get latest questionnaire result
     * 
     * @param int $userId User ID
     * @param string $questionnaireType Questionnaire type
     * @return array|false Latest result or false
     */
    public function getLatestResult($userId, $questionnaireType) {
        $stmt = $this->pdo->prepare("
            SELECT 
                qr.response_id,
                qr.score,
                qr.responses,
                qr.taken_at,
                q.short_code as questionnaire_type,
                q.name as questionnaire_name
            FROM questionnaire_responses qr
            JOIN questionnaires q ON qr.questionnaire_id = q.questionnaire_id
            WHERE qr.user_id = ? AND q.short_code = ?
            ORDER BY qr.taken_at DESC
            LIMIT 1
        ");
        
        $stmt->execute([$userId, $questionnaireType]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
