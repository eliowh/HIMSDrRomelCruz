<!-- Notification System for Pharmacy -->
<div id="pharmacyNotificationOverlay" class="pharmacy-notification-overlay" style="display: none;">
    <div class="pharmacy-notification-modal">
        <div class="pharmacy-notification-header">
            <span class="pharmacy-notification-icon">
                <i id="pharmacyNotificationIcon" class="fas fa-info-circle"></i>
            </span>
            <h3 id="pharmacyNotificationTitle">Notification</h3>
            <button class="pharmacy-notification-close" onclick="closePharmacyNotification()">&times;</button>
        </div>
        <div class="pharmacy-notification-body">
            <p id="pharmacyNotificationMessage">Message content here</p>
        </div>
        <div class="pharmacy-notification-footer">
            <button id="pharmacyNotificationConfirmBtn" class="pharmacy-btn-confirm" onclick="confirmPharmacyNotification()">OK</button>
            <button id="pharmacyNotificationCancelBtn" class="pharmacy-btn-cancel" onclick="closePharmacyNotification()" style="display: none;">Cancel</button>
        </div>
    </div>
</div>

<!-- Pharmacy Notification Styles -->
<style>
.pharmacy-notification-overlay {
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
    animation: pharmacyFadeIn 0.3s ease-in-out;
}

