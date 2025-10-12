<!-- Admin Notification System Modal -->
<div id="adminNotificationModal" class="notification-modal" style="display: none;">
    <div class="notification-overlay"></div>
    <div class="notification-content">
        <div class="notification-header">
            <div class="notification-icon">
                <i id="notificationIcon" class="fas fa-info-circle"></i>
            </div>
            <h3 id="notificationTitle">Notification</h3>
        </div>
        <div class="notification-body">
            <p id="notificationMessage">Loading...</p>
            <div id="notificationLoading" class="loading-spinner" style="display: none;">
                <div class="spinner"></div>
                <span>Processing...</span>
            </div>
        </div>
        <div class="notification-footer">
            <button id="notificationConfirm" class="btn btn-primary" style="display: none;">
                <i class="fas fa-check"></i> Confirm
            </button>
            <button id="notificationCancel" class="btn btn-secondary" style="display: none;">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button id="notificationOk" class="btn btn-primary">
                <i class="fas fa-check"></i> OK
            </button>
        </div>
    </div>
</div>

<style>
/* Admin Notification System Styles */
.notification-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 2000;
    display: flex;
    justify-content: center;
    align-items: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.notification-modal.show {
    opacity: 1;
}

.notification-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(3px);
}

.notification-content {
    position: relative;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    min-width: 400px;
    max-width: 500px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    overflow: hidden;
    transform: translateY(-20px);
    transition: transform 0.3s ease;
}

.notification-modal.show .notification-content {
    transform: translateY(0);
}

.notification-header {
    background: rgba(255, 255, 255, 0.1);
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.notification-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.notification-header h3 {
    color: white;
    margin: 0;
    font-size: 20px;
    font-weight: 600;
}

.notification-body {
    padding: 25px;
    background: white;
    position: relative;
}

.notification-body p {
    margin: 0;
    color: #333;
    font-size: 16px;
    line-height: 1.5;
}

.loading-spinner {
    display: flex;
    align-items: center;
    gap: 15px;
    justify-content: center;
    margin-top: 15px;
}

.spinner {
    width: 30px;
    height: 30px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading-spinner span {
    color: #666;
    font-size: 14px;
}

.notification-footer {
    padding: 20px 25px;
    background: #f8f9fa;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    border-top: 1px solid #e9ecef;
}

.notification-footer .btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.notification-footer .btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.notification-footer .btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.notification-footer .btn-secondary {
    background: #6c757d;
    color: white;
}

.notification-footer .btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-1px);
}

/* Notification Type Styles */
.notification-success .notification-content {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}

.notification-success .notification-footer .btn-primary {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}

.notification-error .notification-content {
    background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
}

.notification-error .notification-footer .btn-primary {
    background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
}

.notification-warning .notification-content {
    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
}

.notification-warning .notification-footer .btn-primary {
    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
}

.notification-confirm .notification-content {
    background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
}

.notification-confirm .notification-footer .btn-primary {
    background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
}

