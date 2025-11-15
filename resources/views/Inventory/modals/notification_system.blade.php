<!-- Notification System for Inventory -->
<div id="notificationOverlay" class="notification-overlay" style="display: none;">
    <div class="notification-modal">
        <div class="notification-header">
            <span class="notification-icon">
                <i id="notificationIcon" class="fas fa-info-circle"></i>
            </span>
            <h3 id="notificationTitle">Notification</h3>
            <button class="notification-close" onclick="closeNotification()">&times;</button>
        </div>
        <div class="notification-body">
            <p id="notificationMessage">Message content here</p>
        </div>
        <div class="notification-footer">
            <button id="notificationConfirmBtn" class="btn-confirm" onclick="confirmNotification()">OK</button>
            <button id="notificationCancelBtn" class="btn-cancel" onclick="closeNotification()" style="display: none;">Cancel</button>
        </div>
    </div>
</div>

<!-- Notification Styles -->
<style>
.notification-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 99999;
    display: flex;
    justify-content: center;
    align-items: center;
    animation: fadeIn 0.3s ease-in-out;
}

.notification-modal {
    background: white;
    border-radius: 8px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    max-width: 450px;
    width: 90%;
    max-height: 80vh;
    overflow: hidden;
    animation: slideIn 0.3s ease-out;
}

.notification-header {
    background: #f8f9fa;
    padding: 20px;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    align-items: center;
    gap: 12px;
}

.notification-icon {
    font-size: 24px;
}

.notification-header h3 {
    margin: 0;
    flex: 1;
    font-size: 18px;
    font-weight: 600;
}

.notification-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #6c757d;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: background-color 0.2s;
}

.notification-close:hover {
    background-color: #e9ecef;
}

.notification-body {
    padding: 20px;
}

.notification-body p {
    margin: 0;
    line-height: 1.5;
    color: #495057;
}

.notification-footer {
    padding: 15px 20px;
    border-top: 1px solid #dee2e6;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.btn-confirm, .btn-cancel {
    padding: 8px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s;
}

.btn-confirm {
    background: #007bff;
    color: white;
}

.btn-confirm:hover {
    background: #0056b3;
}

.btn-cancel {
    background: #6c757d;
    color: white;
}

.btn-cancel:hover {
    background: #545b62;
}

/* Notification type styles */
.notification-success .notification-icon {
    color: #28a745;
}

.notification-error .notification-icon {
    color: #dc3545;
}

.notification-warning .notification-icon {
    color: #ffc107;
}

.notification-info .notification-icon {
    color: #17a2b8;
}

.notification-confirm .notification-icon {
    color: #fd7e14;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideIn {
    from { 
        transform: translateY(-50px);
        opacity: 0;
    }
    to { 
        transform: translateY(0);
        opacity: 1;
    }
}
</style>

<!-- Notification JavaScript -->
<script>
// Global notification system
window.NotificationSystem = {
    currentCallback: null,
    
    show: function(message, type = 'info', title = null, callback = null) {
        const overlay = document.getElementById('notificationOverlay');
        const modal = overlay.querySelector('.notification-modal');
        const titleEl = document.getElementById('notificationTitle');
        const messageEl = document.getElementById('notificationMessage');
        const iconEl = document.getElementById('notificationIcon');
        const confirmBtn = document.getElementById('notificationConfirmBtn');
        const cancelBtn = document.getElementById('notificationCancelBtn');
        
        // Reset modal classes
        modal.className = 'notification-modal notification-' + type;
        
        // Set content
        titleEl.textContent = title || this.getDefaultTitle(type);
        messageEl.textContent = message;
        
        // Set icon
        iconEl.className = 'fas ' + this.getIcon(type);
        
        // Store callback
        this.currentCallback = callback;
        
        // Show/hide buttons based on type
        if (type === 'confirm') {
            confirmBtn.textContent = 'Yes';
            cancelBtn.style.display = 'inline-block';
        } else {
            confirmBtn.textContent = 'OK';
            cancelBtn.style.display = 'none';
        }
        
        // Show overlay
        overlay.style.display = 'flex';
        
        // Focus confirm button
        setTimeout(() => confirmBtn.focus(), 100);
    },
    
    getDefaultTitle: function(type) {
        const titles = {
            success: 'Success',
            error: 'Error',
            warning: 'Warning',
            info: 'Information',
            confirm: 'Confirmation Required'
        };
        return titles[type] || 'Notification';
    },
    
    getIcon: function(type) {
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle',
            confirm: 'fa-question-circle'
        };
        return icons[type] || 'fa-info-circle';
    }
};

// Helper functions for different notification types
function showSuccess(message, title = null, callback = null) {
    NotificationSystem.show(message, 'success', title, callback);
}

function showError(message, title = null, callback = null) {
    NotificationSystem.show(message, 'error', title, callback);
}

function showWarning(message, title = null, callback = null) {
    NotificationSystem.show(message, 'warning', title, callback);
}

function showInfo(message, title = null, callback = null) {
    NotificationSystem.show(message, 'info', title, callback);
}

function showConfirm(message, title = null, callback = null) {
    NotificationSystem.show(message, 'confirm', title, callback);
}

// Helper function to handle AJAX response errors consistently
function handleAjaxError(response, defaultMessage = 'An error occurred') {
    if (response && response.message) {
        showError(response.message, 'Error');
    } else if (response && response.errors) {
        // Handle Laravel validation errors
        const errorMessages = [];
        for (const field in response.errors) {
            if (Array.isArray(response.errors[field])) {
                errorMessages.push(...response.errors[field]);
            }
        }
        showError(errorMessages.join(' '), 'Validation Error');
    } else {
        showError(defaultMessage, 'Error');
    }
}

// Helper function for standardized fetch error handling
async function handleFetchResponse(response, successCallback, errorTitle = 'Request Failed') {
    try {
        const data = await response.json();
        
        if (!response.ok) {
            handleAjaxError(data, `Server returned ${response.status} error`);
            return false;
        }
        
        if (data.ok === false || data.success === false) {
            handleAjaxError(data, 'Operation failed');
            return false;
        }
        
        if (successCallback && typeof successCallback === 'function') {
            successCallback(data);
        }
        
        return true;
    } catch (error) {
        console.error('Response parsing error:', error);
        showError('Invalid server response', errorTitle);
        return false;
    }
}

function confirmNotification() {
    const callback = NotificationSystem.currentCallback;
    closeNotification();
    if (callback && typeof callback === 'function') {
        callback(true);
    }
}

function closeNotification() {
    const overlay = document.getElementById('notificationOverlay');
    overlay.style.display = 'none';
    NotificationSystem.currentCallback = null;
}

// Close on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const overlay = document.getElementById('notificationOverlay');
        if (overlay.style.display === 'flex') {
            closeNotification();
        }
    }
});

// Close on overlay click
document.getElementById('notificationOverlay').addEventListener('click', function(e) {
    if (e.target === this) {
        closeNotification();
    }
});

// Global error handler for unhandled promise rejections
window.addEventListener('unhandledrejection', function(event) {
    console.error('Unhandled promise rejection:', event.reason);
    showError('An unexpected error occurred. Please try again.', 'System Error');
    event.preventDefault();
});

// Global error handler for JavaScript errors
window.addEventListener('error', function(event) {
    console.error('JavaScript error:', event.error);
    // Only show notification for network-related errors or API errors
    if (event.error && (event.error.message.includes('fetch') || event.error.message.includes('network'))) {
        showError('Network error occurred. Please check your connection and try again.', 'Connection Error');
    }
});
</script>