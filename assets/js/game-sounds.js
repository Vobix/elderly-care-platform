/**
 * Game Sound Effects Utility
 * Simple, clear audio feedback for elderly users
 */

class GameSounds {
    constructor() {
        this.audioContext = null;
        this.masterVolume = 0.3; // Keep sounds gentle
        this.enabled = true;
        this.initAudioContext();
    }
    
    initAudioContext() {
        try {
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
        } catch (e) {
            console.warn('Web Audio API not supported');
            this.enabled = false;
        }
    }
    
    // Resume audio context (required for user interaction)
    resume() {
        if (this.audioContext && this.audioContext.state === 'suspended') {
            this.audioContext.resume();
        }
    }
    
    // Success sound - happy, encouraging tone
    playSuccess() {
        if (!this.enabled) return;
        this.resume();
        
        const now = this.audioContext.currentTime;
        const oscillator = this.audioContext.createOscillator();
        const gainNode = this.audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(this.audioContext.destination);
        
        // Happy ascending tone
        oscillator.frequency.setValueAtTime(523.25, now); // C5
        oscillator.frequency.setValueAtTime(659.25, now + 0.1); // E5
        oscillator.frequency.setValueAtTime(783.99, now + 0.2); // G5
        
        oscillator.type = 'sine';
        gainNode.gain.setValueAtTime(this.masterVolume, now);
        gainNode.gain.exponentialRampToValueAtTime(0.01, now + 0.4);
        
        oscillator.start(now);
        oscillator.stop(now + 0.4);
    }
    
    // Error sound - gentle, clear indication
    playError() {
        if (!this.enabled) return;
        this.resume();
        
        const now = this.audioContext.currentTime;
        const oscillator = this.audioContext.createOscillator();
        const gainNode = this.audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(this.audioContext.destination);
        
        // Descending tone
        oscillator.frequency.setValueAtTime(400, now);
        oscillator.frequency.setValueAtTime(300, now + 0.15);
        
        oscillator.type = 'sine';
        gainNode.gain.setValueAtTime(this.masterVolume * 0.7, now);
        gainNode.gain.exponentialRampToValueAtTime(0.01, now + 0.3);
        
        oscillator.start(now);
        oscillator.stop(now + 0.3);
    }
    
    // Click sound - brief confirmation
    playClick() {
        if (!this.enabled) return;
        this.resume();
        
        const now = this.audioContext.currentTime;
        const oscillator = this.audioContext.createOscillator();
        const gainNode = this.audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(this.audioContext.destination);
        
        oscillator.frequency.setValueAtTime(800, now);
        oscillator.type = 'sine';
        gainNode.gain.setValueAtTime(this.masterVolume * 0.5, now);
        gainNode.gain.exponentialRampToValueAtTime(0.01, now + 0.05);
        
        oscillator.start(now);
        oscillator.stop(now + 0.05);
    }
    
    // Achievement sound - celebratory
    playAchievement() {
        if (!this.enabled) return;
        this.resume();
        
        const now = this.audioContext.currentTime;
        const oscillator = this.audioContext.createOscillator();
        const gainNode = this.audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(this.audioContext.destination);
        
        // Triumphant sequence
        oscillator.frequency.setValueAtTime(523.25, now); // C5
        oscillator.frequency.setValueAtTime(659.25, now + 0.1); // E5
        oscillator.frequency.setValueAtTime(783.99, now + 0.2); // G5
        oscillator.frequency.setValueAtTime(1046.50, now + 0.3); // C6
        
        oscillator.type = 'triangle';
        gainNode.gain.setValueAtTime(this.masterVolume, now);
        gainNode.gain.exponentialRampToValueAtTime(0.01, now + 0.6);
        
        oscillator.start(now);
        oscillator.stop(now + 0.6);
    }
    
    // Move sound - for Tetris piece movement
    playMove() {
        if (!this.enabled) return;
        this.resume();
        
        const now = this.audioContext.currentTime;
        const oscillator = this.audioContext.createOscillator();
        const gainNode = this.audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(this.audioContext.destination);
        
        oscillator.frequency.setValueAtTime(600, now);
        oscillator.type = 'square';
        gainNode.gain.setValueAtTime(this.masterVolume * 0.3, now);
        gainNode.gain.exponentialRampToValueAtTime(0.01, now + 0.03);
        
        oscillator.start(now);
        oscillator.stop(now + 0.03);
    }
    
