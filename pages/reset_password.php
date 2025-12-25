<?php
session_start();
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../database/functions.php';
require_once __DIR__ . '/../database/email_config.php';

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

$error = '';
$success = '';
$step = 1; // 1=Email, 2=Verify Code, 3=Reset Password

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

/*
|--------------------------------------------------------------------------
| STEP 1: Enter Email
|--------------------------------------------------------------------------
*/
if ($method === 'POST' && isset($_POST['email'])) {
    $email = sanitizeInput($_POST['email']);

    if (empty($email)) {
        $error = "Please enter your email.";
    } else {
        $stmt = $pdo->prepare("SELECT user_id, username FROM users WHERE email = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['reset_user_id'] = $user['user_id'];
            $_SESSION['reset_email'] = $email;

            // Generate 6-digit verification code
            $_SESSION['reset_code'] = rand(100000, 999999);
            $_SESSION['reset_code_time'] = time();
            $step = 2;

            // Send Email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = SMTP_HOST;
                $mail->SMTPAuth   = SMTP_AUTH;
                $mail->Username   = SMTP_USERNAME;
                $mail->Password   = SMTP_PASSWORD;
                $mail->SMTPSecure = SMTP_SECURE;
                $mail->Port       = SMTP_PORT;

                // Sender
                $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
                // Recipient
                $mail->addAddress($email, $user['username']);

                // Email content
                $mail->isHTML(true);
                $mail->Subject = 'Your Password Reset Code';
                $mail->Body    = "Hello ".$user['username'].",<br>Your password reset code is: <b>".$_SESSION['reset_code']."</b><br>This code is valid for 10 minutes.";

                $mail->send();
                $success = "An identification code has been sent to your email.";
            } catch (Exception $e) {
                $error = "Failed to send email: {$mail->ErrorInfo}. Demo Code: ".$_SESSION['reset_code'];
            }

        } else {
            $error = "Email not found or account inactive.";
        }
    }
}

/*
|--------------------------------------------------------------------------
| STEP 2: Verify Code
|--------------------------------------------------------------------------
*/
if ($method === 'POST' && isset($_POST['verify_code'])) {
    if (!isset($_SESSION['reset_code'], $_SESSION['reset_user_id'])) {
        $error = "Session expired. Please start again.";
        $step = 1;
    } else {
        $code = trim($_POST['verify_code']);
        $generated = $_SESSION['reset_code'];
        $generated_time = $_SESSION['reset_code_time'] ?? 0;

        if (time() - $generated_time > 600) {
            unset($_SESSION['reset_code'], $_SESSION['reset_code_time']);
            $error = "Code expired. Please request again.";
            $step = 1;
        } elseif ($code != $generated) {
            $error = "Incorrect code. Please try again.";
            $step = 2;
        } else {
            $_SESSION['reset_verified'] = true;
            unset($_SESSION['reset_code'], $_SESSION['reset_code_time']);
            $success = "Code verified. You can now reset your password.";
            $step = 3;
        }
    }
}

/*
|--------------------------------------------------------------------------
| STEP 3: Reset Password
|--------------------------------------------------------------------------
*/
if ($method === 'POST' && isset($_POST['new_password'])) {
    if (!isset($_SESSION['reset_verified'], $_SESSION['reset_user_id'])) {
        $error = "Session expired. Please start again.";
        $step = 1;
    } else {
        $newPassword     = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        if (empty($newPassword) || empty($confirmPassword)) {
            $error = "Please fill in all fields.";
            $step = 3;
        } elseif ($newPassword !== $confirmPassword) {
            $error = "Passwords do not match.";
            $step = 3;
        } elseif (strlen($newPassword) < 6) {
            $error = "Password must be at least 6 characters.";
            $step = 3;
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $stmt->execute([ $hashedPassword, $_SESSION['reset_user_id'] ]);

            unset($_SESSION['reset_user_id'], $_SESSION['reset_email'], $_SESSION['reset_verified']);

            $success = "Password reset successful. You may now log in.";
            $step = 4;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - Mind Mosaic</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/header.css">
    <link rel="stylesheet" href="/assets/css/footer.css">
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body>

<div class="login-container">
    <h1>Reset Password</h1>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success && $step != 4): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- Step 1: Enter Email -->
    <?php if ($step === 1): ?>
        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required autofocus>
            </div>
            <button type="submit" class="btn-login">Request Code</button>
        </form>
    <?php endif; ?>

    <!-- Step 2: Enter Verification Code -->
    <?php if ($step === 2): ?>
        <form method="POST">
            <div class="form-group">
                <label>Identification Code</label>
                <input type="text" name="verify_code" required autofocus>
            </div>
            <button type="submit" class="btn-login">Verify Code</button>
        </form>
    <?php endif; ?>

    <!-- Step 3: Reset Password -->
    <?php if ($step === 3): ?>
        <form method="POST">
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" required autofocus>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn-login">Reset Password</button>
        </form>
    <?php endif; ?>

    <!-- Step 4: Complete -->
    <?php if ($step === 4): ?>
        <div class="success">
            <?= htmlspecialchars($success) ?><br>
            <a href="login.php">Back to Login</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>