    </div> <!-- Close container -->
    
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Elderly Care Platform. All rights reserved.</p>
        <p>
            <a href="/pages/account/settings.php">Accessibility Settings</a>
        </p>
    </footer>
    
    <!-- Global Scripts -->
    <script src="/assets/js/footer.js"></script>
    
    <?php if (isset($extra_js)): ?>
        <?php echo $extra_js; ?>
    <?php endif; ?>
    
    <script>
        // Initialize voice assistant if enabled
        <?php if ($user_settings && $user_settings['voice_assistant']): ?>
        initVoiceAssistant(true);
        <?php endif; ?>
    </script>
    
</body>
</html>
