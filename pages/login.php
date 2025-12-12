<?php
/**
 * Login Page
 * Handles user authentication
 */

session_start();
require_once __DIR__ . '/../database/functions.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: /pages/insights/dashboard.php");
    exit();
}

$error = '';
$success = '';

// Check for query parameters
if (isset($_GET['registered'])) {
    $success = "Registration successful! Please log in.";
}
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'unauthorized') {
        $error = "Please log in to access this page.";
    } elseif ($_GET['error'] === 'timeout') {
        $error = "Your session has expired. Please log in again.";
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate inputs
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        // Check database for user
        require_once __DIR__ . '/../database/config.php';
        
        try {
            $stmt = $pdo->prepare("SELECT user_id, username, email, password, is_admin, is_active, has_completed_initial_assessment FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Check if account is active
                if (!$user['is_active']) {
                    $error = "Your account has been deactivated. Please contact support.";
                } else {
                    // Password is correct - start session
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['is_admin'] = $user['is_admin'];
                    $_SESSION['last_activity'] = time();
                    
                    // Update last_login_at
                    $stmt = $pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE user_id = ?");
                    $stmt->execute([$user['user_id']]);
                    
                    // Check if user needs to complete baseline assessment
                    if (!$user['has_completed_initial_assessment']) {
                        header("Location: /pages/emotion/questionnaire.php?type=PHQ9&baseline=1");
                        exit();
                    }
                    
                    // Redirect based on role
                    if ($user['is_admin']) {
                        header("Location: /pages/admin/index.php");
                    } else {
                        header("Location: /pages/insights/dashboard.php");
                    }
                    exit();
                }
            } else {
                // Invalid credentials
                $error = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $error = "An error occurred. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Elderly Care Platform</title>
    <link rel="stylesheet" href="/assets/css/header.css">
    <link rel="stylesheet" href="/assets/css/footer.css">
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body>
    <div class="login-container">
        <h1>Welcome Back!</h1>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn-login">Log In</button>
        </form>
        
        <div class="register-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>
</body>
</html>