/* Responsive Design */
@media (max-width: 768px) {
    .notification-content {
        min-width: 90%;
        max-width: 90%;
        margin: 20px;
    }
    
    .notification-header {
        padding: 15px;
    }
    
    .notification-body {
        padding: 20px;
    }
    
    .notification-footer {
        padding: 15px 20px;
        flex-direction: column;
    }
    
    .notification-footer .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
// Admin Notification System JavaScript
class AdminNotificationSystem {
    constructor() {
        this.modal = document.getElementById('adminNotificationModal');
        this.icon = document.getElementById('notificationIcon');
        this.title = document.getElementById('notificationTitle');
        this.message = document.getElementById('notificationMessage');
        this.loading = document.getElementById('notificationLoading');
        this.confirmBtn = document.getElementById('notificationConfirm');
        this.cancelBtn = document.getElementById('notificationCancel');
        this.okBtn = document.getElementById('notificationOk');
        
        this.setupEventListeners();
    }
    
    setupEventListeners() {
        // Close modal when clicking overlay
        this.modal?.querySelector('.notification-overlay')?.addEventListener('click', () => {
            this.hide();
        });
        
        // OK button
        this.okBtn?.addEventListener('click', () => {
            this.hide();
        });
        
        // Cancel button  
        this.cancelBtn?.addEventListener('click', () => {
            this.hide();
            if (this.onCancel) this.onCancel();
        });
        
        // Confirm button
        this.confirmBtn?.addEventListener('click', () => {
            this.hide();
            if (this.onConfirm) this.onConfirm();
        });
        
        // ESC key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal?.style.display !== 'none') {
                this.hide();
            }
        });
    }
    
    show(options = {}) {
        const {
            type = 'info',
            title = 'Notification',
            message = '',
            showConfirm = false,
            showCancel = false,
            showLoading = false,
            onConfirm = null,
            onCancel = null
        } = options;
        
        // Reset modal classes
        this.modal.className = 'notification-modal';
        this.modal.classList.add(`notification-${type}`);
        
        // Set content
        this.title.textContent = title;
        this.message.textContent = message;
        
        // Set icon based on type
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-triangle',
            warning: 'fas fa-exclamation-circle',
            info: 'fas fa-info-circle',
            confirm: 'fas fa-question-circle'
        };
        this.icon.className = icons[type] || icons.info;
        
        // Show/hide buttons
        this.confirmBtn.style.display = showConfirm ? 'flex' : 'none';
        this.cancelBtn.style.display = showCancel ? 'flex' : 'none';
        this.okBtn.style.display = (!showConfirm && !showCancel) ? 'flex' : 'none';
        
        // Show/hide loading
        this.loading.style.display = showLoading ? 'flex' : 'none';
        this.message.style.display = showLoading ? 'none' : 'block';
        
        // Set callbacks
        this.onConfirm = onConfirm;
        this.onCancel = onCancel;
        
        // Show modal
        this.modal.style.display = 'flex';
        setTimeout(() => this.modal.classList.add('show'), 10);
        
        // Auto-hide for success messages
        if (type === 'success' && !showConfirm && !showCancel) {
            setTimeout(() => this.hide(), 3000);
        }
    }
    
    hide() {
        this.modal?.classList.remove('show');
        setTimeout(() => {
            if (this.modal) this.modal.style.display = 'none';
        }, 300);
    }
    
    // Convenience methods
    success(message, title = 'Success') {
        this.show({
            type: 'success',
            title,
            message
        });
    }
    
    error(message, title = 'Error') {
        this.show({
            type: 'error',
            title,
            message
        });
    }
    
    warning(message, title = 'Warning') {
        this.show({
            type: 'warning',
            title,
            message
        });
    }
    
    info(message, title = 'Information') {
        this.show({
            type: 'info',
            title,
            message
        });
    }
    
    confirm(message, title = 'Confirm Action', onConfirm = null, onCancel = null) {
        this.show({
            type: 'confirm',
            title,
            message,
            showConfirm: true,
            showCancel: true,
            onConfirm,
            onCancel
        });
    }
    
    loading(message = 'Processing...', title = 'Please Wait') {
        this.show({
            type: 'info',
            title,
            message,
            showLoading: true
        });
    }
}

// Initialize the notification system
let adminNotification;
document.addEventListener('DOMContentLoaded', function() {
    adminNotification = new AdminNotificationSystem();
});

// Replacement functions for alert() and confirm()
function adminAlert(message, title = 'Alert') {
    if (adminNotification) {
        adminNotification.info(message, title);
    } else {
        alert(message);
    }
}

function adminSuccess(message, title = 'Success') {
    if (adminNotification) {
        adminNotification.success(message, title);
    } else {
        alert(message);
    }
}

function adminError(message, title = 'Error') {
    if (adminNotification) {
        adminNotification.error(message, title);
    } else {
        alert(message);
    }
}

function adminWarning(message, title = 'Warning') {
    if (adminNotification) {
        adminNotification.warning(message, title);
    } else {
        alert(message);
    }
}

function adminConfirm(message, title = 'Confirm', onConfirm = null, onCancel = null) {
    if (adminNotification) {
        adminNotification.confirm(message, title, onConfirm, onCancel);
    } else {
        if (confirm(message)) {
            if (onConfirm) onConfirm();
        } else {
            if (onCancel) onCancel();
        }
    }
}

function adminLoading(message = 'Processing...', title = 'Please Wait') {
    if (adminNotification) {
        adminNotification.loading(message, title);
    }
}

function hideAdminNotification() {
    if (adminNotification) {
        adminNotification.hide();
    }
}
</script><?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views/admin/modals/notification_system.blade.php ENDPATH**/ ?>