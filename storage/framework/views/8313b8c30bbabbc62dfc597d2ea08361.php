<!-- Billing Notification System Modal -->
<div id="billingNotificationModal" class="notification-modal" style="display: none;">
    <div class="notification-overlay"></div>
    <div class="notification-content">
        <div class="notification-header">
            <div class="notification-icon">
                <i id="billingNotificationIcon" class="fas fa-info-circle"></i>
            </div>
            <h3 id="billingNotificationTitle">Billing Notification</h3>
        </div>
        <div class="notification-body">
            <p id="billingNotificationMessage">Loading...</p>
            <div id="billingNotificationLoading" class="loading-spinner" style="display: none;">
                <div class="spinner"></div>
                <span>Processing...</span>
            </div>
        </div>
        <div class="notification-footer">
            <button id="billingNotificationCancel" class="btn btn-secondary" style="display: none;">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button id="billingNotificationConfirm" class="btn btn-primary" style="display: none;">
                <i class="fas fa-check"></i> Confirm
            </button>
            <button id="billingNotificationOk" class="btn btn-primary" style="display: none;">
                <i class="fas fa-check"></i> OK
            </button>
        </div>
    </div>
</div>

<style>
/* Billing Notification System Styles */
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
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(3px);
}

.notification-content {
    position: relative;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    max-width: 450px;
    width: 90%;
    margin: 20px;
    overflow: hidden;
    transform: scale(0.9);
    transition: transform 0.3s ease;
}

.notification-modal.show .notification-content {
    transform: scale(1);
}

.notification-header {
    padding: 20px 25px 15px;
    display: flex;
    align-items: center;
    gap: 15px;
    border-bottom: 1px solid #e9ecef;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.notification-icon {
    font-size: 24px;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
}

.notification-header h3 {
    margin: 0;
    font-weight: 600;
    font-size: 18px;
}

.notification-body {
    padding: 25px;
    line-height: 1.6;
    color: #333;
    font-size: 15px;
}

.notification-body p {
    margin: 0 0 15px 0;
}

.loading-spinner {
    display: flex;
    align-items: center;
    gap: 10px;
    justify-content: center;
    color: #667eea;
}

