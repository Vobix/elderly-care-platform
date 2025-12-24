<?php
/**
 * Settings Page
 * Accessibility settings for elderly users
 */

require_once __DIR__ . '/auth.php'; // Protect page
require_once __DIR__ . '/../../database/functions.php';

$page_title = "Accessibility Settings";

$user_id = $_SESSION['user_id'];
$success = '';
$error  = '';

// Get current settings
$settings = getUserSettings($user_id);

// Create settings if they don't exist
if (!$settings) {
    createDefaultSettings($user_id);
    $settings = getUserSettings($user_id);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $high_contrast   = isset($_POST['high_contrast']) ? 1 : 0;
    $large_font      = isset($_POST['large_font']) ? 1 : 0;
    $voice_assistant = isset($_POST['voice_assistant']) ? 1 : 0;
    $tap_only_mode   = isset($_POST['tap_only_mode']) ? 1 : 0;

    try {
        updateSettings($user_id, $high_contrast, $large_font, $voice_assistant, $tap_only_mode);
        $success = "Settings saved successfully!";

        // Store in session for immediate application
        $_SESSION['settings'] = [
            'high_contrast'   => $high_contrast,
            'large_font'      => $large_font,
            'voice_assistant' => $voice_assistant,
            'tap_only_mode'   => $tap_only_mode
        ];

        // Refresh settings from DB
        $settings = getUserSettings($user_id);
    } catch (Exception $e) {
        $error = "Failed to save settings.";
        error_log("Settings update error: " . $e->getMessage());
    }
}

// Apply settings to session if not already set
if (!isset($_SESSION['settings'])) {
    $_SESSION['settings'] = [
        'high_contrast'   => $settings['high_contrast'],
        'large_font'      => $settings['large_font'],
        'voice_assistant' => $settings['voice_assistant'],
        'tap_only_mode'   => $settings['tap_only_mode']
    ];
}

// Include global header (this gives you the purple nav bar)
require_once __DIR__ . '/../../_header.php';
?>

<link rel="stylesheet" href="/assets/css/settings.css">
<style>
    /* Base size for this page, affected by the Large Font setting */
    body {
        font-size: <?php echo $settings['large_font'] ? '130%' : '100%'; ?>;
    }
</style>

<div class="settings-container">
    <h1>Accessibility Settings</h1>
    <p class="subtitle">Customize your experience for better comfort and usability</p>

    <?php if ($success): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="setting-card">
            <div class="icon">🎨</div>
            <h2>High Contrast Mode</h2>
            <p>Increases color contrast for better visibility. Perfect for users with vision difficulties.</p>
            <div class="toggle-switch">
                <input type="checkbox" id="high_contrast" name="high_contrast"
                       <?php echo $settings['high_contrast'] ? 'checked' : ''; ?>>
                <label for="high_contrast">Enable High Contrast</label>
            </div>
        </div>

        <div class="setting-card">
            <div class="icon">🔤</div>
            <h2>Large Font</h2>
            <p>Makes all text 30% larger for easier reading.</p>
            <div class="toggle-switch">
                <input type="checkbox" id="large_font" name="large_font"
                       <?php echo $settings['large_font'] ? 'checked' : ''; ?>>
                <label for="large_font">Enable Large Font</label>
            </div>
        </div>

        <div class="setting-card">
            <div class="icon">🔊</div>
            <h2>Voice Assistant</h2>
            <p>Enables text-to-speech for reading content aloud. Helpful for users who prefer audio.</p>
            <div class="toggle-switch">
                <input type="checkbox" id="voice_assistant" name="voice_assistant"
                       <?php echo $settings['voice_assistant'] ? 'checked' : ''; ?>>
                <label for="voice_assistant">Enable Voice Assistant</label>
            </div>
        </div>

        <div class="setting-card">
            <div class="icon">👆</div>
            <h2>Tap-Only Mode</h2>
            <p>Simplifies interactions - removes hover effects and complex gestures. Use simple taps only.</p>
            <div class="toggle-switch">
                <input type="checkbox" id="tap_only_mode" name="tap_only_mode"
                       <?php echo $settings['tap_only_mode'] ? 'checked' : ''; ?>>
                <label for="tap_only_mode">Enable Tap-Only Mode</label>
            </div>
        </div>

        <button type="submit" class="btn-save">💾 Save Settings</button>
    </form>
</div>

<script>
    // Live preview for high contrast toggle
    document.getElementById('high_contrast').addEventListener('change', function() {
        if (this.checked) {
            document.documentElement.classList.add('high-contrast');
        } else {
            document.documentElement.classList.remove('high-contrast');
        }
    });

    // Live preview for large font toggle
    document.getElementById('large_font').addEventListener('change', function() {
        if (this.checked) {
            document.body.style.fontSize = '130%';   // make it bigger here if you want
        } else {
            document.body.style.fontSize = '100%';
        }
    });

    // Voice assistant toggle with immediate activation
    document.getElementById('voice_assistant').addEventListener('change', function() {
        if (window.voiceAssistant) {
            if (this.checked) {
                window.voiceAssistant.enable();
            } else {
                window.voiceAssistant.disable();
            }
        }
    });

    // Check voice assistant status on load
    window.addEventListener('load', function() {
        if (window.voiceAssistant) {
            const isEnabled = localStorage.getItem('voiceAssistantEnabled') === 'true';
            const checkbox = document.getElementById('voice_assistant');
            
            // Sync checkbox with actual state
            if (isEnabled && !checkbox.checked) {
                checkbox.checked = true;
            } else if (!isEnabled && checkbox.checked) {
                checkbox.checked = false;
            }
            
            // If enabled, activate it
            if (isEnabled) {
                window.voiceAssistant.enable();
            }
        }
    });
</script>

<?php require_once __DIR__ . '/../../_footer.php'; ?>
