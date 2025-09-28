<!-- Universal Logout Confirmation Modal -->
<!-- Ensure Font Awesome is available for the modal -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<div id="logoutModal" class="logout-modal-overlay" style="display: none;">
    <div class="logout-modal">
        <div class="logout-modal-header">
            <div class="logout-icon">
                <i class="fas fa-sign-out-alt"></i>
            </div>
            <h3>Confirm Logout</h3>
        </div>
        <div class="logout-modal-body">
            <p>Are you sure you want to logout?</p>
            <p class="logout-warning">You will be redirected to the login page.</p>
        </div>
        <div class="logout-modal-footer">
            <button id="confirmLogoutBtn" class="btn-logout-confirm">
                <i class="fas fa-check" aria-hidden="true"></i> Yes, Logout
            </button>
            <button id="cancelLogoutBtn" class="btn-logout-cancel">
                <i class="fas fa-times" aria-hidden="true"></i> Cancel
            </button>
        </div>
    </div>
</div>

<!-- Logout Modal Styles -->
<style>
.logout-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    z-index: 10000;
    display: flex;
    justify-content: center;
    align-items: center;
    animation: fadeIn 0.3s ease-in-out;
}

.logout-modal {
    background: white;
    border-radius: 12px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
    max-width: 420px;
    width: 90%;
    overflow: hidden;
    animation: slideIn 0.3s ease-out;
}

.logout-modal-header {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
    padding: 25px;
    text-align: center;
}

.logout-icon {
    font-size: 48px;
    margin-bottom: 15px;
    opacity: 0.9;
}

/* Ensure icons in buttons are always displayed */
.logout-modal-footer .fas {
    display: inline-block !important;
    font-family: "Font Awesome 6 Free" !important;
    font-weight: 900 !important;
    font-style: normal !important;
}

.logout-modal-header h3 {
    margin: 0;
    font-size: 22px;
    font-weight: 600;
}

.logout-modal-body {
    padding: 30px 25px;
    text-align: center;
}

.logout-modal-body p {
    margin: 0 0 15px 0;
    font-size: 16px;
    color: #495057;
    line-height: 1.5;
}

.logout-warning {
    font-size: 14px !important;
    color: #6c757d !important;
    font-style: italic;
}

.logout-modal-footer {
    padding: 20px 25px;
    background: #f8f9fa;
    display: flex;
    gap: 15px;
    justify-content: center;
}

.btn-logout-confirm, .btn-logout-cancel {
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 120px;
    justify-content: center;
}

.btn-logout-confirm {
    background: #dc3545;
    color: white;
}

.btn-logout-confirm:hover {
    background: #c82333;
    transform: translateY(-1px);
}

.btn-logout-cancel {
    background: #6c757d;
    color: white;
}

.btn-logout-cancel:hover {
    background: #545b62;
    transform: translateY(-1px);
}

/* Additional icon styles to ensure they display correctly */
.btn-logout-confirm i.fas.fa-check, 
.btn-logout-cancel i.fas.fa-times {
    font-size: 14px;
    line-height: 1;
    width: 14px;
    height: 14px;
    text-align: center;
    vertical-align: middle;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideIn {
    from { 
        transform: translateY(-30px) scale(0.9);
        opacity: 0;
    }
    to { 
        transform: translateY(0) scale(1);
        opacity: 1;
    }
}

/* Loading state */
.logout-modal.logging-out .logout-modal-body {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
}

.logout-modal.logging-out .logout-modal-footer {
    display: none;
}

.logout-spinner {
    border: 3px solid #f3f3f3;
    border-top: 3px solid #dc3545;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin-bottom: 15px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<!-- Logout Modal JavaScript -->
<script>
// Global logout modal system
window.LogoutModal = {
    currentFormId: null,
    
    show: function(formId) {
        this.currentFormId = formId;
        const modal = document.getElementById('logoutModal');
        modal.style.display = 'flex';
        
        // Focus the confirm button
        setTimeout(() => {
            document.getElementById('confirmLogoutBtn').focus();
        }, 100);
    },
    
    hide: function() {
        const modal = document.getElementById('logoutModal');
        modal.style.display = 'none';
        this.currentFormId = null;
    },
    
    confirm: function() {
        if (!this.currentFormId) return;
        
        // Show loading state
        const modal = document.querySelector('.logout-modal');
        const body = modal.querySelector('.logout-modal-body');
        
        modal.classList.add('logging-out');
        
        // Create elements instead of using innerHTML for better reliability
        body.innerHTML = ''; // Clear existing content
        
        const spinner = document.createElement('div');
        spinner.className = 'logout-spinner';
        
        const message = document.createElement('p');
        message.textContent = 'Logging you out...';
        
        const warning = document.createElement('p');
        warning.className = 'logout-warning';
        warning.textContent = 'Please wait while we securely log you out.';
        
        body.appendChild(spinner);
        body.appendChild(message);
        body.appendChild(warning);
        
        // Clear localStorage and submit form
        localStorage.clear();
        sessionStorage.clear();
        
        // Add a small delay for better UX
        setTimeout(() => {
            const form = document.getElementById(this.currentFormId);
            if (form) {
                form.submit();
            }
        }, 1000);
    }
};

// Event listeners for modal buttons
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('confirmLogoutBtn').addEventListener('click', function() {
        LogoutModal.confirm();
    });
    
    document.getElementById('cancelLogoutBtn').addEventListener('click', function() {
        LogoutModal.hide();
    });
    
    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('logoutModal');
            if (modal.style.display === 'flex') {
                LogoutModal.hide();
            }
        }
    });
    
    // Close on overlay click
    document.getElementById('logoutModal').addEventListener('click', function(e) {
        if (e.target === this) {
            LogoutModal.hide();
        }
    });
});

// Global function for confirming logout (to be called from sidebar buttons)
function confirmLogout(formId) {
    LogoutModal.show(formId);
}
</script>