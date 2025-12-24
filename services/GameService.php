<?php
/**
 * GameService - Business Logic Layer for Game System
 * 
 * Phase 3: Refactored to use GameDAO for data access
 * Added: Leaderboard and percentile ranking system
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

require_once __DIR__ . '/../database/dao/GameDAO.php';
require_once __DIR__ . '/../database/dao/LeaderboardDAO.php';

class GameService {
    private $gameDAO;
    private $leaderboardDAO;
    
    // Message constants
    const MSG_CHOOSE_GAME = "Please choose a game to play";
    const MSG_LOADING = "Your game is loading. Please wait…";
    const MSG_GAME_COMPLETE = "Game complete! Your score has been saved.";
    const MSG_STATS_UPDATED = "Your game statistics have been updated.";
    const MSG_ERROR = "Unable to load the game. Please try again later.";
    
    // C2: Allowed Difficulty Levels
    const ALLOWED_DIFFICULTIES = ['easy', 'medium', 'hard'];
    
    // Games that don't require difficulty selection
    const NO_DIFFICULTY_GAMES = ['visual_memory', 'number_memory', 'verbal_memory', 'chimp_test', 'card_flip'];
    
    public function __construct(GameDAO $gameDAO, LeaderboardDAO $leaderboardDAO = null) {
        $this->gameDAO = $gameDAO;
        $this->leaderboardDAO = $leaderboardDAO;
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
        $this->gameDAO->beginTransaction();
        
        try {
            // Get or create game_id
            $gameName = ucwords(str_replace('_', ' ', $gameCode));
            $gameId = $this->gameDAO->getOrCreateGame($gameCode, $gameName);
            
            if (!$gameId) {
                throw new Exception("Failed to get game ID");
            }
            
            // Save game session with duration
            $duration = isset($details['duration']) ? (int)$details['duration'] : 0;
            $sessionId = $this->gameDAO->createSession($userId, $gameId, $difficulty, $duration);
            
            if (!$sessionId) {
                throw new Exception("Failed to save game session");
            }
            
            // Save game score
            $this->gameDAO->saveScore($sessionId, $score, $details);
            
            // C3: Update stats with exact formula
            $this->gameDAO->updateStats($userId, $gameId, $score);
            
            // Update leaderboard and recalculate rankings
            if ($this->leaderboardDAO) {
                $this->leaderboardDAO->recalculateRanks($gameId);
            }
            
            // Commit transaction - C1 guaranteed
            $this->gameDAO->commit();
            
            // M3 + M4: Success messages
            return [
                'success' => true,
                'message' => self::MSG_GAME_COMPLETE . ' ' . self::MSG_STATS_UPDATED,
                'session_id' => $sessionId,
                'game_id' => $gameId
            ];
            
        } catch (Exception $e) {
            $this->gameDAO->rollback();
            
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
            $gameId = $this->gameDAO->getGameIdByCode($gameCode);
            
            if (!$gameId) {
                return null;
            }
            
            return $this->gameDAO->getStats($userId, $gameId);
        }
        
        // Get all stats
        return $this->gameDAO->getAllStats($userId);
    }
    
    /**
     * Get recent game sessions
     * 
     * @param int $userId User ID
     * @param int $limit Number of sessions to retrieve
     * @return array Recent game sessions
     */
    public function getRecentSessions($userId, $limit = 10) {
        return $this->gameDAO->getRecentSessions($userId, $limit);
    }
    
    /**
     * Get leaderboard for a specific game
     * 
     * @param string $gameCode Game code
     * @param int $limit Number of top players
     * @return array Leaderboard data
     */
    public function getLeaderboard($gameCode, $limit = 10) {
        if (!$this->leaderboardDAO) {
            return [];
        }
        
        $gameId = $this->gameDAO->getGameIdByCode($gameCode);
        if (!$gameId) {
            return [];
        }
        
        return $this->leaderboardDAO->getGameLeaderboard($gameId, $limit);
    }
    
    /**
     * Get user's rank and percentile for a game
     * 
     * @param int $userId User ID
     * @param string $gameCode Game code
     * @return array|null Rank information with percentile
     */
    public function getUserRanking($userId, $gameCode) {
        if (!$this->leaderboardDAO) {
            return null;
        }
        
        $gameId = $this->gameDAO->getGameIdByCode($gameCode);
        if (!$gameId) {
            return null;
        }
        
        return $this->leaderboardDAO->getUserRank($userId, $gameId);
    }
    
    /**
     * Get all messages for display
     * 
     * @return array Message constants
     */
    public static function getMessages() {
        return [
            'choose_game' => self::MSG_CHOOSE_GAME,
            'loading' => self::MSG_LOADING,
            'game_complete' => self::MSG_GAME_COMPLETE,
            'stats_updated' => self::MSG_STATS_UPDATED,
            'error' => self::MSG_ERROR
        ];
    }
}