.pharmacy-notification-modal {
    background: white;
    border-radius: 12px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25);
    max-width: 480px;
    width: 90%;
    max-height: 80vh;
    overflow: hidden;
    animation: pharmacySlideIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.pharmacy-notification-header {
    background: linear-gradient(135deg, #2E7D32, #4CAF50);
    color: white;
    padding: 25px;
    display: flex;
    align-items: center;
    gap: 15px;
    border-radius: 12px 12px 0 0;
}

.pharmacy-notification-icon {
    font-size: 28px;
    opacity: 0.9;
}

.pharmacy-notification-header h3 {
    margin: 0;
    flex: 1;
    font-size: 20px;
    font-weight: 600;
}

.pharmacy-notification-close {
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

.pharmacy-notification-close:hover {
    background: rgba(255, 255, 255, 0.3);
}

.pharmacy-notification-body {
    padding: 30px 25px;
    background: #f8f9fa;
}

.pharmacy-notification-body p {
    margin: 0;
    line-height: 1.6;
    color: #495057;
    font-size: 16px;
}

.pharmacy-notification-footer {
    padding: 20px 25px;
    background: white;
    display: flex;
    gap: 12px;
    justify-content: center;
    border-radius: 0 0 12px 12px;
}

.pharmacy-btn-confirm, .pharmacy-btn-cancel {
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

.pharmacy-btn-confirm {
    background: #2E7D32;
    color: white;
}

.pharmacy-btn-confirm:hover {
    background: #1B5E20;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(46, 125, 50, 0.3);
}

.pharmacy-btn-cancel {
    background: #6c757d;
    color: white;
}

.pharmacy-btn-cancel:hover {
    background: #545b62;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
}

/* Pharmacy notification type styles */
.pharmacy-notification-success .pharmacy-notification-header {
    background: linear-gradient(135deg, #28a745, #34ce57);
}

.pharmacy-notification-success .pharmacy-notification-icon {
    color: white;
}

.pharmacy-notification-error .pharmacy-notification-header {
    background: linear-gradient(135deg, #dc3545, #e55353);
}

.pharmacy-notification-error .pharmacy-notification-icon {
    color: white;
}

.pharmacy-notification-warning .pharmacy-notification-header {
    background: linear-gradient(135deg, #ffc107, #ffcd39);
}

.pharmacy-notification-warning .pharmacy-notification-icon {
    color: white;
}

.pharmacy-notification-info .pharmacy-notification-header {
    background: linear-gradient(135deg, #17a2b8, #20c9e7);
}

.pharmacy-notification-info .pharmacy-notification-icon {
    color: white;
}

.pharmacy-notification-confirm .pharmacy-notification-header {
    background: linear-gradient(135deg, #fd7e14, #ff922b);
}

.pharmacy-notification-confirm .pharmacy-notification-icon {
    color: white;
}

/* Animations */
@keyframes pharmacyFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes pharmacySlideIn {
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
.pharmacy-notification-loading .pharmacy-notification-body {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    padding: 40px 25px;
}

.pharmacy-notification-loading .pharmacy-notification-footer {
    display: none;
}

.pharmacy-notification-spinner {
    border: 3px solid #f3f3f3;
    border-top: 3px solid #2E7D32;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: pharmacySpin 1s linear infinite;
    margin-bottom: 15px;
}

@keyframes pharmacySpin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<!-- Pharmacy Notification JavaScript -->
<script>
// Global pharmacy notification system
window.PharmacyNotificationSystem = {
    currentCallback: null,
    
    show: function(message, type = 'info', title = null, callback = null) {
        const overlay = document.getElementById('pharmacyNotificationOverlay');
        const modal = overlay.querySelector('.pharmacy-notification-modal');
        const titleEl = document.getElementById('pharmacyNotificationTitle');
        const messageEl = document.getElementById('pharmacyNotificationMessage');
        const iconEl = document.getElementById('pharmacyNotificationIcon');
        const confirmBtn = document.getElementById('pharmacyNotificationConfirmBtn');
        const cancelBtn = document.getElementById('pharmacyNotificationCancelBtn');
        
        // Reset modal classes
        modal.className = 'pharmacy-notification-modal pharmacy-notification-' + type;
        
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
        const overlay = document.getElementById('pharmacyNotificationOverlay');
        const modal = overlay.querySelector('.pharmacy-notification-modal');
        const titleEl = document.getElementById('pharmacyNotificationTitle');
        const body = modal.querySelector('.pharmacy-notification-body');
        
        // Set loading state
        modal.className = 'pharmacy-notification-modal pharmacy-notification-loading';
        titleEl.textContent = title;
        
        // Create loading content
        body.innerHTML = `
            <div class="pharmacy-notification-spinner"></div>
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

// Helper functions for different pharmacy notification types
function showPharmacySuccess(message, title = null, callback = null) {
    PharmacyNotificationSystem.show(message, 'success', title, callback);
}

function showPharmacyError(message, title = null, callback = null) {
    PharmacyNotificationSystem.show(message, 'error', title, callback);
}

function showPharmacyWarning(message, title = null, callback = null) {
    PharmacyNotificationSystem.show(message, 'warning', title, callback);
}

function showPharmacyInfo(message, title = null, callback = null) {
    PharmacyNotificationSystem.show(message, 'info', title, callback);
}

function showPharmacyConfirm(message, title = null, callback = null) {
    PharmacyNotificationSystem.show(message, 'confirm', title, callback);
}

function showPharmacyLoading(message = 'Processing your request...', title = 'Please Wait') {
    PharmacyNotificationSystem.showLoading(message, title);
}

// Helper function to handle AJAX response errors consistently
function handlePharmacyAjaxError(response, defaultMessage = 'An error occurred') {
    if (response && response.message) {
        showPharmacyError(response.message, 'Error');
    } else if (response && response.errors) {
        // Handle Laravel validation errors
        const errorMessages = [];
        for (const field in response.errors) {
            if (Array.isArray(response.errors[field])) {
                errorMessages.push(...response.errors[field]);
            }
        }
        showPharmacyError(errorMessages.join(' '), 'Validation Error');
    } else {
        showPharmacyError(defaultMessage, 'Error');
    }
}

// Helper function for standardized fetch error handling
async function handlePharmacyFetchResponse(response, successCallback, errorTitle = 'Request Failed') {
    try {
        const data = await response.json();
        
        if (!response.ok) {
            handlePharmacyAjaxError(data, `Server returned ${response.status} error`);
            return false;
        }
        
        if (data.ok === false || data.success === false) {
            handlePharmacyAjaxError(data, 'Operation failed');
            return false;
        }
        
        if (successCallback && typeof successCallback === 'function') {
            successCallback(data);
        }
        
        return true;
    } catch (error) {
        console.error('Response parsing error:', error);
        showPharmacyError('Invalid server response', errorTitle);
        return false;
    }
}

function confirmPharmacyNotification() {
    const callback = PharmacyNotificationSystem.currentCallback;
    closePharmacyNotification();
    if (callback && typeof callback === 'function') {
        callback(true);
    }
}

function closePharmacyNotification() {
    const overlay = document.getElementById('pharmacyNotificationOverlay');
    overlay.style.display = 'none';
    PharmacyNotificationSystem.currentCallback = null;
}

// Close on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const overlay = document.getElementById('pharmacyNotificationOverlay');
        if (overlay && overlay.style.display === 'flex') {
            closePharmacyNotification();
        }
    }
});

// Set up overlay click handler when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('pharmacyNotificationOverlay');
    if (overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                closePharmacyNotification();
            }
        });
    }
});
</script>