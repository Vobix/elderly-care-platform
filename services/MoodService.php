<?php
/**
 * MoodService - Business Logic Layer for Mood Tracking System
 * 
 * Enforces ALL mood tracking constraints and flows:
 * 
 * BASIC FLOW (BF:1-10):
 * BF:1 - User navigates to Mood Tracking page
 * BF:2 - System displays mood selection (M1)
 * BF:3 - User selects one of 5 mood levels
 * BF:4 - System shows notes field (M2)
 * BF:5 - User enters optional notes
 * BF:6 - User submits form
 * BF:7 - System validates mood selection
 * BF:8 - System saves mood entry (C1)
 * BF:9 - System updates Recent Mood History (C2)
 * BF:10 - System displays confirmation (M3)
 * 
 * ALTERNATE FLOW (A1):
 * A1 - No Mood Selected
 * A1.1 - System displays error message (M4)
 * A1.2 - User returns to form
 * 
 * Constraints:
 * - C1: Mood Save Rule - Entry must contain entry_date + mood_value (1-5)
 * - C2: History Update Rule - Display newest entries first (ORDER BY DESC)
 * - C3: Optional Notes Rule - mood_text optional, entry saves with empty notes
 * 
 * Messages:
 * - M1: "Please select your mood for today"
 * - M2: "Add your thoughts (optional)"
 * - M3: "Your mood has been saved successfully for today!"
 * - M4: "Please select a mood level before saving"
 */

class MoodService {
    private $pdo;
    
    // Message constants
    const MSG_SELECT_MOOD = "Please select your mood for today";
    const MSG_OPTIONAL_NOTES = "Add your thoughts (optional)";
    const MSG_MOOD_SAVED = "Your mood has been saved successfully for today!";
    const MSG_NO_MOOD_SELECTED = "Please select a mood level before saving";
    
