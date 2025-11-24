<?php
/**
 * Profile Page
 * View and edit user profile details
 */

require_once __DIR__ . '/auth.php'; // Protect page
require_once __DIR__ . '/../../database/functions.php';

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Get user data
$user = getUserById($user_id);
$profile = getUserProfile($user_id);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    $date_of_birth = $_POST['date_of_birth'] ?? null;
    $gender = $_POST['gender'] ?? '';
    
    // Handle avatar upload
    $avatar = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['avatar']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $upload_dir = __DIR__ . '/../../assets/images/avatars/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $avatar_filename = 'user_' . $user_id . '_' . time() . '.' . $file_extension;
            $avatar_path = $upload_dir . $avatar_filename;
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar_path)) {
                $avatar = '/assets/images/avatars/' . $avatar_filename;
                
                // Delete old avatar if exists
                if (!empty($profile['avatar_url']) && file_exists(__DIR__ . '/../../' . $profile['avatar_url'])) {
                    unlink(__DIR__ . '/../../' . $profile['avatar_url']);
                }
            } else {
                $error = "Failed to upload avatar.";
            }
        } else {
            $error = "Invalid file type. Please upload JPG, PNG, or GIF.";
        }
    }
    
    // Update profile
    if (empty($error)) {
        try {
            updateProfile($user_id, $full_name, $date_of_birth, $gender, $avatar);
            $success = "Profile updated successfully!";
            
            // Refresh profile data
            $profile = getUserProfile($user_id);
        } catch (Exception $e) {
            $error = "Failed to update profile.";
            error_log("Profile update error: " . $e->getMessage());
        }
    }
}

// Get settings for display
$settings = getUserSettings($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Elderly Care Platform</title>
    <link rel="stylesheet" href="/assets/css/header.css">
    <link rel="stylesheet" href="/assets/css/footer.css">
    <link rel="stylesheet" href="/assets/css/profile.css">
</head>
<body>
    <div class="profile-container">
        <div class="nav-links">
            <a href="/pages/insights/dashboard.php">← Dashboard</a>
            <a href="settings.php">Settings</a>
            <a href="logout.php">Logout</a>
        </div>
        
        <div class="profile-header">
            <div class="avatar-section">
                <?php if (!empty($profile['avatar_url'])): ?>
                    <img src="<?php echo htmlspecialchars($profile['avatar_url']); ?>" alt="Avatar" class="avatar-preview">
                <?php else: ?>
                    <div class="avatar-placeholder">👤</div>
                <?php endif; ?>
            </div>
            <div class="profile-info">
                <h1><?php echo !empty($profile['full_name']) ? htmlspecialchars($profile['full_name']) : htmlspecialchars($user['email']); ?></h1>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Member since:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
            </div>
        </div>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" enctype="multipart/form-data">
            <h2>Edit Profile</h2>
            
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($profile['full_name'] ?? ''); ?>">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="date_of_birth">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($profile['date_of_birth'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender">
                        <option value="">Prefer not to say</option>
                        <option value="male" <?php echo ($profile['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                        <option value="female" <?php echo ($profile['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                        <option value="other" <?php echo ($profile['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="avatar">Update Avatar</label>
                <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/gif">
            </div>
            
            <button type="submit" class="btn-save">Save Changes</button>
        </form>
        
        <?php if ($settings): ?>
        <div class="accessibility-info">
            <h3>Current Accessibility Settings</h3>
            <ul>
                <li>High Contrast: <?php echo $settings['high_contrast'] ? '✓ Enabled' : '✗ Disabled'; ?></li>
                <li>Large Font: <?php echo $settings['large_font'] ? '✓ Enabled' : '✗ Disabled'; ?></li>
                <li>Voice Assistant: <?php echo $settings['voice_assistant'] ? '✓ Enabled' : '✗ Disabled'; ?></li>
                <li>Tap-Only Mode: <?php echo $settings['tap_only_mode'] ? '✓ Enabled' : '✗ Disabled'; ?></li>
            </ul>
            <p><a href="settings.php">Manage accessibility settings →</a></p>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
