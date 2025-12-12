/**
 * Admin Panel JavaScript
 * Handles confirmation dialogs, AJAX operations, and interactive features
 */

// C1: Confirmation dialogs for user status changes
function confirmDeactivate(userId, username) {
    if (confirm(`Are you sure you want to deactivate ${username}?\n\nThis will prevent the user from logging in.`)) {
        document.getElementById('formAction').value = 'deactivate';
        document.getElementById('formUserId').value = userId;
        document.getElementById('statusForm').submit();
    }
}

function confirmReactivate(userId, username) {
    if (confirm(`Are you sure you want to reactivate ${username}?\n\nThis will allow the user to log in again.`)) {
        document.getElementById('formAction').value = 'reactivate';
        document.getElementById('formUserId').value = userId;
        document.getElementById('statusForm').submit();
    }
}

// C1: Confirmation dialogs for content management
function confirmDeleteGame(gameId, gameName) {
    if (confirm(`Are you sure you want to delete the game "${gameName}"?\n\nThis action cannot be undone.`)) {
        document.getElementById('deleteAction').value = 'delete_game';
        document.getElementById('deleteId').value = gameId;
        document.getElementById('deleteForm').submit();
    }
}

function confirmDeleteQuestionnaire(questionnaireId, questionnaireName) {
    if (confirm(`Are you sure you want to delete "${questionnaireName}"?\n\nThis action cannot be undone.`)) {
        document.getElementById('deleteAction').value = 'delete_questionnaire';
        document.getElementById('deleteId').value = questionnaireId;
        document.getElementById('deleteForm').submit();
    }
}

// Form validation
function validateGameForm() {
    const name = document.getElementById('game_name').value.trim();
    const code = document.getElementById('game_code').value.trim();
    
    if (!name || !code) {
        alert('Invalid Content. Please check all the fields are filled with correct value.'); // M5
        return false;
    }
    
    return true;
}

function validateQuestionnaireForm() {
    const name = document.getElementById('questionnaire_name').value.trim();
    const type = document.getElementById('questionnaire_type').value.trim();
    
    if (!name || !type) {
        alert('Invalid Content. Please check all the fields are filled with correct value.'); // M5
        return false;
    }
    
    return true;
}

// Auto-dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});

// Search/filter form auto-submit on enter
document.addEventListener('DOMContentLoaded', function() {
    const searchInputs = document.querySelectorAll('.filter-form input[type="text"]');
    searchInputs.forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.target.closest('form').submit();
            }
        });
    });
});

// Confirm before leaving page with unsaved changes
let formChanged = false;

document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form[data-confirm-leave]');
    
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('change', () => {
                formChanged = true;
            });
        });
        
        form.addEventListener('submit', () => {
            formChanged = false;
        });
    });
});

window.addEventListener('beforeunload', function(e) {
    if (formChanged) {
        e.preventDefault();
        e.returnValue = '';
        return '';
    }
});

// Chart helper for analytics (if using charts)
function createSimpleBarChart(canvasId, labels, data, title) {
    // Placeholder for chart library integration (e.g., Chart.js)
    console.log('Chart:', title, labels, data);
}

// Utility: Format numbers
function formatNumber(num) {
    return new Intl.NumberFormat().format(num);
}

// Utility: Format dates
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

// Export analytics data as CSV
function exportToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = Array.from(cols).map(col => `"${col.textContent.trim()}"`);
        csv.push(rowData.join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.setAttribute('hidden', '');
    a.setAttribute('href', url);
    a.setAttribute('download', filename || 'export.csv');
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}
