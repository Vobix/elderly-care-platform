<?php
/**
 * Database Initialization Script
 * Creates all necessary tables for the elderly care platform
 * Run this file once to set up your database
 */

require_once __DIR__ . '/config.php';

try {
    // Users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        user_id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        last_login DATETIME NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Profiles table
    $pdo->exec("CREATE TABLE IF NOT EXISTS profiles (
        profile_id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        full_name VARCHAR(100),
        date_of_birth DATE NULL,
        gender ENUM('male', 'female', 'other', '') DEFAULT '',
        avatar VARCHAR(255),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NULL,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // User settings table
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_settings (
        setting_id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        high_contrast TINYINT(1) DEFAULT 0,
        large_font TINYINT(1) DEFAULT 0,
        voice_assistant TINYINT(1) DEFAULT 0,
        tap_only_mode TINYINT(1) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NULL,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Mood logs table
    $pdo->exec("CREATE TABLE IF NOT EXISTS mood_logs (
        mood_id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        mood_level TINYINT NOT NULL CHECK (mood_level BETWEEN 1 AND 5),
        notes TEXT,
        logged_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        INDEX idx_user_date (user_id, logged_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Game sessions table
    $pdo->exec("CREATE TABLE IF NOT EXISTS game_sessions (
        session_id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        game_type ENUM('memory', 'attention', 'reaction', 'puzzle') NOT NULL,
        score DECIMAL(6,2) NOT NULL,
        duration_seconds INT NOT NULL,
        difficulty ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
        played_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        INDEX idx_user_game (user_id, game_type),
        INDEX idx_played_at (played_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Questionnaire results table
    $pdo->exec("CREATE TABLE IF NOT EXISTS questionnaire_results (
        result_id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        questionnaire_type VARCHAR(50) NOT NULL,
        total_score INT NOT NULL,
        answers_json TEXT,
        completed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        INDEX idx_user_type (user_id, questionnaire_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Diary entries table (optional - for future expansion)
    $pdo->exec("CREATE TABLE IF NOT EXISTS diary_entries (
        entry_id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        title VARCHAR(200),
        content TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NULL,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        INDEX idx_user_date (user_id, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    echo "✅ Database tables created successfully!<br>";
    echo "📊 Tables created:<br>";
    echo "- users<br>";
    echo "- profiles<br>";
    echo "- user_settings<br>";
    echo "- mood_logs<br>";
    echo "- game_sessions<br>";
    echo "- questionnaire_results<br>";
    echo "- diary_entries<br>";
    echo "<br>🎉 Your database is ready to use!<br>";
    echo "<br><a href='/pages/register.php'>→ Create your account</a>";
    
} catch (PDOException $e) {
    echo "❌ Error creating tables: " . $e->getMessage();
    error_log("Database initialization error: " . $e->getMessage());
}
?>
