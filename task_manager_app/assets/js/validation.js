// JavaScript validation functions
document.addEventListener('DOMContentLoaded', function() {
    // Registration form validation
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            if (!validateRegistrationForm()) {
                e.preventDefault();
            }
        });
    }
    
    // Login form validation
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            if (!validateLoginForm()) {
                e.preventDefault();
            }
        });
    }
    
    // Task form validation
    const taskForm = document.getElementById('taskForm');
    if (taskForm) {
        taskForm.addEventListener('submit', function(e) {
            if (!validateTaskForm()) {
                e.preventDefault();
            }
        });
    }
    
    // Real-time password confirmation validation
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    if (password && confirmPassword) {
        confirmPassword.addEventListener('input', function() {
            validatePasswordMatch();
        });
    }
    
    // Real-time email validation
    const emailField = document.getElementById('email');
    if (emailField) {
        emailField.addEventListener('blur', function() {
            validateEmail(emailField.value);
        });
    }
});

// Registration form validation
function validateRegistrationForm() {
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    let isValid = true;
    let errorMessage = '';
    
    // Clear previous error messages
    clearErrorMessages();
    
    // Validate name
    if (name === '') {
        showFieldError('name', 'Name is required');
        isValid = false;
    } else if (name.length < 2) {
        showFieldError('name', 'Name must be at least 2 characters long');
        isValid = false;
    }
    
    // Validate email
    if (email === '') {
        showFieldError('email', 'Email is required');
        isValid = false;
    } else if (!isValidEmail(email)) {
        showFieldError('email', 'Please enter a valid email address');
        isValid = false;
    }
    
    // Validate password
    if (password === '') {
        showFieldError('password', 'Password is required');
        isValid = false;
    } else if (password.length < 6) {
        showFieldError('password', 'Password must be at least 6 characters long');
        isValid = false;
    }
    
    // Validate confirm password
    if (confirmPassword === '') {
        showFieldError('confirm_password', 'Please confirm your password');
        isValid = false;
    } else if (password !== confirmPassword) {
        showFieldError('confirm_password', 'Passwords do not match');
        isValid = false;
    }
    
    if (!isValid) {
        showAlert('Please fix the errors below', 'danger');
    }
    
    return isValid;
}

// Login form validation
function validateLoginForm() {
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    
    let isValid = true;
    
    // Clear previous error messages
    clearErrorMessages();
    
    // Validate email
    if (email === '') {
        showFieldError('email', 'Email is required');
        isValid = false;
    } else if (!isValidEmail(email)) {
        showFieldError('email', 'Please enter a valid email address');
        isValid = false;
    }
    
    // Validate password
    if (password === '') {
        showFieldError('password', 'Password is required');
        isValid = false;
    }
    
    if (!isValid) {
        showAlert('Please fix the errors below', 'danger');
    }
    
    return isValid;
}

// Task form validation
function validateTaskForm() {
    const title = document.getElementById('title').value.trim();
    const dueDate = document.getElementById('due_date').value;
    
    let isValid = true;
    
    // Clear previous error messages
    clearErrorMessages();
    
    // Validate title
    if (title === '') {
        showFieldError('title', 'Task title is required');
        isValid = false;
    } else if (title.length < 3) {
        showFieldError('title', 'Task title must be at least 3 characters long');
        isValid = false;
    }
    
    // Validate due date (if provided)
    if (dueDate !== '') {
        const selectedDate = new Date(dueDate);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (selectedDate < today) {
            showFieldError('due_date', 'Due date cannot be in the past');
            isValid = false;
        }
    }
    
    if (!isValid) {
        showAlert('Please fix the errors below', 'danger');
    }
    
    return isValid;
}

// Email validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Real-time email validation
function validateEmail(email) {
    const emailField = document.getElementById('email');
    if (email && !isValidEmail(email)) {
        showFieldError('email', 'Please enter a valid email address');
        return false;
    } else {
        clearFieldError('email');
        return true;
    }
}

// Real-time password match validation
function validatePasswordMatch() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (confirmPassword !== '' && password !== confirmPassword) {
        showFieldError('confirm_password', 'Passwords do not match');
        return false;
    } else {
        clearFieldError('confirm_password');
        return true;
    }
}

// Show field error
function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    const errorDiv = document.getElementById(fieldId + '_error');
    
    // Add error class to field
    field.classList.add('is-invalid');
    
    // Create or update error message
    if (errorDiv) {
        errorDiv.textContent = message;
    } else {
        const errorElement = document.createElement('div');
        errorElement.id = fieldId + '_error';
        errorElement.className = 'invalid-feedback';
        errorElement.textContent = message;
        field.parentNode.appendChild(errorElement);
    }
}

// Clear field error
function clearFieldError(fieldId) {
    const field = document.getElementById(fieldId);
    const errorDiv = document.getElementById(fieldId + '_error');
    
    // Remove error class from field
    field.classList.remove('is-invalid');
    
    // Remove error message
    if (errorDiv) {
        errorDiv.remove();
    }
}

// Clear all error messages
function clearErrorMessages() {
    const errorElements = document.querySelectorAll('.invalid-feedback');
    errorElements.forEach(element => element.remove());
    
    const invalidFields = document.querySelectorAll('.is-invalid');
    invalidFields.forEach(field => field.classList.remove('is-invalid'));
}

// Show alert message
function showAlert(message, type) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());
    
    // Create new alert
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert alert at the top of the form
    const form = document.querySelector('form');
    if (form) {
        form.parentNode.insertBefore(alertDiv, form);
    }
}

// Confirm delete action
function confirmDelete(message = 'Are you sure you want to delete this item?') {
    return confirm(message);
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert.classList.contains('show')) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    });
});
