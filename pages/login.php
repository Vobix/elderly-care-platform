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
        $user = getUserByEmail($email);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Password is correct - start session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['last_activity'] = time();
            
            // Redirect to dashboard
            header("Location: /pages/insights/dashboard.php");
            exit();
        } else {
            // Invalid credentials
            $error = "Invalid email or password.";
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
