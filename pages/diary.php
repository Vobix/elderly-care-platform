<?php
/**
 * Personal Diary Page - Write and view diary entries
 */

$page_title = "My Diary";
require_once __DIR__ . '/account/auth.php';
require_once __DIR__ . '/../_header.php';
require_once __DIR__ . '/../database/config.php';

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle new diary entry submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['diary_entry'])) {
    $title = trim($_POST['title'] ?? '');
    $entry = trim($_POST['diary_entry'] ?? '');
    
    if (empty($entry)) {
        $error = "Please write something in your diary entry.";
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO diary_entries (user_id, title, entry_text, created_at) 
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$user_id, $title, $entry]);
            $success = "Diary entry saved successfully!";
            
            // Clear form
            $_POST = [];
        } catch (PDOException $e) {
            error_log("Diary save error: " . $e->getMessage());
            $error = "Failed to save diary entry. Please try again.";
        }
    }
}

// Handle delete entry
if (isset($_GET['delete'])) {
    $entry_id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM diary_entries WHERE entry_id = ? AND user_id = ?");
        $stmt->execute([$entry_id, $user_id]);
        $success = "Diary entry deleted successfully!";
        header("Location: diary.php");
        exit();
    } catch (PDOException $e) {
        error_log("Diary delete error: " . $e->getMessage());
        $error = "Failed to delete entry.";
    }
}

// Get all diary entries
try {
    $stmt = $pdo->prepare("
        SELECT * FROM diary_entries 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$user_id]);
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Diary fetch error: " . $e->getMessage());
    $entries = [];
}
?>

<link rel="stylesheet" href="/assets/css/diary.css">

<div class="diary-container">
    <h1 class="diary-title">📔 My Personal Diary</h1>
    <p class="diary-subtitle">
        Write your thoughts, feelings, and daily experiences
    </p>

    <?php if ($success): ?>
        <div class="alert alert-success">✅ <?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error">❌ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- New Entry Form -->
    <div class="diary-form-card">
        <h2 class="form-title">✍️ Write New Entry</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="title" class="form-label">
                    📝 Title (Optional)
                </label>
                <input type="text" 
                       id="title" 
                       name="title" 
                       class="form-input"
                       placeholder="Give your entry a title...">
            </div>

            <div class="form-group">
                <label for="diary_entry" class="form-label">
                    💭 Your Diary Entry *
                </label>
                <textarea id="diary_entry" 
                          name="diary_entry" 
                          required
                          class="form-textarea"
                          placeholder="What's on your mind today? Share your thoughts, feelings, experiences, or anything you'd like to remember..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-save">
                💾 Save Entry
            </button>
        </form>
    </div>

    <!-- Previous Entries -->
    <?php if (!empty($entries)): ?>
        <h2 class="entries-heading">📚 Previous Entries</h2>
        <div class="diary-entries">
            <?php foreach ($entries as $entry): ?>
                <div class="diary-entry-card">
                    <div class="entry-header">
                        <div class="entry-meta">
                            <?php if (!empty($entry['title'])): ?>
                                <h3 class="entry-title">
                                    <?php echo htmlspecialchars($entry['title']); ?>
                                </h3>
                            <?php endif; ?>
                            <p class="entry-date">
                                📅 <?php echo date('l, F j, Y', strtotime($entry['created_at'])); ?>
                                at <?php echo date('g:i A', strtotime($entry['created_at'])); ?>
                            </p>
                        </div>
                        <a href="?delete=<?php echo $entry['entry_id']; ?>" 
                           onclick="return confirm('Are you sure you want to delete this diary entry?')"
                           class="delete-btn"
                           title="Delete entry">
                            🗑️
                        </a>
                    </div>
                    <div class="entry-text">
                        <?php echo nl2br(htmlspecialchars($entry['entry_text'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info empty-state">
            <h3>No diary entries yet</h3>
            <p>Start writing your first entry above to keep track of your thoughts and experiences!</p>
        </div>
    <?php endif; ?>

    <div class="action-buttons">
        <a href="emotion/mood.php" class="btn btn-secondary">😊 Log Mood Instead</a>
        <a href="insights/dashboard.php" class="btn btn-secondary">📊 View Dashboard</a>
    </div>
</div>

<?php require_once __DIR__ . '/../_footer.php'; ?>