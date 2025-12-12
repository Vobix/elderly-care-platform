<?php
/**
 * User Data Access Object
 * Phase 3: Handles all user database operations
 * Separates data access from business logic
 */

class UserDAO {
    private $pdo;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Create a new user
     * 
     * @param string $username Username
     * @param string $email Email address
     * @param string $passwordHash Hashed password
     * @param string $fullName Full name
     * @param string|null $dateOfBirth Date of birth
     * @return int User ID
     */
    public function create($username, $email, $passwordHash, $fullName, $dateOfBirth = null) {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (username, email, password, full_name, date_of_birth, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([$username, $email, $passwordHash, $fullName, $dateOfBirth]);
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Find user by username
     * 
     * @param string $username Username
     * @return array|false User data or false
     */
    public function findByUsername($username) {
        $stmt = $this->pdo->prepare("
            SELECT user_id, username, email, password, full_name, date_of_birth, created_at
            FROM users
            WHERE username = ?
        ");
        
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Find user by email
     * 
     * @param string $email Email address
     * @return array|false User data or false
     */
    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("
            SELECT user_id, username, email, password, full_name, date_of_birth, created_at
            FROM users
            WHERE email = ?
        ");
        
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Find user by ID
     * 
     * @param int $userId User ID
     * @return array|false User data or false
     */
    public function findById($userId) {
        $stmt = $this->pdo->prepare("
            SELECT user_id, username, email, full_name, date_of_birth, created_at
            FROM users
            WHERE user_id = ?
        ");
        
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Update user profile
     * 
     * @param int $userId User ID
     * @param array $data Array of fields to update
     * @return bool Success
     */
    public function update($userId, $data) {
        $allowedFields = ['full_name', 'email', 'date_of_birth'];
        $updates = [];
        $params = [];
        
        foreach ($data as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $updates[] = "$field = ?";
                $params[] = $value;
            }
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $params[] = $userId;
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE user_id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Update user password
     * 
     * @param int $userId User ID
     * @param string $passwordHash New hashed password
     * @return bool Success
     */
    public function updatePassword($userId, $passwordHash) {
        $stmt = $this->pdo->prepare("
            UPDATE users
            SET password = ?
            WHERE user_id = ?
        ");
        
        return $stmt->execute([$passwordHash, $userId]);
    }
    
    /**
     * Check if username exists
     * 
     * @param string $username Username
     * @param int|null $excludeUserId Optional: exclude this user ID from check
     * @return bool True if exists
     */
    public function usernameExists($username, $excludeUserId = null) {
        if ($excludeUserId) {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM users WHERE username = ? AND user_id != ?
            ");
            $stmt->execute([$username, $excludeUserId]);
        } else {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$username]);
        }
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Check if email exists
     * 
     * @param string $email Email address
     * @param int|null $excludeUserId Optional: exclude this user ID from check
     * @return bool True if exists
     */
    public function emailExists($email, $excludeUserId = null) {
        if ($excludeUserId) {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM users WHERE email = ? AND user_id != ?
            ");
            $stmt->execute([$email, $excludeUserId]);
        } else {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
        }
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Delete user account
     * 
     * @param int $userId User ID
     * @return bool Success
     */
    public function delete($userId) {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }
}
