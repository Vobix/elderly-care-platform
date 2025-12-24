/**
 * Voice Assistant for Elderly Care Platform
 * Provides text-to-speech functionality for better accessibility
 */

class VoiceAssistant {
    constructor() {
        this.synth = window.speechSynthesis;
        this.enabled = false;
        this.currentUtterance = null;
        this.rate = 0.9; // Slower speech rate for elderly users
        this.pitch = 1.0;
        this.volume = 1.0;
        this.voice = null;
        
        this.init();
    }

    init() {
        // Check if speech synthesis is supported
        if (!this.synth) {
            console.warn('Speech synthesis not supported in this browser');
            return;
        }

        // Load preferred voice when voices are loaded
        if (this.synth.onvoiceschanged !== undefined) {
            this.synth.onvoiceschanged = () => this.loadVoice();
        }
        this.loadVoice();

        // Check localStorage for saved setting
        const savedSetting = localStorage.getItem('voiceAssistantEnabled');
        if (savedSetting === 'true') {
            this.enable();
        }
    }

    loadVoice() {
        const voices = this.synth.getVoices();
        // Prefer English voices, particularly US or UK English
        this.voice = voices.find(v => v.lang.startsWith('en-')) || voices[0];
    }

    enable() {
        this.enabled = true;
        localStorage.setItem('voiceAssistantEnabled', 'true');
        this.attachListeners();
        this.speak('Voice assistant enabled. I will read content as you navigate.');
    }

    disable() {
        this.enabled = false;
        localStorage.setItem('voiceAssistantEnabled', 'false');
        this.stop();
        this.removeListeners();
    }

    toggle() {
        if (this.enabled) {
            this.disable();
        } else {
            this.enable();
        }
    }

    speak(text, interrupt = true) {
        if (!this.enabled || !text || !this.synth) return;

        // Stop current speech if interrupting
        if (interrupt && this.synth.speaking) {
            this.synth.cancel();
        }

        // Clean and prepare text
        const cleanText = this.cleanText(text);
        if (!cleanText) return;

        // Create utterance
        this.currentUtterance = new SpeechSynthesisUtterance(cleanText);
        this.currentUtterance.voice = this.voice;
        this.currentUtterance.rate = this.rate;
        this.currentUtterance.pitch = this.pitch;
        this.currentUtterance.volume = this.volume;

        // Speak
        this.synth.speak(this.currentUtterance);
    }

    stop() {
        if (this.synth && this.synth.speaking) {
            this.synth.cancel();
        }
    }

    cleanText(text) {
        return text
            .replace(/[\u{1F300}-\u{1F9FF}]/gu, '') // Remove emojis
            .replace(/\s+/g, ' ') // Collapse whitespace
            .trim();
    }

    attachListeners() {
        // Speak button text on click
        document.addEventListener('click', this.handleClick.bind(this), true);
        
        // Speak link/button text on focus
        document.addEventListener('focus', this.handleFocus.bind(this), true);
        
        // Speak headings when scrolled into view
        this.observeHeadings();
    }

    removeListeners() {
        document.removeEventListener('click', this.handleClick.bind(this), true);
        document.removeEventListener('focus', this.handleFocus.bind(this), true);
    }

    handleClick(event) {
        if (!this.enabled) return;

        const target = event.target;
        const textToSpeak = this.getElementText(target);
        
        if (textToSpeak) {
            this.speak(textToSpeak);
        }
    }

    handleFocus(event) {
        if (!this.enabled) return;

        const target = event.target;
        if (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.tagName === 'BUTTON') {
            const label = this.getInputLabel(target);
            if (label) {
                this.speak(label);
            }
        }
    }

    getElementText(element) {
        // Priority order for getting text
        if (element.getAttribute('data-speak')) {
            return element.getAttribute('data-speak');
        }
        
        if (element.getAttribute('aria-label')) {
            return element.getAttribute('aria-label');
        }

        if (element.getAttribute('title')) {
            return element.getAttribute('title');
        }

        // For buttons and links
        if (element.tagName === 'BUTTON' || element.tagName === 'A') {
            return element.textContent;
        }

        // For navigation items
        if (element.closest('.nav-item') || element.closest('.nav-link')) {
            return element.textContent;
        }

        // For cards
        if (element.closest('.card') || element.closest('.game-card') || element.closest('.setting-card')) {
            const heading = element.querySelector('h2, h3, h4');
            if (heading) return heading.textContent;
        }

        return element.textContent || '';
    }

    getInputLabel(input) {
        // Check for associated label
        if (input.id) {
            const label = document.querySelector(`label[for="${input.id}"]`);
            if (label) return label.textContent;
        }

        // Check for aria-label
        if (input.getAttribute('aria-label')) {
            return input.getAttribute('aria-label');
        }

        // Check for placeholder
        if (input.placeholder) {
            return input.placeholder;
        }

        return input.name || '';
    }

    observeHeadings() {
        const headings = document.querySelectorAll('h1, h2, h3');
        const options = {
            root: null,
            threshold: 0.5
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && this.enabled) {
                    this.speak(entry.target.textContent, false);
                }
            });
        }, options);

        headings.forEach(heading => observer.observe(heading));
    }

    // Special method for game instructions
    speakGameInstructions(gameName, instructions) {
        if (!this.enabled) return;
        
        const intro = `Starting ${gameName}.`;
        const fullText = intro + ' ' + instructions;
        this.speak(fullText);
    }

    // Method to announce page changes
    announcePage(pageTitle, description = '') {
        if (!this.enabled) return;
        
        const announcement = `${pageTitle} page. ${description}`;
        this.speak(announcement);
    }
}

// Create global instance
window.voiceAssistant = new VoiceAssistant();

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check if voice assistant should be enabled from PHP session/cookie
    const voiceAssistantCheckbox = document.getElementById('voice_assistant');
    
    // If we're on settings page, sync the checkbox with localStorage
    if (voiceAssistantCheckbox) {
        const isEnabled = localStorage.getItem('voiceAssistantEnabled') === 'true';
        if (isEnabled && !voiceAssistantCheckbox.checked) {
            voiceAssistantCheckbox.checked = true;
        }
        
        // Add change listener for settings page
        voiceAssistantCheckbox.addEventListener('change', function() {
            if (this.checked) {
                window.voiceAssistant.enable();
            } else {
                window.voiceAssistant.disable();
            }
        });
    }

    // Auto-announce page title on load
    const pageTitle = document.querySelector('h1');
    if (pageTitle && window.voiceAssistant.enabled) {
        setTimeout(() => {
            window.voiceAssistant.announcePage(pageTitle.textContent);
        }, 500);
    }
});
