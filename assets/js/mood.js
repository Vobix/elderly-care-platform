// Enhanced mood selection visual feedback
document.querySelectorAll('.mood-button').forEach(button => {
    button.addEventListener('click', function() {
        // Remove selected class from all buttons
        document.querySelectorAll('.mood-button').forEach(b => {
            b.classList.remove('selected');
        });
        
        // Add selected class to clicked button
        this.classList.add('selected');
        
        // Also check the radio button inside
        const radio = this.querySelector('input[type="radio"]');
        if (radio) {
            radio.checked = true;
        }
        
        // Scroll the selected mood into view (helpful on mobile)
        this.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    });
});

// Also handle when clicking directly on the label
document.querySelectorAll('.mood-option label').forEach(label => {
    label.addEventListener('change', function() {
        const button = this.closest('.mood-option').querySelector('.mood-button');
        if (button) {
            // Trigger the button's click handler
            document.querySelectorAll('.mood-button').forEach(b => {
                b.classList.remove('selected');
            });
            button.classList.add('selected');
        }
    });
});