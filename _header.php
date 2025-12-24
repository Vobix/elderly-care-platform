<?php
/**
 * Global Header
 * Reusable header with navigation and accessibility features
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get user settings if logged in
$user_settings = null;
if (isset($_SESSION['user_id'])) {
    if (!isset($_SESSION['settings'])) {
        require_once __DIR__ . '/database/functions.php';
        $user_settings = getUserSettings($_SESSION['user_id']);
        if ($user_settings) {
            $_SESSION['settings'] = $user_settings;
        }
    } else {
        $user_settings = $_SESSION['settings'];
    }
}

// Apply accessibility classes
$body_classes = [];
if ($user_settings) {
    if ($user_settings['high_contrast']) $body_classes[] = 'high-contrast';
    if ($user_settings['large_font']) $body_classes[] = 'large-font';
    if ($user_settings['tap_only_mode']) $body_classes[] = 'tap-only';
}
$body_class = !empty($body_classes) ? implode(' ', $body_classes) : '';

// Determine current page
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Elderly Care Platform</title>
    
    <!-- Global Styles -->
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">
    <link rel="stylesheet" href="/assets/css/header.css">
    <link rel="stylesheet" href="/assets/css/footer.css">
    
    <?php if (isset($extra_css)): ?>
        <?php echo $extra_css; ?>
    <?php endif; ?>
</head>
<body class="<?php echo $body_class; ?>">
    
    <nav class="navbar">
        <div class="nav-container">
            <a href="/index.php" class="nav-brand">🏥 Elderly Care</a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <ul class="nav-menu">
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                        <!-- Admin has access to both sides -->
                        <li><a href="/pages/admin/index.php" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/admin/') !== false ? 'active' : ''; ?>" style="background: #667eea; color: white; padding: 8px 12px; border-radius: 5px;">⚙️ Admin Panel</a></li>
                    <?php endif; ?>
                    <li><a href="/pages/insights/dashboard.php" class="<?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">📊 Dashboard</a></li>
                    <li><a href="/pages/games.php" class="<?php echo $current_page === 'games.php' ? 'active' : ''; ?>">🎮 Games</a></li>
                    <li><a href="/pages/emotion/mood.php" class="<?php echo $current_page === 'mood.php' ? 'active' : ''; ?>">😊 Mood</a></li>
                    <li><a href="/pages/emotion/questionnaire_selection.php">📝 Assessments</a></li>
                    <li><a href="/pages/diary.php" class="<?php echo $current_page === 'diary.php' ? 'active' : ''; ?>">📔 Diary</a></li>
                    <li><a href="/pages/account/profile.php" class="<?php echo $current_page === 'profile.php' ? 'active' : ''; ?>">👤 Profile</a></li>
                    <li><a href="/pages/account/logout.php">🚪 Logout</a></li>
                </ul>
            <?php else: ?>
                <ul class="nav-menu">
                    <li><a href="/pages/login.php">Login</a></li>
                    <li><a href="/pages/register.php">Register</a></li>
                </ul>
            <?php endif; ?>
        </div>
    </nav>
    
    <div class="container">
