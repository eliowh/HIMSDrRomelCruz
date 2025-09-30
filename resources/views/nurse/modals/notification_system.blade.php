<!-- Nurse Notification System Modal -->
<div id="nurseNotificationModal" class="notification-modal" style="display: none;">
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
/* Nurse Notification System Styles */
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
    background: linear-gradient(135deg, #4f7942 0%, #6b8e5a 100%);
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
    font-size: 1.5rem;
    font-weight: 600;
}

.notification-body {
    padding: 25px;
    background: white;
    min-height: 100px;
}

.notification-body p {
    margin: 0;
    color: #2d3748;
    font-size: 1rem;
    line-height: 1.6;
}

.loading-spinner {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 20px;
}

.spinner {
    width: 20px;
    height: 20px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #4f7942;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.notification-footer {
    padding: 20px 25px;
    background: #f7fafc;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    border-top: 1px solid #e2e8f0;
}

.notification-footer .btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.notification-footer .btn-primary {
    background: #4f7942;
    color: white;
}

.notification-footer .btn-primary:hover {
    background: #3d5e33;
    transform: translateY(-1px);
}

.notification-footer .btn-secondary {
    background: #e2e8f0;
    color: #4a5568;
}

.notification-footer .btn-secondary:hover {
    background: #cbd5e0;
}

/* Header notification styles */
.header-actions {
    display: flex;
    align-items: center;
    gap: 20px;
}

.notification-bell {
    position: relative;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: background-color 0.2s ease;
}

.notification-bell:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.bell-icon {
    font-size: 20px;
    color: white;
}

