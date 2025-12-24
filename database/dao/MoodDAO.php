<?php
/**
 * Mood Data Access Object
 * Phase 3: Handles all mood tracking database operations
 * Separates data access from business logic
 */

class MoodDAO {
    private $pdo;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Insert mood record
     * 
     * @param int $userId User ID
     * @param int $level Mood level (1-5)
     * @param string|null $notes Optional notes
     * @return int Inserted mood_id
     */
    public function insert($userId, $level, $notes = null) {
        $stmt = $this->pdo->prepare("
            INSERT INTO mood_logs (user_id, mood_value, notes, entry_date, created_at)
            VALUES (?, ?, ?, CURDATE(), NOW())
        ");

        $stmt->execute([$userId, $level, $notes]);
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Get today's mood for a user
     * 
     * @param int $userId User ID
     * @return array|false Mood record or false if none today
     */
    public function getTodaysMood($userId) {
        $stmt = $this->pdo->prepare("
            SELECT mood_id, mood_value, notes, created_at
            FROM mood_logs
            WHERE user_id = ? AND DATE(created_at) = CURDATE()
            ORDER BY created_at DESC
            LIMIT 1
        ");
        
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get recent mood history
     * C2: History Update Rule - newest first (ORDER BY DESC)
     * 
     * @param int $userId User ID
     * @param int $limit Number of records to retrieve
     * @return array Mood history
     */
    public function getRecentHistory($userId, $limit = 7) {
        $stmt = $this->pdo->prepare("
            SELECT 
                mood_id,
                mood_value,
                notes,
                created_at,
                DATE(created_at) as log_date
            FROM mood_logs
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ?
        ");
        
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get mood history for a date range
     * 
     * @param int $userId User ID
     * @param int $days Number of days to look back
     * @return array Mood history
     */
    public function getHistory($userId, $days = 30) {
        $stmt = $this->pdo->prepare("
            SELECT 
                mood_id,
                mood_value,
                notes,
                created_at,
                DATE(created_at) as log_date
            FROM mood_logs
            WHERE user_id = ? 
              AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            ORDER BY created_at DESC
        ");
        
        $stmt->execute([$userId, $days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get mood statistics for a user
     * 
     * @param int $userId User ID
     * @param int $days Number of days to analyze
     * @return array Statistics
     */
    public function getStatistics($userId, $days = 30) {
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total_logs,
                AVG(mood_value) as average_mood,
                MAX(mood_value) as best_mood,
                MIN(mood_value) as worst_mood
            FROM mood_logs
            WHERE user_id = ? 
              AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        
        $stmt->execute([$userId, $days]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Update existing mood record
     * 
     * @param int $moodId Mood ID
     * @param int $level New mood level
     * @param string|null $notes New notes
     * @return bool Success
     */
    public function update($moodId, $level, $notes = null) {
        $stmt = $this->pdo->prepare("
            UPDATE mood_logs
            SET mood_value = ?, notes = ?
            WHERE mood_id = ?
        ");
        
        return $stmt->execute([$level, $notes, $moodId]);
    }
    
    /**
     * Delete mood record
     * 
     * @param int $moodId Mood ID
     * @return bool Success
     */
    public function delete($moodId) {
        $stmt = $this->pdo->prepare("DELETE FROM mood_logs WHERE mood_id = ?");
        return $stmt->execute([$moodId]);
    }
}
