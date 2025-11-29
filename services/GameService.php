<?php
/**
 * GameService - Business Logic Layer for Game System
 * 
 * Enforces ALL game constraints:
 * - C1: Auto Save Rule - Every game completion MUST save immediately
 * - C2: Allowed Difficulty Levels {Easy, Medium, Hard}
 * - C3: Stats Update Formula (Times Played, Best Score, Average Score)
 * - C4: Valid Game Launch Actions (Difficulty button OR Play Now button)
 * 
 * Messages:
 * - M1: "Please choose a game to play"
 * - M2: "Your game is loading. Please wait…"
 * - M3: "Game complete! Your score has been saved."
 * - M4: "Your game statistics have been updated."
 * - M5: "Unable to load the game. Please try again later."
 */

class GameService {
    private $pdo;
    
    // Message constants
    const MSG_CHOOSE_GAME = "Please choose a game to play";
    const MSG_LOADING = "Your game is loading. Please wait…";
    const MSG_GAME_COMPLETE = "Game complete! Your score has been saved.";
    const MSG_STATS_UPDATED = "Your game statistics have been updated.";
    const MSG_ERROR = "Unable to load the game. Please try again later.";
    
    // C2: Allowed Difficulty Levels
    const ALLOWED_DIFFICULTIES = ['easy', 'medium', 'hard'];
    
