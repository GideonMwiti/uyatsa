// UYTSA System JavaScript

// Fix modal flicker for announcements
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-info[data-bs-toggle="modal"]').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
        });
    });

    // Prevent modal flicker when mouse leaves page
    document.querySelectorAll('.modal').forEach(function(modal) {
        modal.addEventListener('mouseleave', function(e) {
            // Only hide modal if mouse leaves window, not just modal
            if (!document.hasFocus() || e.relatedTarget === null) {
                var bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) bsModal.hide();
            }
        });
        modal.addEventListener('mouseout', function(e) {
            if (!document.hasFocus() || e.relatedTarget === null) {
                var bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) bsModal.hide();
            }
        });
    });
});

// DOM Ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Form validation
    validateForms();
    
    // Initialize datepickers
    initDatePickers();
    
    // Initialize file upload preview
    initFileUploadPreview();
    
    // Initialize search functionality
    initSearch();
    
    // Initialize notifications
    initNotifications();
});

// Form Validation
function validateForms() {
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
}

// Initialize Date Pickers
function initDatePickers() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(function(input) {
        if (!input.value) {
            const today = new Date().toISOString().split('T')[0];
            input.value = today;
        }
    });
}

// File Upload Preview
function initFileUploadPreview() {
    const fileInputs = document.querySelectorAll('input[type="file"][accept="image/*"]');
    
    fileInputs.forEach(function(input) {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            // Check file size (5MB limit)
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB');
                e.target.value = '';
                return;
            }
            
            // Check file type
            const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                alert('Only JPG, PNG, and GIF images are allowed');
                e.target.value = '';
                return;
            }
            
            // Create preview
            const reader = new FileReader();
            reader.onload = function(e) {
                // Look for existing preview container
                let previewContainer = input.parentNode.querySelector('.image-preview');
                if (!previewContainer) {
                    previewContainer = document.createElement('div');
                    previewContainer.className = 'image-preview mt-2';
                    input.parentNode.appendChild(previewContainer);
                }
                
                previewContainer.innerHTML = `
                    <img src="${e.target.result}" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                    <button type="button" class="btn btn-sm btn-danger mt-2 remove-preview">Remove</button>
                `;
                
                // Add remove functionality
                const removeBtn = previewContainer.querySelector('.remove-preview');
                removeBtn.addEventListener('click', function() {
                    previewContainer.remove();
                    input.value = '';
                });
            };
            reader.readAsDataURL(file);
        });
    });
}

// Search Functionality
function initSearch() {
    const searchInputs = document.querySelectorAll('.search-input');
    searchInputs.forEach(function(input) {
        let timeout;
        input.addEventListener('keyup', function(e) {
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                // Trigger search after 500ms of no typing
                if (e.target.value.length >= 2 || e.target.value.length === 0) {
                    e.target.form.submit();
                }
            }, 500);
        });
    });
}

// Notifications
function initNotifications() {
    // Mark notifications as read
    const notificationLinks = document.querySelectorAll('.notification-link');
    notificationLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            const notificationId = this.dataset.notificationId;
            if (notificationId) {
                markNotificationAsRead(notificationId);
            }
        });
    });
    
    // Real-time notification check (every 30 seconds)
    if (window.location.pathname.includes('dashboard')) {
        setInterval(checkNewNotifications, 30000);
    }
}

function markNotificationAsRead(notificationId) {
    fetch('../api/mark-notification-read.php?id=' + notificationId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (notificationElement) {
                    notificationElement.classList.remove('unread-notification');
                }
            }
        });
}

function checkNewNotifications() {
    fetch('../api/get-notifications-count.php')
        .then(response => response.json())
        .then(data => {
            if (data.unread > 0) {
                updateNotificationBadge(data.unread);
                // Show notification toast
                showNotificationToast('You have ' + data.unread + ' new notification' + (data.unread > 1 ? 's' : ''));
            }
        });
}

function updateNotificationBadge(count) {
    const badge = document.querySelector('.notification-badge');
    if (badge) {
        badge.textContent = count;
        badge.classList.remove('d-none');
    }
}

function showNotificationToast(message) {
    // Check if browser supports notifications
    if ('Notification' in window && Notification.permission === 'granted') {
        new Notification('UYTSA Notification', {
            body: message,
            icon: '../assets/images/logo.png'
        });
    } else {
        // Fallback to browser alert
        alert(message);
    }
}

// Request notification permission
function requestNotificationPermission() {
    if ('Notification' in window) {
        Notification.requestPermission().then(function(permission) {
            if (permission === 'granted') {
                console.log('Notification permission granted');
            }
        });
    }
}

// Password Strength Checker
function checkPasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    return strength;
}