.spinner {
    width: 20px;
    height: 20px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.notification-footer {
    padding: 15px 25px 20px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    background: #f8f9fa;
}

.notification-footer .btn {
    padding: 10px 24px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-width: 100px;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #545b62;
    transform: translateY(-1px);
}

/* Icon color variations */
.notification-icon.success { color: #28a745; }
.notification-icon.error { color: #dc3545; }
.notification-icon.warning { color: #ffc107; }
.notification-icon.info { color: #17a2b8; }

/* Header color variations */
.notification-header.success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}

.notification-header.error {
    background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
}

.notification-header.warning {
    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
    color: #212529;
}

.notification-header.info {
    background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
}

.notification-header.payment {
    background: linear-gradient(135deg, #367F2B 0%, #28a745 100%);
}

.notification-header.delete {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
}
</style>

<script>
// Billing Notification System Functions
window.showBillingNotification = function(type, title, message, showConfirm = false) {
    return new Promise((resolve, reject) => {
        const modal = document.getElementById('billingNotificationModal');
        const header = modal.querySelector('.notification-header');
        const icon = document.getElementById('billingNotificationIcon');
        const titleEl = document.getElementById('billingNotificationTitle');
        const messageEl = document.getElementById('billingNotificationMessage');
        const confirmBtn = document.getElementById('billingNotificationConfirm');
        const cancelBtn = document.getElementById('billingNotificationCancel');
        const okBtn = document.getElementById('billingNotificationOk');
        
        // Reset classes
        header.className = 'notification-header';
        icon.className = 'fas';
        
        // Set type-specific styles and icons
        switch(type) {
            case 'success':
                header.classList.add('success');
                icon.classList.add('fa-check-circle');
                break;
            case 'error':
                header.classList.add('error');
                icon.classList.add('fa-exclamation-circle');
                break;
            case 'warning':
                header.classList.add('warning');
                icon.classList.add('fa-exclamation-triangle');
                break;
            case 'confirm':
                header.classList.add('warning');
                icon.classList.add('fa-question-circle');
                break;
            case 'payment':
                header.classList.add('success');
                icon.classList.add('fa-credit-card');
                break;
            case 'delete':
                header.classList.add('error');
                icon.classList.add('fa-trash-alt');
                break;
            default:
                header.classList.add('info');
                icon.classList.add('fa-info-circle');
        }
        
        titleEl.textContent = title;
        // Handle line breaks in messages
        messageEl.style.whiteSpace = 'pre-line';
        messageEl.textContent = message;
        
        // First hide ALL buttons with !important
        confirmBtn.style.setProperty('display', 'none', 'important');
        cancelBtn.style.setProperty('display', 'none', 'important');
        okBtn.style.setProperty('display', 'none', 'important');
        
        // Then show only the appropriate buttons
        if (showConfirm || type === 'confirm' || type === 'payment' || type === 'delete') {
            // For confirmation dialogs: show Confirm and Cancel buttons only
            confirmBtn.style.setProperty('display', 'inline-flex', 'important');
            cancelBtn.style.setProperty('display', 'inline-flex', 'important');
        } else {
            // For simple notifications: show only OK button
            okBtn.style.setProperty('display', 'inline-flex', 'important');
        }
        
        // Show modal
        modal.style.display = 'flex';
        setTimeout(() => modal.classList.add('show'), 10);
        
        // Event listeners
        const cleanup = () => {
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
                confirmBtn.onclick = null;
                cancelBtn.onclick = null;
                okBtn.onclick = null;
            }, 300);
        };
        
        confirmBtn.onclick = () => {
            cleanup();
            resolve(true);
        };
        
        cancelBtn.onclick = () => {
            cleanup();
            resolve(false);
        };
        
        okBtn.onclick = () => {
            cleanup();
            // Check if this is a success notification that should trigger a page refresh
            if (type === 'success' && (message.includes('marked as PAID') || message.includes('reverted to UNPAID'))) {
                location.reload();
            }
            resolve(true);
        };
        
        // ESC key handler
        const handleEsc = (e) => {
            if (e.key === 'Escape') {
                cleanup();
                resolve(false);
                document.removeEventListener('keydown', handleEsc);
            }
        };
        document.addEventListener('keydown', handleEsc);
    });
};

window.showBillingLoading = function(message = 'Processing billing...') {
    const modal = document.getElementById('billingNotificationModal');
    const header = modal.querySelector('.notification-header');
    const icon = document.getElementById('billingNotificationIcon');
    const titleEl = document.getElementById('billingNotificationTitle');
    const messageEl = document.getElementById('billingNotificationMessage');
    const loadingEl = document.getElementById('billingNotificationLoading');
    const confirmBtn = document.getElementById('billingNotificationConfirm');
    const cancelBtn = document.getElementById('billingNotificationCancel');
    const okBtn = document.getElementById('billingNotificationOk');
    
    // Reset to info style
    header.className = 'notification-header info';
    icon.className = 'fas fa-sync-alt fa-spin';
    
    titleEl.textContent = 'Processing';
    messageEl.textContent = message;
    loadingEl.style.display = 'flex';
    
    // Hide all buttons
    confirmBtn.style.display = 'none';
    cancelBtn.style.display = 'none';
    okBtn.style.display = 'none';
    
    // Show modal
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('show'), 10);
};

window.closeBillingNotification = function() {
    const modal = document.getElementById('billingNotificationModal');
    const loadingEl = document.getElementById('billingNotificationLoading');
    
    modal.classList.remove('show');
    setTimeout(() => {
        modal.style.display = 'none';
        loadingEl.style.display = 'none';
    }, 300);
};

// Enhanced confirm function for billing
window.confirmBillingAction = function(message, title = 'Confirm Action') {
    return showBillingNotification('confirm', title, message, true);
};

// Specialized function for payment confirmations
window.confirmPaymentAction = function(message, title = 'Payment Confirmation') {
    return showBillingNotification('payment', title, message, true);
};

// Specialized function for delete confirmations  
window.confirmDeleteAction = function(message, title = 'Delete Confirmation') {
    return showBillingNotification('delete', title, message, true);
};

// Close modal when clicking overlay
document.addEventListener('click', function(e) {
    const modal = document.getElementById('billingNotificationModal');
    if (e.target === modal.querySelector('.notification-overlay')) {
        closeBillingNotification();
    }
});
</script><?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\cashier\modals\notification_system.blade.php ENDPATH**/ ?>