    // Games that don't require difficulty selection
    const NO_DIFFICULTY_GAMES = ['visual_memory', 'number_memory', 'verbal_memory', 'chimp_test'];
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Complete a game session and update statistics
     * 
     * Enforces:
     * - C1: Auto Save Rule (transaction ensures atomicity)
     * - C3: Stats Update Formula
     * - M3, M4: Success messages
     * 
     * @param int $userId User ID
     * @param string $gameCode Game code (e.g., 'memory', 'reaction')
     * @param string|null $difficulty Difficulty level (null for new games)
     * @param int $score Player's score
     * @param array $details Additional details (max_score, accuracy, reaction_time, etc.)
     * @return array ['success' => bool, 'message' => string, 'session_id' => int]
     */
    public function completeGame($userId, $gameCode, $difficulty, $score, $details = []) {
        // C1: Auto Save Rule - Use transaction to ensure atomicity
        $this->pdo->beginTransaction();
        
        try {
            // Get or create game_id
            $gameId = $this->getOrCreateGame($gameCode);
            
            if (!$gameId) {
                throw new Exception("Failed to get game ID");
            }
            
            // Save game session
            $sessionId = $this->saveSession($userId, $gameId, $difficulty);
            
            if (!$sessionId) {
                throw new Exception("Failed to save game session");
            }
            
            // Save game score
            $this->saveScore($sessionId, $score, $details);
            
            // C3: Update stats with exact formula
            $this->updateStats($userId, $gameId, $score);
            
            // Commit transaction - C1 guaranteed
            $this->pdo->commit();
            
            // M3 + M4: Success messages
            return [
                'success' => true,
                'message' => self::MSG_GAME_COMPLETE . ' ' . self::MSG_STATS_UPDATED,
                'session_id' => $sessionId
            ];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            
            // Log error (you can add proper logging here)
            error_log("GameService::completeGame error: " . $e->getMessage());
            
            // M5: Error message
            return [
                'success' => false,
                'message' => self::MSG_ERROR,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Validate game launch parameters
     * 
     * Enforces:
     * - C2: Allowed Difficulty Levels {Easy, Medium, Hard}
     * - C4: Valid Game Launch Actions (Difficulty button OR Play Now button)
     * 
     * @param string $gameCode Game code
     * @param string|null $difficulty Difficulty level (null for new games)
     * @return array ['valid' => bool, 'message' => string, 'requires_difficulty' => bool]
     */
    public function validateGameLaunch($gameCode, $difficulty = null) {
        // C4: New games use "Play Now" only (no difficulty required)
        if (in_array($gameCode, self::NO_DIFFICULTY_GAMES)) {
            return [
                'valid' => true,
                'requires_difficulty' => false,
                'message' => self::MSG_LOADING
            ];
        }
        
        // C4: Original games require difficulty selection
        // C2: Must be one of {Easy, Medium, Hard}
        if (!$difficulty || !in_array(strtolower($difficulty), self::ALLOWED_DIFFICULTIES)) {
            return [
                'valid' => false,
                'requires_difficulty' => true,
                'message' => 'Please select a difficulty level (Easy, Medium, or Hard)'
            ];
        }
        
        return [
            'valid' => true,
            'requires_difficulty' => true,
            'message' => self::MSG_LOADING
        ];
    }
    
    /**
     * Get user's game statistics
     * 
     * @param int $userId User ID
     * @param string|null $gameCode Optional game code filter
     * @return array Game statistics
     */
    public function getStats($userId, $gameCode = null) {
        if ($gameCode) {
            $gameId = $this->getGameIdByCode($gameCode);
            
            if (!$gameId) {
                return null;
            }
            
            $stmt = $this->pdo->prepare("
                SELECT times_played, best_score, total_score, average_score
                FROM user_game_stats
                WHERE user_id = ? AND game_id = ?
            ");
            $stmt->execute([$userId, $gameId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        // Get all stats
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
     * Get recent game sessions
     * 
     * @param int $userId User ID
     * @param int $limit Number of sessions to retrieve
     * @return array Recent game sessions
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
                sc.max_score
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
     * Get or create game by code
     * 
     * @param string $gameCode Game code
     * @return int|false Game ID
     */
    private function getOrCreateGame($gameCode) {
        // Try to get existing game
        $gameId = $this->getGameIdByCode($gameCode);
        
        if ($gameId) {
            return $gameId;
        }
        
        // Create new game entry
        $gameNames = [
            'memory' => 'Memory Match',
            'attention' => 'Attention Focus',
            'reaction' => 'Reaction Time',
            'puzzle' => 'Puzzle Solver',
            'visual_memory' => 'Visual Memory',
            'number_memory' => 'Number Memory',
            'verbal_memory' => 'Verbal Memory',
            'chimp_test' => 'Chimp Test'
        ];
        
        $gameName = $gameNames[$gameCode] ?? ucfirst($gameCode);
        
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
    private function getGameIdByCode($gameCode) {
        $stmt = $this->pdo->prepare("SELECT game_id FROM games WHERE code = ?");
        $stmt->execute([$gameCode]);
        return $stmt->fetchColumn();
    }
    
    /**
     * Save game session
     * 
     * @param int $userId User ID
     * @param int $gameId Game ID
     * @param string|null $difficulty Difficulty level
     * @return int Session ID
     */
    private function saveSession($userId, $gameId, $difficulty) {
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
     * @param int $score Player's score
     * @param array $details Additional details
     * @return bool Success
     */
    private function saveScore($sessionId, $score, $details) {
        $maxScore = $details['max_score'] ?? null;
        $accuracy = $details['accuracy'] ?? null;
        $avgReactionMs = $details['avg_reaction_ms'] ?? null;
        $levelReached = $details['level_reached'] ?? null;
        
        $stmt = $this->pdo->prepare("
            INSERT INTO game_scores 
            (session_id, score, max_score, accuracy, avg_reaction_ms, level_reached)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $sessionId,
            $score,
            $maxScore,
            $accuracy,
            $avgReactionMs,
            $levelReached
        ]);
    }
    
    /**
     * Update user game statistics
     * 
     * Enforces C3: Stats Update Formula:
     * - Times Played = Times Played + 1
     * - If Score > Best Score → Best Score = Score
     * - Average Score = Total Score / Times Played
     * 
     * @param int $userId User ID
     * @param int $gameId Game ID
     * @param int $score Current game score
     * @return bool Success
     */
    private function updateStats($userId, $gameId, $score) {
        // Get current stats
        $stmt = $this->pdo->prepare("
            SELECT times_played, best_score, total_score, average_score
            FROM user_game_stats
            WHERE user_id = ? AND game_id = ?
        ");
        $stmt->execute([$userId, $gameId]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$stats) {
            // First time playing this game
            // C3: Times Played = 1, Best Score = Score, Average = Score
            $stmt = $this->pdo->prepare("
                INSERT INTO user_game_stats 
                (user_id, game_id, times_played, best_score, total_score, average_score)
                VALUES (?, ?, 1, ?, ?, ?)
            ");
            return $stmt->execute([$userId, $gameId, $score, $score, $score]);
        }
        
        // Update existing stats with C3 formula
        $timesPlayed = $stats['times_played'] + 1; // C3: Times Played = Times Played + 1
        $bestScore = max($stats['best_score'], $score); // C3: If Score > Best Score → Best Score = Score
        $totalScore = $stats['total_score'] + $score;
        $averageScore = $totalScore / $timesPlayed; // C3: Average Score = Total Score / Times Played
        
        $stmt = $this->pdo->prepare("
            UPDATE user_game_stats
            SET times_played = ?,
                best_score = ?,
                total_score = ?,
                average_score = ?
            WHERE user_id = ? AND game_id = ?
        ");
        
        return $stmt->execute([
            $timesPlayed,
            $bestScore,
            $totalScore,
            $averageScore,
            $userId,
            $gameId
        ]);
    }
}
