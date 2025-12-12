<?php
/**
 * Reusable Database Functions
 * Common queries and operations for the elderly care application
 */

require_once __DIR__ . '/config.php';

// ==================== USER FUNCTIONS ====================

/**
 * Get user by email
 */
function getUserByEmail($email) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

/**
 * Get user by ID
 */
function getUserById($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ? LIMIT 1");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

/**
 * Create new user
 */
function createUser($email, $password_hash) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO users (email, password, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$email, $password_hash]);
    return $pdo->lastInsertId();
}

/**
 * Check if email exists
 */
function emailExists($email) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetchColumn() > 0;
}

// ==================== PROFILE FUNCTIONS ====================

/**
 * Get user profile
 */
function getUserProfile($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM profiles WHERE user_id = ? LIMIT 1");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

/**
 * Create default profile
 */
function createDefaultProfile($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO profiles (user_id, created_at) VALUES (?, NOW())");
    $stmt->execute([$user_id]);
    return true;
}

/**
 * Update user profile
 */
function updateProfile($user_id, $full_name, $date_of_birth, $gender, $avatar_url = null) {
    global $pdo;
    if ($avatar_url !== null) {
        $stmt = $pdo->prepare("UPDATE profiles SET full_name = ?, date_of_birth = ?, gender = ?, avatar_url = ? WHERE user_id = ?");
        $stmt->execute([$full_name, $date_of_birth, $gender, $avatar_url, $user_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE profiles SET full_name = ?, date_of_birth = ?, gender = ? WHERE user_id = ?");
        $stmt->execute([$full_name, $date_of_birth, $gender, $user_id]);
    }
    return $stmt->rowCount();
}

// ==================== SETTINGS FUNCTIONS ====================

/**
 * Get user settings
 */
function getUserSettings($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM user_settings WHERE user_id = ? LIMIT 1");
    $stmt->execute([$user_id]);
    $settings = $stmt->fetch();
    
    // Convert font size to boolean for backward compatibility
    if ($settings) {
        $settings['large_font'] = ($settings['preferred_font_size'] === 'large' || $settings['preferred_font_size'] === 'xlarge') ? 1 : 0;
        $settings['voice_assistant'] = $settings['voice_assistant_enabled'];
    }
    
    return $settings;
}

/**
 * Create default settings
 */
function createDefaultSettings($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO user_settings (user_id, high_contrast, preferred_font_size, voice_assistant_enabled, tap_only_mode, created_at) VALUES (?, 0, 'normal', 0, 0, NOW())");
    $stmt->execute([$user_id]);
    return true;
}

/**
 * Update user settings
 */
function updateSettings($user_id, $high_contrast, $large_font, $voice_assistant, $tap_only_mode) {
    global $pdo;
    // Convert boolean to font size
    $font_size = $large_font ? 'large' : 'normal';
    $stmt = $pdo->prepare("UPDATE user_settings SET high_contrast = ?, preferred_font_size = ?, voice_assistant_enabled = ?, tap_only_mode = ? WHERE user_id = ?");
    $stmt->execute([$high_contrast, $font_size, $voice_assistant, $tap_only_mode, $user_id]);
    return $stmt->rowCount();
}

// ==================== MOOD FUNCTIONS ====================

/**
 * Insert mood entry
 */
/**
 * Insert or update mood entry
 * @implements C1: Mood Save Rule - entry_date must not be null, mood_value required (1-5)
 * @implements C3: Optional Notes Rule - notes can be empty string, entry still saves
 */
function insertMood($user_id, $mood_value, $mood_emoji = '', $mood_text = '') {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO mood_logs (user_id, entry_date, mood_value, mood_emoji, notes) VALUES (?, CURDATE(), ?, ?, ?) ON DUPLICATE KEY UPDATE mood_value = ?, mood_emoji = ?, notes = ?");
    $stmt->execute([$user_id, $mood_value, $mood_emoji, $mood_text, $mood_value, $mood_emoji, $mood_text]);
    return $stmt->rowCount();
}

/**
 * Get recent mood entries
 */
/**
 * Get recent mood entries
 * @implements C2: History Update Rule - ORDER BY entry_date DESC ensures newest entries display first
 */
function getRecentMood($user_id, $limit = 10) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM mood_logs WHERE user_id = ? ORDER BY entry_date DESC LIMIT ?");
    $stmt->execute([$user_id, $limit]);
    return $stmt->fetchAll();
}

/**
 * Get mood statistics for date range
 */
function getMoodStats($user_id, $days = 30) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT AVG(mood_value) as avg_mood, MIN(mood_value) as min_mood, MAX(mood_value) as max_mood, COUNT(*) as total_entries FROM mood_logs WHERE user_id = ? AND entry_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)");
    $stmt->execute([$user_id, $days]);
    return $stmt->fetch();
}

