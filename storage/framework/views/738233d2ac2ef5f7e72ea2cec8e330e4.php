<!-- Notification System for Lab Technician -->
<div id="labtechNotificationOverlay" class="labtech-notification-overlay" style="display: none;">
    <div class="labtech-notification-modal">
        <div class="labtech-notification-header">
            <span class="labtech-notification-icon">
                <i id="labtechNotificationIcon" class="fas fa-info-circle"></i>
            </span>
            <h3 id="labtechNotificationTitle">Notification</h3>
            <button class="labtech-notification-close" onclick="closeLabtechNotification()">&times;</button>
        </div>
        <div class="labtech-notification-body">
            <p id="labtechNotificationMessage">Message content here</p>
        </div>
        <div class="labtech-notification-footer">
            <button id="labtechNotificationConfirmBtn" class="labtech-btn-confirm" onclick="confirmLabtechNotification()">OK</button>
            <button id="labtechNotificationCancelBtn" class="labtech-btn-cancel" onclick="closeLabtechNotification()" style="display: none;">Cancel</button>
        </div>
    </div>
</div>

<!-- Lab Technician Notification Styles -->
<style>
.labtech-notification-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
    animation: labtechFadeIn 0.3s ease-in-out;
}

.labtech-notification-modal {
    background: white;
    border-radius: 12px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25);
    max-width: 480px;
    width: 90%;
    max-height: 80vh;
    overflow: hidden;
    animation: labtechSlideIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.labtech-notification-header {
    background: linear-gradient(135deg, #367F2B, #4CAF50);
    color: white;
    padding: 25px;
    display: flex;
    align-items: center;
    gap: 15px;
    border-radius: 12px 12px 0 0;
}

.labtech-notification-icon {
    font-size: 28px;
    opacity: 0.9;
}

.labtech-notification-header h3 {
    margin: 0;
    flex: 1;
    font-size: 20px;
    font-weight: 600;
}

.labtech-notification-close {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: white;
    padding: 8px;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: background-color 0.2s;
}

.labtech-notification-close:hover {
    background: rgba(255, 255, 255, 0.3);
}

.labtech-notification-body {
    padding: 30px 25px;
    background: #f8f9fa;
}

.labtech-notification-body p {
    margin: 0;
    line-height: 1.6;
    color: #495057;
    font-size: 16px;
}

.labtech-notification-footer {
    padding: 20px 25px;
    background: white;
    display: flex;
    gap: 12px;
    justify-content: center;
    border-radius: 0 0 12px 12px;
}

.labtech-btn-confirm, .labtech-btn-cancel {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.2s;
    min-width: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.labtech-btn-confirm {
    background: #367F2B;
    color: white;
}

.labtech-btn-confirm:hover {
    background: #2E6E24;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(54, 127, 43, 0.3);
}

.labtech-btn-cancel {
    background: #6c757d;
    color: white;
}

.labtech-btn-cancel:hover {
    background: #545b62;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
}

/* Lab Technician notification type styles */
.labtech-notification-success .labtech-notification-header {
    background: linear-gradient(135deg, #28a745, #34ce57);
}

.labtech-notification-success .labtech-notification-icon {
    color: white;
}

.labtech-notification-error .labtech-notification-header {
    background: linear-gradient(135deg, #dc3545, #e55353);
}

.labtech-notification-error .labtech-notification-icon {
    color: white;
}

.labtech-notification-warning .labtech-notification-header {
    background: linear-gradient(135deg, #ffc107, #ffcd39);
}

.labtech-notification-warning .labtech-notification-icon {
    color: white;
}

.labtech-notification-info .labtech-notification-header {
    background: linear-gradient(135deg, #17a2b8, #20c9e7);
}

.labtech-notification-info .labtech-notification-icon {
    color: white;
}

.labtech-notification-confirm .labtech-notification-header {
    background: linear-gradient(135deg, #fd7e14, #ff922b);
}

.labtech-notification-confirm .labtech-notification-icon {
    color: white;
}

/* Animations */
@keyframes labtechFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes labtechSlideIn {
    from { 
        transform: translateY(-50px) scale(0.9);
        opacity: 0;
    }
    to { 
        transform: translateY(0) scale(1);
        opacity: 1;
    }
}

/* Loading state */
.labtech-notification-loading .labtech-notification-body {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    padding: 40px 25px;
}

.labtech-notification-loading .labtech-notification-footer {
    display: none;
}

.labtech-notification-spinner {
    border: 3px solid #f3f3f3;
    border-top: 3px solid #367F2B;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: labtechSpin 1s linear infinite;
    margin-bottom: 15px;
}

@keyframes labtechSpin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<!-- Lab Technician Notification JavaScript -->
<script>
// Global labtech notification system
window.LabtechNotificationSystem = {
    currentCallback: null,
    
    show: function(message, type = 'info', title = null, callback = null) {
        const overlay = document.getElementById('labtechNotificationOverlay');
        const modal = overlay.querySelector('.labtech-notification-modal');
        const titleEl = document.getElementById('labtechNotificationTitle');
        const messageEl = document.getElementById('labtechNotificationMessage');
        const iconEl = document.getElementById('labtechNotificationIcon');
        const confirmBtn = document.getElementById('labtechNotificationConfirmBtn');
        const cancelBtn = document.getElementById('labtechNotificationCancelBtn');
        
        // Reset modal classes
        modal.className = 'labtech-notification-modal labtech-notification-' + type;
        
        // Set content
        titleEl.textContent = title || this.getDefaultTitle(type);
        messageEl.textContent = message;
        
        // Set icon
        iconEl.className = 'fas ' + this.getIcon(type);
        
        // Store callback
        this.currentCallback = callback;
        
        // Show/hide buttons based on type
        if (type === 'confirm') {
            confirmBtn.innerHTML = '<i class="fas fa-check"></i> Yes';
            cancelBtn.innerHTML = '<i class="fas fa-times"></i> Cancel';
            cancelBtn.style.display = 'inline-flex';
        } else {
            confirmBtn.innerHTML = '<i class="fas fa-check"></i> OK';
            cancelBtn.style.display = 'none';
        }
        
        // Show overlay
        overlay.style.display = 'flex';
        
        // Focus confirm button
        setTimeout(() => confirmBtn.focus(), 100);
    },
    
    showLoading: function(message = 'Processing...', title = 'Please Wait') {
        const overlay = document.getElementById('labtechNotificationOverlay');
        const modal = overlay.querySelector('.labtech-notification-modal');
        const titleEl = document.getElementById('labtechNotificationTitle');
        const body = modal.querySelector('.labtech-notification-body');
        
        // Set loading state
        modal.className = 'labtech-notification-modal labtech-notification-loading';
        titleEl.textContent = title;
        
        // Create loading content
        body.innerHTML = `
            <div class="labtech-notification-spinner"></div>
            <p>${message}</p>
        `;
        
        // Show overlay
        overlay.style.display = 'flex';
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

// Helper functions for different labtech notification types
function showLabtechSuccess(message, title = null, callback = null) {
    LabtechNotificationSystem.show(message, 'success', title, callback);
}

function showLabtechError(message, title = null, callback = null) {
    LabtechNotificationSystem.show(message, 'error', title, callback);
}

function showLabtechWarning(message, title = null, callback = null) {
    LabtechNotificationSystem.show(message, 'warning', title, callback);
}

function showLabtechInfo(message, title = null, callback = null) {
    LabtechNotificationSystem.show(message, 'info', title, callback);
}

function showLabtechConfirm(message, title = null, callback = null) {
    LabtechNotificationSystem.show(message, 'confirm', title, callback);
}

function showLabtechLoading(message = 'Processing your request...', title = 'Please Wait') {
    LabtechNotificationSystem.showLoading(message, title);
}

// Helper function to handle AJAX response errors consistently
function handleLabtechAjaxError(response, defaultMessage = 'An error occurred') {
    if (response && response.message) {
        showLabtechError(response.message, 'Error');
    } else if (response && response.errors) {
        // Handle Laravel validation errors
        const errorMessages = [];
        for (const field in response.errors) {
            if (Array.isArray(response.errors[field])) {
                errorMessages.push(...response.errors[field]);
            }
        }
        showLabtechError(errorMessages.join(' '), 'Validation Error');
    } else {
        showLabtechError(defaultMessage, 'Error');
    }
}

// Helper function for standardized fetch error handling
async function handleLabtechFetchResponse(response, successCallback, errorTitle = 'Request Failed') {
    try {
        const data = await response.json();
        
        if (!response.ok) {
            handleLabtechAjaxError(data, `Server returned ${response.status} error`);
            return false;
        }
        
        if (data.ok === false || data.success === false) {
            handleLabtechAjaxError(data, 'Operation failed');
            return false;
        }
        
        if (successCallback && typeof successCallback === 'function') {
            successCallback(data);
        }
        
        return true;
    } catch (error) {
        console.error('Response parsing error:', error);
        showLabtechError('Invalid server response', errorTitle);
        return false;
    }
}

function confirmLabtechNotification() {
    const callback = LabtechNotificationSystem.currentCallback;
    closeLabtechNotification();
    if (callback && typeof callback === 'function') {
        callback(true);
    }
}

function closeLabtechNotification() {
    const overlay = document.getElementById('labtechNotificationOverlay');
    overlay.style.display = 'none';
    LabtechNotificationSystem.currentCallback = null;
}

// Close on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const overlay = document.getElementById('labtechNotificationOverlay');
        if (overlay && overlay.style.display === 'flex') {
            closeLabtechNotification();
        }
    }
});

// Set up overlay click handler when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('labtechNotificationOverlay');
    if (overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                closeLabtechNotification();
            }
        });
    }
});
</script><?php /**PATH D:\xamppLatest\htdocs\HIMSDrRomelCruz\resources\views/labtech/modals/notification_system.blade.php ENDPATH**/ ?>