    // Mood levels (C1: mood_value must be 1-5)
    const MOOD_LEVELS = [
        1 => ['emoji' => '😢', 'label' => 'Very Sad', 'color' => '#dc3545'],
        2 => ['emoji' => '😕', 'label' => 'Sad', 'color' => '#ffc107'],
        3 => ['emoji' => '😐', 'label' => 'Okay', 'color' => '#17a2b8'],
        4 => ['emoji' => '😊', 'label' => 'Happy', 'color' => '#28a745'],
        5 => ['emoji' => '😄', 'label' => 'Very Happy', 'color' => '#20c997']
    ];
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Record user's mood for today
     * 
     * Implements BASIC FLOW (BF:6-10) and ALTERNATE FLOW (A1)
     * Enforces C1, C3
     * 
     * @param int $userId User ID
     * @param int|null $moodValue Mood level (1-5)
     * @param string|null $notes Optional notes
     * @return array ['success' => bool, 'message' => string, 'flow_step' => string]
     */
    public function recordMood($userId, $moodValue, $notes = null) {
        // BF:6 - Form submitted
        
        // BF:7 - Validate mood selection
        // A1: No Mood Selected - Check if mood_value is valid
        if ($moodValue === null || !is_numeric($moodValue)) {
            // A1.1 - Display error message (M4)
            return [
                'success' => false,
                'message' => self::MSG_NO_MOOD_SELECTED,
                'flow_step' => 'A1.1' // Alternate flow
            ];
        }
        
        // Convert to integer
        $moodValue = intval($moodValue);
        
        // C1: Mood Save Rule - mood_value must be between 1-5
        if ($moodValue < 1 || $moodValue > 5) {
            // A1.1 - Invalid mood value
            return [
                'success' => false,
                'message' => self::MSG_NO_MOOD_SELECTED,
                'flow_step' => 'A1.1'
            ];
        }
        
        // Get mood emoji for this level
        $moodEmoji = self::MOOD_LEVELS[$moodValue]['emoji'];
        
        // C3: Optional Notes Rule - notes can be null or empty
        $notes = $notes ? trim($notes) : null;
        
        // BF:8 - Save mood entry
        // C1: Mood Save Rule - Must contain entry_date (CURDATE) and mood_value
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO mood_logs 
                (user_id, entry_date, mood_value, mood_emoji, mood_text, created_at)
                VALUES (?, CURDATE(), ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                    mood_value = VALUES(mood_value),
                    mood_emoji = VALUES(mood_emoji),
                    mood_text = VALUES(mood_text)
            ");
            
            $stmt->execute([$userId, $moodValue, $moodEmoji, $notes]);
            
            // BF:9 - Recent Mood History will auto-update with C2 (newest first)
            
            // BF:10 - Display confirmation message (M3)
            return [
                'success' => true,
                'message' => self::MSG_MOOD_SAVED,
                'flow_step' => 'BF:10' // Basic flow complete
            ];
            
        } catch (Exception $e) {
            error_log("MoodService::recordMood error: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'An error occurred while saving your mood. Please try again.',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get recent mood history
     * 
     * Implements C2: History Update Rule
     * Returns newest entries first (ORDER BY entry_date DESC)
     * 
     * @param int $userId User ID
     * @param int $days Number of days to retrieve (default 7)
     * @return array Recent mood entries
     */
    public function getRecentHistory($userId, $days = 7) {
        // C2: History Update Rule - ORDER BY entry_date DESC ensures newest first
        $stmt = $this->pdo->prepare("
            SELECT entry_date, mood_value, mood_emoji, mood_text, created_at
            FROM mood_logs
            WHERE user_id = ?
            ORDER BY entry_date DESC
            LIMIT ?
        ");
        
        $stmt->execute([$userId, $days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get today's mood entry
     * 
     * @param int $userId User ID
     * @return array|false Today's mood entry or false if not recorded
     */
    public function getTodaysMood($userId) {
        $stmt = $this->pdo->prepare("
            SELECT mood_value, mood_emoji, mood_text, entry_date, created_at
            FROM mood_logs
            WHERE user_id = ? AND entry_date = CURDATE()
        ");
        
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get mood statistics for a time period
     * 
     * @param int $userId User ID
     * @param int $days Number of days to analyze (default 30)
     * @return array|false Mood statistics
     */
    public function getMoodStats($userId, $days = 30) {
        $stmt = $this->pdo->prepare("
            SELECT 
                AVG(mood_value) as avg_mood,
                MIN(mood_value) as min_mood,
                MAX(mood_value) as max_mood,
                COUNT(*) as total_entries
            FROM mood_logs
            WHERE user_id = ? 
            AND entry_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
        ");
        
        $stmt->execute([$userId, $days]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get mood data structure (for displaying mood options)
     * 
     * @return array Mood levels with emoji, label, color
     */
    public static function getMoodLevels() {
        return self::MOOD_LEVELS;
    }
    
    /**
     * Get emoji for a specific mood value
     * 
     * @param int $moodValue Mood level (1-5)
     * @return string Emoji
     */
    public static function getMoodEmoji($moodValue) {
        return self::MOOD_LEVELS[$moodValue]['emoji'] ?? '😐';
    }
    
    /**
     * Get label for a specific mood value
     * 
     * @param int $moodValue Mood level (1-5)
     * @return string Label
     */
    public static function getMoodLabel($moodValue) {
        return self::MOOD_LEVELS[$moodValue]['label'] ?? 'Unknown';
    }
    
    /**
     * Get color for a specific mood value
     * 
     * @param int $moodValue Mood level (1-5)
     * @return string Color hex code
     */
    public static function getMoodColor($moodValue) {
        return self::MOOD_LEVELS[$moodValue]['color'] ?? '#6c757d';
    }
    
    /**
     * Get all message constants
     * 
     * @return array All mood tracking messages
     */
    public static function getMessages() {
        return [
            'M1' => self::MSG_SELECT_MOOD,
            'M2' => self::MSG_OPTIONAL_NOTES,
            'M3' => self::MSG_MOOD_SAVED,
            'M4' => self::MSG_NO_MOOD_SELECTED
        ];
    }
}
