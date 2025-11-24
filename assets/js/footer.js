/**
 * Footer JavaScript
 * Voice assistant and accessibility features
 */

// Voice assistant functionality
function initVoiceAssistant(enabled) {
    if (!enabled || !('speechSynthesis' in window)) {
        return;
    }
    
    function speak(text) {
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.rate = 0.9;
        utterance.pitch = 1;
        utterance.volume = 1;
        window.speechSynthesis.speak(utterance);
    }
    
    // Add click listeners to speak button text
    document.querySelectorAll('.btn, button').forEach(btn => {
        btn.addEventListener('click', function() {
            const text = this.textContent.trim();
            if (text) speak(text);
        });
    });
}

// Export for use in footer
if (typeof window !== 'undefined') {
    window.initVoiceAssistant = initVoiceAssistant;
}
