<?php
/**
 * Game Data Access Object
 * Phase 3: Handles all game-related database operations
 * Separates data access from business logic
 */

class GameDAO {
    private $pdo;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get or create game by code
     * 
     * @param string $gameCode Game code
     * @param string $gameName Game name
     * @return int Game ID
     */
    public function getOrCreateGame($gameCode, $gameName) {
        // Try to get existing game
        $gameId = $this->getGameIdByCode($gameCode);
        
        if ($gameId) {
            return $gameId;
        }
        
        // Create new game entry
        $stmt = $this->pdo->prepare("
            INSERT INTO games (name, code, description)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$gameName, $gameCode, 'Cognitive training game']);
        
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Get game ID by code
     * 
     * @param string $gameCode Game code
     * @return int|false Game ID or false
     */
    public function getGameIdByCode($gameCode) {
        $stmt = $this->pdo->prepare("SELECT game_id FROM games WHERE code = ?");
        $stmt->execute([$gameCode]);
        return $stmt->fetchColumn();
    }
    
    /**
     * Create game session
     * 
     * @param int $userId User ID
     * @param int $gameId Game ID
     * @param string|null $difficulty Difficulty level
     * @return int Session ID
     */
    public function createSession($userId, $gameId, $difficulty = null) {
        $stmt = $this->pdo->prepare("
            INSERT INTO game_sessions (user_id, game_id, difficulty, started_at, ended_at)
            VALUES (?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([$userId, $gameId, $difficulty]);
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Save game score
     * 
     * @param int $sessionId Session ID
     * @param int $score Score value
     * @param array $details Additional details (JSON)
     * @return bool Success
     */
    public function saveScore($sessionId, $score, $details = []) {
        $stmt = $this->pdo->prepare("
            INSERT INTO game_scores (session_id, score, details, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        
        $detailsJson = json_encode($details);
        return $stmt->execute([$sessionId, $score, $detailsJson]);
    }
    
    /**
     * Get user game stats
     * 
     * @param int $userId User ID
     * @param int $gameId Game ID
     * @return array|null Stats array or null
     */
    public function getStats($userId, $gameId) {
        $stmt = $this->pdo->prepare("
            SELECT times_played, best_score, total_score, average_score
            FROM user_game_stats
            WHERE user_id = ? AND game_id = ?
        ");
        $stmt->execute([$userId, $gameId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all user stats for all games
     * 
     * @param int $userId User ID
     * @return array All game stats
     */
    public function getAllStats($userId) {
        $stmt = $this->pdo->prepare("
            SELECT 
                g.code as game_type,
                g.name as game_name,
                ugs.times_played as games_played,
                ugs.best_score,
                ugs.average_score as avg_score
            FROM user_game_stats ugs
            JOIN games g ON ugs.game_id = g.game_id
            WHERE ugs.user_id = ?
            ORDER BY ugs.times_played DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Update user game stats
     * Implements C3: Stats Update Formula
     * 
     * @param int $userId User ID
     * @param int $gameId Game ID
     * @param int $score New score
     * @return bool Success
     */
    public function updateStats($userId, $gameId, $score) {
        // Check if stats exist
        $existingStats = $this->getStats($userId, $gameId);
        
        if ($existingStats) {
            // Update existing stats
            $newTimesPlayed = $existingStats['times_played'] + 1;
            $newBestScore = max($existingStats['best_score'], $score);
            $newTotalScore = $existingStats['total_score'] + $score;
            $newAverageScore = $newTotalScore / $newTimesPlayed;
            
            $stmt = $this->pdo->prepare("
                UPDATE user_game_stats
                SET times_played = ?,
                    best_score = ?,
                    total_score = ?,
                    average_score = ?,
                    last_played_at = NOW()
                WHERE user_id = ? AND game_id = ?
            ");
            
            return $stmt->execute([
                $newTimesPlayed,
                $newBestScore,
                $newTotalScore,
                $newAverageScore,
                $userId,
                $gameId
            ]);
        } else {
            // Create new stats entry
            $stmt = $this->pdo->prepare("
                INSERT INTO user_game_stats 
                (user_id, game_id, times_played, best_score, total_score, average_score, last_played_at)
                VALUES (?, ?, 1, ?, ?, ?, NOW())
            ");
            
            return $stmt->execute([
                $userId,
                $gameId,
                $score,
                $score,
                $score
            ]);
        }
    }
    
    /**
     * Get recent game sessions
     * 
     * @param int $userId User ID
     * @param int $limit Number of sessions to retrieve
     * @return array Recent sessions
     */
    public function getRecentSessions($userId, $limit = 10) {
        $stmt = $this->pdo->prepare("
            SELECT 
                gs.session_id,
                g.code as game_type,
                g.name as game_name,
                gs.difficulty,
                gs.started_at,
                gs.ended_at,
                TIMESTAMPDIFF(SECOND, gs.started_at, gs.ended_at) as duration_seconds,
                sc.score,
                sc.details
            FROM game_sessions gs
            JOIN games g ON gs.game_id = g.game_id
            LEFT JOIN game_scores sc ON gs.session_id = sc.session_id
            WHERE gs.user_id = ?
            ORDER BY gs.started_at DESC
            LIMIT ?
        ");
        
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->pdo->rollBack();
    }
}