// ==================== GAME FUNCTIONS ====================

/**
 * Get or create game ID by name/code
 */
function getGameId($game_type) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT game_id FROM games WHERE code = ? LIMIT 1");
    $stmt->execute([$game_type]);
    $game = $stmt->fetch();
    
    if ($game) {
        return $game['game_id'];
    }
    
    // Create game if it doesn't exist
    $game_names = [
        'memory' => 'Memory Match',
        'attention' => 'Attention Focus',
        'reaction' => 'Reaction Time',
        'puzzle' => 'Puzzle Solver'
    ];
    $name = $game_names[$game_type] ?? ucfirst($game_type);
    
    $stmt = $pdo->prepare("INSERT INTO games (name, code) VALUES (?, ?)");
    $stmt->execute([$name, $game_type]);
    return $pdo->lastInsertId();
}

/**
 * Insert game session with score
 * 
 * Constraints:
 * C1: Auto Save Rule - Automatically saves game score immediately
 * C3: Stats Update Formula:
 *     - Times Played = Times Played + 1 (automatic via INSERT)
 *     - If Score > Best Score -> Best Score = Score (calculated in getUserGameStats)
 *     - Average Score = Total Score / Times Played (calculated in getUserGameStats)
 */
function insertGameSession($user_id, $game_type, $score, $duration, $difficulty = 'medium', $details = null) {
    global $pdo;
    
    // Get game ID
    $game_id = getGameId($game_type);
    
    // Parse details if provided as JSON
    $accuracy = null;
    $avg_reaction_ms = null;
    if ($details) {
        $details_arr = is_string($details) ? json_decode($details, true) : $details;
        $accuracy = $details_arr['accuracy'] ?? null;
        $avg_reaction_ms = isset($details_arr['reaction_time']) ? round($details_arr['reaction_time']) : null;
    }
    
    // C1: Auto Save - Insert game session immediately
    $stmt = $pdo->prepare("INSERT INTO game_sessions (user_id, game_id, difficulty, started_at, ended_at) VALUES (?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? SECOND))");
    $stmt->execute([$user_id, $game_id, $difficulty, $duration]);
    $session_id = $pdo->lastInsertId();
    
    // C1: Auto Save - Insert game score immediately
    $stmt = $pdo->prepare("INSERT INTO game_scores (session_id, score, accuracy, avg_reaction_ms) VALUES (?, ?, ?, ?)");
    $stmt->execute([$session_id, $score, $accuracy, $avg_reaction_ms]);
    
    // C3: Statistics automatically updated by this insert
    // - Times Played incremented (new row added)
    // - Best Score and Average Score recalculated on next query
    
    return $session_id;
}

/**
 * Get user game statistics
 * 
 * Constraints:
 * C3: Stats Update Formula implementation:
 *     - Times Played = COUNT(*) = Times Played + 1 with each new session
 *     - Best Score = MAX(score) = highest score achieved
 *     - Average Score = AVG(score) = Total Score / Times Played
 */
