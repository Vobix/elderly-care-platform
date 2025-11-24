<?php
/**
 * Authentication Helper
 * Protects pages - only logged-in users can access
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
