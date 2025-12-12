<?php
/**
 * Questionnaire Data Access Object
 * Phase 3: Handles all questionnaire database operations
 * Separates data access from business logic
 */

class QuestionnaireDAO {
    private $pdo;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get questionnaire ID by type
     * 
     * @param string $type Questionnaire type
     * @return int|false Questionnaire ID or false
     */
    public function getQuestionnaireId($type) {
        $stmt = $this->pdo->prepare("
            SELECT questionnaire_id 
            FROM questionnaires 
            WHERE type = ?
        ");
        $stmt->execute([$type]);
        return $stmt->fetchColumn();
    }
    
    /**
     * Get or create questionnaire by type
     * 
     * @param string $type Questionnaire type
     * @param string $name Questionnaire name
     * @return int Questionnaire ID
     */
    public function getOrCreateQuestionnaire($type, $name) {
        $id = $this->getQuestionnaireId($type);
        
        if ($id) {
            return $id;
        }
        
        // Create new questionnaire
        $stmt = $this->pdo->prepare("
            INSERT INTO questionnaires (type, name, created_at)
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$type, $name]);
        
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Save questionnaire result
     * 
     * @param int $userId User ID
     * @param int $questionnaireId Questionnaire ID
     * @param int $score Total score
     * @param array $answers User answers (will be JSON encoded)
     * @param array $interpretation Interpretation data
     * @return int Result ID
     */
    public function saveResult($userId, $questionnaireId, $score, $answers, $interpretation = []) {
        $stmt = $this->pdo->prepare("
            INSERT INTO questionnaire_responses 
            (user_id, questionnaire_id, score, answers, interpretation, completed_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $answersJson = is_array($answers) ? json_encode($answers) : $answers;
        $interpretationJson = is_array($interpretation) ? json_encode($interpretation) : $interpretation;
        
        $stmt->execute([
            $userId,
            $questionnaireId,
            $score,
            $answersJson,
            $interpretationJson
        ]);
        
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Get all results for a user
     * 
     * @param int $userId User ID
     * @param string|null $type Optional: filter by questionnaire type
     * @return array Results
     */
    public function getResults($userId, $type = null) {
        if ($type) {
            $stmt = $this->pdo->prepare("
                SELECT 
                    qr.result_id,
                    qr.score,
                    qr.answers,
                    qr.interpretation,
                    qr.completed_at,
                    q.type as questionnaire_type,
                    q.name as questionnaire_name
                FROM questionnaire_responses qr
                JOIN questionnaires q ON qr.questionnaire_id = q.questionnaire_id
                WHERE qr.user_id = ? AND q.type = ?
                ORDER BY qr.completed_at DESC
            ");
            $stmt->execute([$userId, $type]);
        } else {
            $stmt = $this->pdo->prepare("
                SELECT 
                    qr.result_id,
                    qr.score,
                    qr.answers,
                    qr.interpretation,
                    qr.completed_at,
                    q.type as questionnaire_type,
                    q.name as questionnaire_name
                FROM questionnaire_responses qr
                JOIN questionnaires q ON qr.questionnaire_id = q.questionnaire_id
                WHERE qr.user_id = ?
                ORDER BY qr.completed_at DESC
            ");
            $stmt->execute([$userId]);
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get latest result for a specific questionnaire type
     * 
     * @param int $userId User ID
     * @param string $type Questionnaire type
     * @return array|false Latest result or false
     */
    public function getLatestResult($userId, $type) {
        $stmt = $this->pdo->prepare("
            SELECT 
                qr.result_id,
                qr.score,
                qr.answers,
                qr.interpretation,
                qr.completed_at,
                q.type as questionnaire_type,
                q.name as questionnaire_name
            FROM questionnaire_responses qr
            JOIN questionnaires q ON qr.questionnaire_id = q.questionnaire_id
            WHERE qr.user_id = ? AND q.type = ?
            ORDER BY qr.completed_at DESC
            LIMIT 1
        ");
        
        $stmt->execute([$userId, $type]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get statistics for a questionnaire type
     * 
     * @param int $userId User ID
     * @param string $type Questionnaire type
     * @return array Statistics
     */
    public function getStatistics($userId, $type) {
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total_taken,
                AVG(qr.score) as average_score,
                MAX(qr.score) as best_score,
                MIN(qr.score) as worst_score,
                MAX(qr.completed_at) as last_taken
            FROM questionnaire_responses qr
            JOIN questionnaires q ON qr.questionnaire_id = q.questionnaire_id
            WHERE qr.user_id = ? AND q.type = ?
        ");
        
        $stmt->execute([$userId, $type]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Delete a result
     * 
     * @param int $resultId Result ID
     * @return bool Success
     */
    public function deleteResult($resultId) {
        $stmt = $this->pdo->prepare("DELETE FROM questionnaire_responses WHERE result_id = ?");
        return $stmt->execute([$resultId]);
    }
}
