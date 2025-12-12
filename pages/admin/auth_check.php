<?php
/**
 * Admin Authentication Check
 * Protects admin pages - only admin users can access
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // User is not logged in, redirect to login page
    header("Location: /pages/login.php?error=unauthorized");
    exit();
}

// Check if user is admin
require_once __DIR__ . '/../../database/config.php';

try {
    $stmt = $pdo->prepare("SELECT is_admin, username FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || $user['is_admin'] != 1) {
        // User is not an admin, redirect to regular dashboard
        header("Location: /pages/insights/dashboard.php?error=admin_only");
        exit();
    }
    
    // Store admin username for display
    $_SESSION['admin_username'] = $user['username'];
    
} catch (PDOException $e) {
    error_log("Admin auth check error: " . $e->getMessage());
    header("Location: /pages/login.php?error=server_error");
    exit();
}

// Optional: Refresh session activity timestamp
$_SESSION['last_activity'] = time();

// Optional: Session timeout check (30 minutes of inactivity)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    // Last request was more than 30 minutes ago
    session_unset();
    session_destroy();
    header("Location: /pages/login.php?error=timeout");
    exit();
}
