<?php
/**
 * Leaderboard Data Access Object
 * Handles all leaderboard and ranking calculations
 */

class LeaderboardDAO {
    private $pdo;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get global leaderboard for a specific game
     * 
     * @param int $gameId Game ID
     * @param int $limit Number of top players to return
     * @param string $metric 'best_score' or 'average_score'
     * @return array Leaderboard entries
     */
    public function getGameLeaderboard($gameId, $limit = 100, $metric = 'best_score') {
        $allowedMetrics = ['best_score', 'average_score'];
        if (!in_array($metric, $allowedMetrics)) {
            $metric = 'best_score';
        }
        
        $stmt = $this->pdo->prepare("
            SELECT 
                u.user_id,
                u.username,
                u.full_name,
                ugs.{$metric} as score,
                ugs.times_played,
                ugs.last_played_at,
                @rank := @rank + 1 as rank
            FROM user_game_stats ugs
            JOIN users u ON ugs.user_id = u.user_id
            CROSS JOIN (SELECT @rank := 0) r
            WHERE ugs.game_id = ?
            ORDER BY ugs.{$metric} DESC, ugs.times_played DESC
            LIMIT ?
        ");
        
        $stmt->execute([$gameId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get user's rank and percentile for a game
     * 
     * @param int $userId User ID
     * @param int $gameId Game ID
     * @param string $metric 'best_score' or 'average_score'
     * @return array Rank info with percentile
     */
    public function getUserRank($userId, $gameId, $metric = 'best_score') {
        $allowedMetrics = ['best_score', 'average_score'];
        if (!in_array($metric, $allowedMetrics)) {
            $metric = 'best_score';
        }
        
        // Get user's score
        $stmt = $this->pdo->prepare("
            SELECT {$metric} as user_score, times_played
            FROM user_game_stats
            WHERE user_id = ? AND game_id = ?
        ");
        $stmt->execute([$userId, $gameId]);
        $userStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$userStats) {
            return null;
        }
        
        // Get total players for this game
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total_players
            FROM user_game_stats
            WHERE game_id = ?
        ");
        $stmt->execute([$gameId]);
        $totalPlayers = $stmt->fetchColumn();
        
        // Get user's rank (how many players have better scores)
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) + 1 as rank
            FROM user_game_stats
            WHERE game_id = ? 
              AND (
                  {$metric} > ? 
                  OR ({$metric} = ? AND times_played > ?)
              )
        ");
        $stmt->execute([
            $gameId, 
            $userStats['user_score'],
            $userStats['user_score'],
            $userStats['times_played']
        ]);
        $rank = $stmt->fetchColumn();
        
        // Calculate percentile (what % of players are below this user)
        $percentile = $totalPlayers > 1 
            ? (($totalPlayers - $rank) / ($totalPlayers - 1)) * 100 
            : 100;
        
        return [
            'rank' => $rank,
            'total_players' => $totalPlayers,
            'percentile' => round($percentile, 1),
            'score' => $userStats['user_score'],
            'times_played' => $userStats['times_played'],
            'better_than' => $totalPlayers - $rank,
            'message' => $this->getPercentileMessage($percentile)
        ];
    }
    
    /**
     * Get top N players around a specific user
     * 
     * @param int $userId User ID
     * @param int $gameId Game ID
     * @param int $above Number of players to show above user
     * @param int $below Number of players to show below user
     * @param string $metric Scoring metric
     * @return array Players around user
     */
    public function getPlayersAroundUser($userId, $gameId, $above = 5, $below = 5, $metric = 'best_score') {
        $allowedMetrics = ['best_score', 'average_score'];
        if (!in_array($metric, $allowedMetrics)) {
            $metric = 'best_score';
        }
        
        // First get user's rank
        $rankInfo = $this->getUserRank($userId, $gameId, $metric);
        if (!$rankInfo) {
            return [];
        }
        
        $userRank = $rankInfo['rank'];
        $startRank = max(1, $userRank - $above);
        $endRank = $userRank + $below;
        
        $stmt = $this->pdo->prepare("
            SELECT 
                u.user_id,
                u.username,
                u.full_name,
                ugs.{$metric} as score,
                ugs.times_played,
                @rank := @rank + 1 as rank,
                (u.user_id = ?) as is_current_user
            FROM user_game_stats ugs
            JOIN users u ON ugs.user_id = u.user_id
            CROSS JOIN (SELECT @rank := 0) r
            WHERE ugs.game_id = ?
            ORDER BY ugs.{$metric} DESC, ugs.times_played DESC
            LIMIT ?, ?
        ");
        
        $stmt->execute([$userId, $gameId, $startRank - 1, ($endRank - $startRank + 1)]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get global cross-game leaderboard (most active players)
     * 
     * @param int $limit Number of players to return
     * @return array Top players across all games
     */
    public function getGlobalLeaderboard($limit = 50) {
        $stmt = $this->pdo->prepare("
            SELECT 
                u.user_id,
                u.username,
                u.full_name,
                COUNT(DISTINCT ugs.game_id) as games_played,
                SUM(ugs.times_played) as total_sessions,
                AVG(ugs.average_score) as overall_avg_score,
                SUM(ugs.best_score) as total_best_scores
            FROM users u
            JOIN user_game_stats ugs ON u.user_id = ugs.user_id
            GROUP BY u.user_id
            ORDER BY total_sessions DESC, games_played DESC
            LIMIT ?
        ");
        
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get percentile message based on performance
     * 
     * @param float $percentile Percentile (0-100)
     * @return string Encouraging message
     */
    private function getPercentileMessage($percentile) {
        if ($percentile >= 99) {
            return "🏆 Top 1%! You're a legend!";
        } elseif ($percentile >= 95) {
            return "🌟 Top 5%! Exceptional performance!";
        } elseif ($percentile >= 90) {
            return "⭐ Top 10%! Outstanding!";
        } elseif ($percentile >= 75) {
            return "🎯 Top 25%! Great job!";
        } elseif ($percentile >= 50) {
            return "👍 Above average! Keep it up!";
        } elseif ($percentile >= 25) {
            return "💪 You're improving! Practice more!";
        } else {
            return "🎮 Keep practicing! You'll get better!";
        }
    }
}