// Update password strength meter
function updatePasswordStrength(password) {
    const strength = checkPasswordStrength(password);
    const meter = document.getElementById('password-strength-meter');
    const text = document.getElementById('password-strength-text');
    
    if (meter && text) {
        meter.value = strength;
        
        let message = '';
        let color = '';
        
        switch(strength) {
            case 0:
            case 1:
                message = 'Very Weak';
                color = 'danger';
                break;
            case 2:
                message = 'Weak';
                color = 'warning';
                break;
            case 3:
                message = 'Good';
                color = 'info';
                break;
            case 4:
                message = 'Strong';
                color = 'success';
                break;
            case 5:
                message = 'Very Strong';
                color = 'success';
                break;
        }
        
        text.textContent = message;
        text.className = `text-${color}`;
        meter.className = `password-strength-meter strength-${strength}`;
    }
}

// File Upload Progress
function showUploadProgress(form, progressId) {
    const formData = new FormData(form);
    const xhr = new XMLHttpRequest();
    
    // Create progress bar
    let progressBar = document.getElementById(progressId);
    if (!progressBar) {
        progressBar = document.createElement('div');
        progressBar.id = progressId;
        progressBar.className = 'progress mt-2';
        progressBar.innerHTML = `
            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                 role="progressbar" style="width: 0%"></div>
        `;
        form.appendChild(progressBar);
    }
    
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const percentComplete = (e.loaded / e.total) * 100;
            const progressBarInner = progressBar.querySelector('.progress-bar');
            progressBarInner.style.width = percentComplete + '%';
            progressBarInner.textContent = Math.round(percentComplete) + '%';
        }
    });
    
    xhr.addEventListener('load', function() {
        // Handle response
        const progressBarInner = progressBar.querySelector('.progress-bar');
        progressBarInner.classList.remove('progress-bar-animated');
        progressBarInner.classList.remove('progress-bar-striped');
        
        if (xhr.status === 200) {
            progressBarInner.classList.add('bg-success');
            setTimeout(() => progressBar.remove(), 2000);
        } else {
            progressBarInner.classList.add('bg-danger');
        }
    });
    
    return xhr;
}

// Auto-save form data
function autoSaveForm(formId, interval = 30000) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    let timeout;
    form.addEventListener('input', function() {
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            saveFormData(formId);
        }, interval);
    });
    
    // Load saved data on page load
    loadSavedData(formId);
}

function saveFormData(formId) {
    const form = document.getElementById(formId);
    const formData = new FormData(form);
    const data = {};
    
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    localStorage.setItem(`uytsa_${formId}_draft`, JSON.stringify(data));
    
    // Show save indicator
    const indicator = document.createElement('div');
    indicator.className = 'alert alert-info alert-dismissible fade show position-fixed bottom-0 end-0 m-3';
    indicator.style.zIndex = '1060';
    indicator.innerHTML = `
        <i class="fas fa-save"></i> Draft saved
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(indicator);
    
    setTimeout(() => indicator.remove(), 3000);
}

function loadSavedData(formId) {
    const savedData = localStorage.getItem(`uytsa_${formId}_draft`);
    if (savedData) {
        const data = JSON.parse(savedData);
        const form = document.getElementById(formId);
        
        for (const key in data) {
            const input = form.querySelector(`[name="${key}"]`);
            if (input) {
                input.value = data[key];
            }
        }
        
        // Show loaded indicator
        const indicator = document.createElement('div');
        indicator.className = 'alert alert-info alert-dismissible fade show';
        indicator.innerHTML = `
            <i class="fas fa-history"></i> Draft loaded from last session
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        form.prepend(indicator);
        
        setTimeout(() => indicator.remove(), 5000);
    }
}

// Export data to CSV
function exportToCSV(data, filename) {
    let csv = '';
    
    // Add headers
    const headers = Object.keys(data[0]);
    csv += headers.join(',') + '\n';
    
    // Add data
    data.forEach(row => {
        const values = headers.map(header => {
            let value = row[header];
            // Escape commas and quotes
            if (typeof value === 'string') {
                value = value.replace(/"/g, '""');
                if (value.includes(',') || value.includes('"') || value.includes('\n')) {
                    value = `"${value}"`;
                }
            }
            return value;
        });
        csv += values.join(',') + '\n';
    });
    
    // Create download link
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

// Print page content
function printPage(elementId) {
    const printContent = document.getElementById(elementId);
    const originalContent = document.body.innerHTML;
    
    document.body.innerHTML = printContent.innerHTML;
    window.print();
    document.body.innerHTML = originalContent;
    window.location.reload();
}

// Copy to clipboard
function copyToClipboard(text, element) {
    navigator.clipboard.writeText(text).then(() => {
        // Show success feedback
        const originalText = element.innerHTML;
        element.innerHTML = '<i class="fas fa-check"></i> Copied!';
        element.classList.add('text-success');
        
        setTimeout(() => {
            element.innerHTML = originalText;
            element.classList.remove('text-success');
        }, 2000);
    });
}

// Image lazy loading
function lazyLoadImages() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

// Initialize when page loads
window.addEventListener('load', function() {
    lazyLoadImages();
    requestNotificationPermission();
});