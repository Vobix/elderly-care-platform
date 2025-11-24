// Mood selection visual feedback
document.querySelectorAll('.mood-button').forEach(button => {
    button.addEventListener('click', function() {
        document.querySelectorAll('.mood-button').forEach(b => b.classList.remove('selected'));
        this.classList.add('selected');
    });
});
