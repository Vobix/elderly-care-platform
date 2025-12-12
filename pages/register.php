<?php
/**
 * Registration Page
 * Creates new user accounts
 */

session_start();
require_once __DIR__ . '/../database/functions.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: /pages/insights/dashboard.php");
    exit();
}

$error = '';
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    
    // Validate inputs
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!isValidEmail($email)) {
        $errors[] = "Invalid email format.";
    } elseif (emailExists($email)) {
        $errors[] = "Email already registered.";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    
    // If no errors, create user
    if (empty($errors)) {
        require_once __DIR__ . '/../database/config.php';
        require_once __DIR__ . '/../database/dao/UserDAO.php';
        
        try {
            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Generate username from email
            $username = sanitizeInput($_POST['username'] ?? explode('@', $email)[0]);
            
            // Create user using UserDAO
            $userDAO = new UserDAO($pdo);
            $user_id = $userDAO->create($username, $email, $password_hash, $full_name, null);
            
            // Create default settings
            $stmt = $pdo->prepare("INSERT INTO user_settings (user_id) VALUES (?)");
            $stmt->execute([$user_id]);
            
            // Log user in immediately
            $_SESSION['user_id'] = $user_id;
            $_SESSION['email'] = $email;
            $_SESSION['username'] = $username;
            $_SESSION['last_activity'] = time();
            
            // Redirect to baseline assessment (PHQ-9 questionnaire)
            header("Location: /pages/emotion/questionnaire.php?type=PHQ9&baseline=1");
            exit();
            
        } catch (Exception $e) {
            $error = "Registration failed. Please try again.";
            error_log("Registration error: " . $e->getMessage());
        }
    } else {
        $error = implode("<br>", $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Elderly Care Platform</title>
    <link rel="stylesheet" href="/assets/css/header.css">
    <link rel="stylesheet" href="/assets/css/footer.css">
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body>
    <div class="register-container">
        <h1>Create Account</h1>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                <small>Optional - you can add this later</small>
            </div>
            
            <div class="form-group">
                <label for="username">Username *</label>
                <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                <small>This will be displayed on leaderboards</small>
            </div>
            
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" required>
                <small>At least 6 characters</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn-register">Register</button>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="login.php">Log in here</a>
        </div>
    </div>
</body>
</html>