function getUserGameStats($user_id, $game_type = null) {
    global $pdo;
    if ($game_type) {
        // C3: Calculate statistics using formula
        $stmt = $pdo->prepare("
            SELECT g.code as game_type, 
                   COUNT(*) as games_played,                    -- Times Played
                   AVG(gsc.score) as avg_score,                 -- Average Score = Total / Times Played
                   MAX(gsc.score) as best_score,                -- Best Score = Maximum score
                   SUM(TIMESTAMPDIFF(SECOND, gs.started_at, gs.ended_at)) as total_time 
            FROM game_sessions gs
            JOIN games g ON gs.game_id = g.game_id
            LEFT JOIN game_scores gsc ON gs.session_id = gsc.session_id
            WHERE gs.user_id = ? AND g.code = ? 
            GROUP BY g.code
        ");
        $stmt->execute([$user_id, $game_type]);
    } else {
        $stmt = $pdo->prepare("
            SELECT g.code as game_type, COUNT(*) as games_played, 
                   AVG(gsc.score) as avg_score, MAX(gsc.score) as best_score, 
                   SUM(TIMESTAMPDIFF(SECOND, gs.started_at, gs.ended_at)) as total_time 
            FROM game_sessions gs
            JOIN games g ON gs.game_id = g.game_id
            LEFT JOIN game_scores gsc ON gs.session_id = gsc.session_id
            WHERE gs.user_id = ? 
            GROUP BY g.code
        ");
        $stmt->execute([$user_id]);
    }
    return $stmt->fetchAll();
}

/**
 * Get recent game sessions
 */
function getRecentGameSessions($user_id, $limit = 10) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT gs.*, g.code as game_type, gsc.score,
               TIMESTAMPDIFF(SECOND, gs.started_at, gs.ended_at) as duration_seconds
        FROM game_sessions gs
        JOIN games g ON gs.game_id = g.game_id
        LEFT JOIN game_scores gsc ON gs.session_id = gsc.session_id
        WHERE gs.user_id = ? 
        ORDER BY gs.started_at DESC 
        LIMIT ?
    ");
    $stmt->execute([$user_id, $limit]);
    return $stmt->fetchAll();
}

// ==================== QUESTIONNAIRE FUNCTIONS ====================

/**
 * Get or create questionnaire ID by type/code
 */
function getQuestionnaireId($questionnaire_type) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT questionnaire_id FROM questionnaires WHERE short_code = ? LIMIT 1");
    $stmt->execute([$questionnaire_type]);
    $questionnaire = $stmt->fetch();
    
    if ($questionnaire) {
        return $questionnaire['questionnaire_id'];
    }
    
    // Create questionnaire if it doesn't exist
    $name = ucfirst($questionnaire_type) . ' Assessment';
    $stmt = $pdo->prepare("INSERT INTO questionnaires (name, short_code) VALUES (?, ?)");
    $stmt->execute([$name, $questionnaire_type]);
    return $pdo->lastInsertId();
}

/**
 * Insert questionnaire result
 */
function insertQuestionnaireResult($user_id, $questionnaire_type, $total_score, $answers_json) {
    global $pdo;
    
    // Get questionnaire ID
    $questionnaire_id = getQuestionnaireId($questionnaire_type);
    
    // Insert response
    $stmt = $pdo->prepare("INSERT INTO questionnaire_responses (user_id, questionnaire_id, score, responses) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $questionnaire_id, $total_score, $answers_json]);
    return $pdo->lastInsertId();
}

/**
 * Get questionnaire results
 */
function getQuestionnaireResults($user_id, $questionnaire_type = null) {
    global $pdo;
    if ($questionnaire_type) {
        $stmt = $pdo->prepare("
            SELECT qr.*, q.short_code as questionnaire_type, q.name as questionnaire_name 
            FROM questionnaire_responses qr
            JOIN questionnaires q ON qr.questionnaire_id = q.questionnaire_id
            WHERE qr.user_id = ? AND q.short_code = ? 
            ORDER BY qr.completed_at DESC
        ");
        $stmt->execute([$user_id, $questionnaire_type]);
    } else {
        $stmt = $pdo->prepare("
            SELECT qr.*, q.short_code as questionnaire_type, q.name as questionnaire_name 
            FROM questionnaire_responses qr
            JOIN questionnaires q ON qr.questionnaire_id = q.questionnaire_id
            WHERE qr.user_id = ? 
            ORDER BY qr.completed_at DESC
        ");
        $stmt->execute([$user_id]);
    }
    return $stmt->fetchAll();
}

/**
 * Get latest questionnaire result
 */
function getLatestQuestionnaireResult($user_id, $questionnaire_type) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT qr.*, q.short_code as questionnaire_type, q.name as questionnaire_name 
        FROM questionnaire_responses qr
        JOIN questionnaires q ON qr.questionnaire_id = q.questionnaire_id
        WHERE qr.user_id = ? AND q.short_code = ? 
        ORDER BY qr.completed_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$user_id, $questionnaire_type]);
    return $stmt->fetch();
}

// ==================== DIARY FUNCTIONS ====================

/**
 * Insert diary entry
 */
function insertDiaryEntry($user_id, $title, $content) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO diary_entries (user_id, title, content, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$user_id, $title, $content]);
    return $pdo->lastInsertId();
}

/**
 * Get user diary entries
 */
function getDiaryEntries($user_id, $limit = 20, $offset = 0) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM diary_entries WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$user_id, $limit, $offset]);
    return $stmt->fetchAll();
}

// ==================== UTILITY FUNCTIONS ====================

/**
 * Sanitize input
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Validate email format
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'F j, Y') {
    return date($format, strtotime($date));
}

/**
 * Calculate age from date of birth
 */
function calculateAge($date_of_birth) {
    $dob = new DateTime($date_of_birth);
    $now = new DateTime();
    $age = $now->diff($dob);
    return $age->y;
}
