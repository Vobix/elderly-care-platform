<?php
/**
 * Landing Page
 */

session_start();

// If logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: /pages/insights/dashboard.php");
    exit();
}

$page_title = "Welcome";
require_once __DIR__ . '/_header.php';
?>

<link rel="stylesheet" href="/assets/css/home.css">

<div class="hero">
    <h1>🏥 Elderly Care Platform</h1>
    <p>Comprehensive wellness tracking for seniors</p>
    <p style="font-size: 18px; margin-bottom: 30px;">
        Monitor mood, play cognitive games, and track your mental wellness
    </p>
    <div class="hero-buttons">
        <a href="/pages/register.php" class="hero-btn">Get Started</a>
        <a href="/pages/login.php" class="hero-btn" style="background: transparent; border: 3px solid white; color: white;">Log In</a>
    </div>
</div>

<h2 style="text-align: center; font-size: 36px; margin-bottom: 40px;">Features</h2>

<div class="features">
    <div class="feature-card">
        <div class="icon">😊</div>
        <h3>Mood Tracking</h3>
        <p>Log your daily emotions with simple emoji scales. Track patterns and understand your emotional wellness over time.</p>
    </div>
    
    <div class="feature-card">
        <div class="icon">🎮</div>
        <h3>Cognitive Games</h3>
        <p>Exercise your mind with fun games designed to improve memory, attention, reaction time, and problem-solving skills.</p>
    </div>
    
    <div class="feature-card">
        <div class="icon">📊</div>
        <h3>Analytics Dashboard</h3>
        <p>View comprehensive insights about your mood trends, game performance, and overall wellness progress.</p>
    </div>
    
    <div class="feature-card">
        <div class="icon">📋</div>
        <h3>Wellness Assessments</h3>
        <p>Take scientifically-backed questionnaires to evaluate your mental health, sleep quality, and stress levels.</p>
    </div>
    
    <div class="feature-card">
        <div class="icon">♿</div>
        <h3>Accessibility Features</h3>
        <p>High contrast mode, large fonts, voice assistant, and tap-only navigation designed specifically for elderly users.</p>
    </div>
    
    <div class="feature-card">
        <div class="icon">📔</div>
        <h3>Personal Diary</h3>
        <p>Keep a record of your moods and thoughts. Review your journey and celebrate your progress.</p>
    </div>
</div>

<div style="text-align: center; margin: 60px 0; padding: 40px; background: #e3f2fd; border-radius: 15px;">
    <h2 style="margin-bottom: 20px;">Ready to start your wellness journey?</h2>
    <a href="/pages/register.php" class="btn btn-primary" style="padding: 20px 40px; font-size: 22px;">Create Free Account</a>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
