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

// C5: Mandatory Completion Rule - Check if user must complete baseline
// Exempt: admin users and baseline-related pages
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    $currentPage = basename($_SERVER['PHP_SELF']);
    $exemptPages = ['baseline_selection.php', 'questionnaire.php', 'questionnaire_result.php', 'logout.php'];
    
    if (!in_array($currentPage, $exemptPages)) {
        require_once __DIR__ . '/../../database/config.php';
        try {
            $stmt = $pdo->prepare("SELECT baseline_id FROM baseline_assessments WHERE user_id = ? LIMIT 1");
            $stmt->execute([$_SESSION['user_id']]);
            $hasBaseline = $stmt->fetchColumn();
            
            if (!$hasBaseline) {
                // Redirect to baseline selection if not completed
                header("Location: /pages/emotion/baseline_selection.php");
                exit();
            }
        } catch (PDOException $e) {
            error_log("Baseline check error: " . $e->getMessage());
        }
    }
}