    // Rotate sound - for Tetris rotation
    playRotate() {
        if (!this.enabled) return;
        this.resume();
        
        const now = this.audioContext.currentTime;
        const oscillator = this.audioContext.createOscillator();
        const gainNode = this.audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(this.audioContext.destination);
        
        oscillator.frequency.setValueAtTime(700, now);
        oscillator.frequency.setValueAtTime(900, now + 0.05);
        oscillator.type = 'square';
        gainNode.gain.setValueAtTime(this.masterVolume * 0.3, now);
        gainNode.gain.exponentialRampToValueAtTime(0.01, now + 0.1);
        
        oscillator.start(now);
        oscillator.stop(now + 0.1);
    }
    
    // Drop sound - for Tetris hard drop
    playDrop() {
        if (!this.enabled) return;
        this.resume();
        
        const now = this.audioContext.currentTime;
        const oscillator = this.audioContext.createOscillator();
        const gainNode = this.audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(this.audioContext.destination);
        
        oscillator.frequency.setValueAtTime(1200, now);
        oscillator.frequency.exponentialRampToValueAtTime(200, now + 0.15);
        oscillator.type = 'sawtooth';
        gainNode.gain.setValueAtTime(this.masterVolume * 0.5, now);
        gainNode.gain.exponentialRampToValueAtTime(0.01, now + 0.15);
        
        oscillator.start(now);
        oscillator.stop(now + 0.15);
    }
    
    // Line clear sound - for Tetris
    playLineClear(numLines = 1) {
        if (!this.enabled) return;
        this.resume();
        
        const now = this.audioContext.currentTime;
        
        // More lines = more dramatic sound
        const frequencies = numLines === 4 ? [523, 659, 783, 1046] : 
                           numLines === 3 ? [523, 659, 783] :
                           numLines === 2 ? [523, 659] : [523];
        
        frequencies.forEach((freq, i) => {
            const oscillator = this.audioContext.createOscillator();
            const gainNode = this.audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(this.audioContext.destination);
            
            const time = now + (i * 0.08);
            oscillator.frequency.setValueAtTime(freq, time);
            oscillator.type = 'triangle';
            gainNode.gain.setValueAtTime(this.masterVolume * 0.6, time);
            gainNode.gain.exponentialRampToValueAtTime(0.01, time + 0.3);
            
            oscillator.start(time);
            oscillator.stop(time + 0.3);
        });
    }
    
    // Game over sound
    playGameOver() {
        if (!this.enabled) return;
        this.resume();
        
        const now = this.audioContext.currentTime;
        const oscillator = this.audioContext.createOscillator();
        const gainNode = this.audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(this.audioContext.destination);
        
        // Sad descending tone
        oscillator.frequency.setValueAtTime(523, now);
        oscillator.frequency.setValueAtTime(493, now + 0.2);
        oscillator.frequency.setValueAtTime(440, now + 0.4);
        oscillator.frequency.setValueAtTime(392, now + 0.6);
        
        oscillator.type = 'sine';
        gainNode.gain.setValueAtTime(this.masterVolume * 0.5, now);
        gainNode.gain.exponentialRampToValueAtTime(0.01, now + 1);
        
        oscillator.start(now);
        oscillator.stop(now + 1);
    }
    
    // Match sound - for card matching games
    playMatch() {
        this.playSuccess();
    }
    
    // No match sound - for card games
    playNoMatch() {
        this.playError();
    }
    
    // Level up sound
    playLevelUp() {
        this.playAchievement();
    }
    
    // Toggle sound on/off
    toggle() {
        this.enabled = !this.enabled;
        return this.enabled;
    }
    
    setVolume(volume) {
        this.masterVolume = Math.max(0, Math.min(1, volume));
    }
}

// Create global instance
const gameSounds = new GameSounds();
