<?php
/**
 * Hobbies Page - Placeholder for future expansion
 */

$page_title = "Hobbies & Interests";
require_once __DIR__ . '/account/auth.php';
require_once __DIR__ . '/../_header.php';
?>

<link rel="stylesheet" href="/assets/css/hobbies.css">

<div class="hobbies-container">
    <h1 style="font-size: 42px; margin-bottom: 20px;">🎨 Hobbies & Interests</h1>
    <p style="font-size: 18px; color: #666; margin-bottom: 40px;">
        Explore activities that bring you joy and keep your mind engaged
    </p>
    
    <div class="alert alert-info">
        🚧 This feature is coming soon! We're working on adding hobby tracking and recommendations.
    </div>
    
    <div class="hobby-grid">
        <div class="hobby-card">
            <div class="icon">📚</div>
            <h3>Reading</h3>
            <p>Track your reading progress and discover new books</p>
        </div>
        
        <div class="hobby-card">
            <div class="icon">🎨</div>
            <h3>Arts & Crafts</h3>
            <p>Share your creative projects and get inspiration</p>
        </div>
        
        <div class="hobby-card">
            <div class="icon">🌱</div>
            <h3>Gardening</h3>
            <p>Log your gardening activities and plant care</p>
        </div>
        
        <div class="hobby-card">
            <div class="icon">🎵</div>
            <h3>Music</h3>
            <p>Track listening habits and discover new music</p>
        </div>
        
        <div class="hobby-card">
            <div class="icon">👥</div>
            <h3>Social Activities</h3>
            <p>Connect with others who share your interests</p>
        </div>
        
        <div class="hobby-card">
            <div class="icon">🚶</div>
            <h3>Exercise</h3>
            <p>Log physical activities and stay active</p>
        </div>
    </div>
    
    <div style="margin-top: 40px;">
        <a href="insights/dashboard.php" class="btn btn-primary">← Back to Dashboard</a>
    </div>
</div>

<?php require_once __DIR__ . '/../_footer.php'; ?>