.notification-badge {
    position: absolute;
    top: 0;
    right: 0;
    background: #e53e3e;
    color: white;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    font-size: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.notification-dropdown {
    position: absolute;
    top: 100%;
    right: 20px;
    width: 350px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    z-index: 1000;
    overflow: hidden;
}

.notification-dropdown .notification-header {
    background: #4f7942;
    color: white;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.notification-dropdown .notification-header h4 {
    margin: 0;
    font-size: 1.1rem;
}

.mark-all-read {
    background: none;
    border: none;
    color: white;
    font-size: 0.9rem;
    cursor: pointer;
    text-decoration: underline;
}

.notification-list {
    max-height: 300px;
    overflow-y: auto;
}

.no-notifications {
    padding: 30px 20px;
    text-align: center;
    color: #718096;
    font-style: italic;
}

.notification-item {
    padding: 15px 20px;
    border-bottom: 1px solid #e2e8f0;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.notification-item:hover {
    background-color: #f7fafc;
}

.notification-item.unread {
    background-color: #ebf8ff;
    border-left: 4px solid #4f7942;
}

.notification-item h5 {
    margin: 0 0 5px 0;
    font-size: 0.95rem;
    color: #2d3748;
}

.notification-item p {
    margin: 0;
    font-size: 0.85rem;
    color: #718096;
    line-height: 1.4;
}

.notification-time {
    font-size: 0.75rem;
    color: #a0aec0;
    margin-top: 5px;
}

/* Icon variations */
.notification-icon.success .fas {
    color: #38a169;
}

.notification-icon.error .fas {
    color: #e53e3e;
}

.notification-icon.warning .fas {
    color: #d69e2e;
}

.notification-icon.info .fas {
    color: #3182ce;
}
</style>

<script>
// Nurse Notification System JavaScript
let nurseNotifications = [];

function nurseNotify(type = 'info', title = 'Notification', message = '', options = {}) {
    const modal = document.getElementById('nurseNotificationModal');
    const icon = document.getElementById('notificationIcon');
    const titleEl = document.getElementById('notificationTitle');
    const messageEl = document.getElementById('notificationMessage');
    const confirmBtn = document.getElementById('notificationConfirm');
    const cancelBtn = document.getElementById('notificationCancel');
    const okBtn = document.getElementById('notificationOk');
    const iconContainer = icon.parentElement;

    // Set icon based on type
    iconContainer.className = `notification-icon ${type}`;
    switch(type) {
        case 'success':
            icon.className = 'fas fa-check-circle';
            break;
        case 'error':
            icon.className = 'fas fa-exclamation-circle';
            break;
        case 'warning':
            icon.className = 'fas fa-exclamation-triangle';
            break;
        case 'confirm':
            icon.className = 'fas fa-question-circle';
            break;
        default:
            icon.className = 'fas fa-info-circle';
    }

    titleEl.textContent = title;
    messageEl.textContent = message;

    // Handle buttons based on type
    if (type === 'confirm') {
        confirmBtn.style.display = 'inline-flex';
        cancelBtn.style.display = 'inline-flex';
        okBtn.style.display = 'none';
        
        confirmBtn.onclick = () => {
            hideNurseNotification();
            if (options.onConfirm) options.onConfirm();
        };
        
        cancelBtn.onclick = () => {
            hideNurseNotification();
            if (options.onCancel) options.onCancel();
        };
    } else {
        confirmBtn.style.display = 'none';
        cancelBtn.style.display = 'none';
        okBtn.style.display = 'inline-flex';
        
        okBtn.onclick = () => {
            hideNurseNotification();
            if (options.onOk) options.onOk();
        };
    }

    // Show modal
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('show'), 10);

    // Add to notifications list
    addToNotificationsList(type, title, message);
}

function hideNurseNotification() {
    const modal = document.getElementById('nurseNotificationModal');
    modal.classList.remove('show');
    setTimeout(() => modal.style.display = 'none', 300);
}

function nurseSuccess(title, message, onOk) {
    nurseNotify('success', title, message, { onOk });
}

function nurseError(title, message, onOk) {
    nurseNotify('error', title, message, { onOk });
}

function nurseWarning(title, message, onOk) {
    nurseNotify('warning', title, message, { onOk });
}

function nurseConfirm(title, message, onConfirm, onCancel) {
    nurseNotify('confirm', title, message, { onConfirm, onCancel });
}

function addToNotificationsList(type, title, message) {
    const notification = {
        id: Date.now(),
        type,
        title,
        message,
        time: new Date(),
        read: false
    };
    
    nurseNotifications.unshift(notification);
    updateNotificationUI();
}

function updateNotificationUI() {
    const badge = document.getElementById('notificationBadge');
    const list = document.getElementById('notificationList');
    
    const unreadCount = nurseNotifications.filter(n => !n.read).length;
    
    if (unreadCount > 0) {
        badge.style.display = 'flex';
        badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
    } else {
        badge.style.display = 'none';
    }
    
    if (nurseNotifications.length === 0) {
        list.innerHTML = '<div class="no-notifications">No new notifications</div>';
        return;
    }
    
    list.innerHTML = nurseNotifications.slice(0, 10).map(notification => `
        <div class="notification-item ${!notification.read ? 'unread' : ''}" onclick="markNotificationAsRead(${notification.id})">
            <h5>${notification.title}</h5>
            <p>${notification.message}</p>
            <div class="notification-time">${formatNotificationTime(notification.time)}</div>
        </div>
    `).join('');
}

function toggleNotifications() {
    const dropdown = document.getElementById('notificationDropdown');
    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
}

function markNotificationAsRead(id) {
    nurseNotifications = nurseNotifications.map(n => 
        n.id === id ? { ...n, read: true } : n
    );
    updateNotificationUI();
}

function markAllAsRead() {
    nurseNotifications = nurseNotifications.map(n => ({ ...n, read: true }));
    updateNotificationUI();
}

function formatNotificationTime(time) {
    const now = new Date();
    const diff = now - time;
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);
    
    if (days > 0) return `${days}d ago`;
    if (hours > 0) return `${hours}h ago`;
    if (minutes > 0) return `${minutes}m ago`;
    return 'Just now';
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('notificationDropdown');
    const bell = document.querySelector('.notification-bell');
    
    if (dropdown && !dropdown.contains(event.target) && !bell.contains(event.target)) {
        dropdown.style.display = 'none';
    }
});

// Override default alert and confirm functions for nurses
if (typeof window !== 'undefined') {
    window.alert = function(message) {
        nurseNotify('info', 'Alert', message);
    };
    
    window.confirm = function(message) {
        return new Promise((resolve) => {
            nurseConfirm('Confirm', message, 
                () => resolve(true), 
                () => resolve(false)
            );
        });
    };
}
</script>