{{-- Pharmacy Notification System --}}
<div id="pharmacyNotificationOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 10000; justify-content: center; align-items: center;">
    <div id="pharmacyNotificationModal" style="background: white; border-radius: 8px; padding: 20px; max-width: 500px; width: 90%; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3); position: relative;">
        <div id="pharmacyNotificationHeader" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
            <h3 id="pharmacyNotificationTitle" style="margin: 0; color: #333; font-size: 18px; font-weight: 600;"></h3>
            <button id="pharmacyNotificationClose" onclick="closePharmacyNotification()" style="background: none; border: none; font-size: 24px; color: #999; cursor: pointer; padding: 0; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">&times;</button>
        </div>
        <div id="pharmacyNotificationContent" style="margin-bottom: 20px; color: #555; line-height: 1.5;"></div>
        <div id="pharmacyNotificationButtons" style="display: flex; justify-content: flex-end; gap: 10px;"></div>
    </div>
</div>

<script>
// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    
// Pharmacy Notification System
const PharmacyNotificationSystem = {
    show: function(message, type = 'info', title = 'Notice', callback = null) {
        const overlay = document.getElementById('pharmacyNotificationOverlay');
        const titleEl = document.getElementById('pharmacyNotificationTitle');
        const contentEl = document.getElementById('pharmacyNotificationContent');
        const buttonsEl = document.getElementById('pharmacyNotificationButtons');
        
        // Set title
        titleEl.textContent = title;
        
        // Set content
        if (typeof message === 'string') {
            contentEl.innerHTML = message;
        } else {
            contentEl.innerHTML = message.toString();
        }
        
        // Clear previous buttons
        buttonsEl.innerHTML = '';
        
        // Create buttons based on type
        if (type === 'confirm') {
            const confirmBtn = document.createElement('button');
            confirmBtn.textContent = 'Confirm';
            confirmBtn.style.cssText = 'background: #4CAF50; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; margin-left: 10px;';
            confirmBtn.onclick = () => {
                closePharmacyNotification();
                if (callback) callback(true);
            };
            
            const cancelBtn = document.createElement('button');
            cancelBtn.textContent = 'Cancel';
            cancelBtn.style.cssText = 'background: #f44336; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;';
            cancelBtn.onclick = () => {
                closePharmacyNotification();
                if (callback) callback(false);
            };
            
            buttonsEl.appendChild(cancelBtn);
            buttonsEl.appendChild(confirmBtn);
        } else if (type === 'loading') {
            const loadingDiv = document.createElement('div');
            loadingDiv.innerHTML = '<div style="text-align: center;"><div style="border: 3px solid #f3f3f3; border-top: 3px solid #3498db; border-radius: 50%; width: 30px; height: 30px; animation: spin 1s linear infinite; margin: 0 auto;"></div></div>';
            buttonsEl.appendChild(loadingDiv);
            
            // Add CSS for loading animation
            if (!document.getElementById('loadingCSS')) {
                const style = document.createElement('style');
                style.id = 'loadingCSS';
                style.textContent = '@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }';
                document.head.appendChild(style);
            }
        } else {
            const okBtn = document.createElement('button');
            okBtn.textContent = 'OK';
            okBtn.style.cssText = 'background: #2196F3; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;';
            okBtn.onclick = () => {
                closePharmacyNotification();
                if (callback) callback();
            };
            buttonsEl.appendChild(okBtn);
        }
        
        // Show overlay
        overlay.style.display = 'flex';
    }
};

// Helper functions for different notification types - make them global
window.showPharmacySuccess = function(message, title = 'Success') {
    PharmacyNotificationSystem.show(message, 'success', title);
};

window.showPharmacyError = function(message, title = 'Error') {
    PharmacyNotificationSystem.show(message, 'error', title);
};

window.showPharmacyInfo = function(message, title = 'Information') {
    PharmacyNotificationSystem.show(message, 'info', title);
};

window.showPharmacyConfirm = function(message, title = 'Confirm', callback = null) {
    PharmacyNotificationSystem.show(message, 'confirm', title, callback);
};

window.showPharmacyLoading = function(message = 'Loading...', title = 'Please Wait') {
    PharmacyNotificationSystem.show(message, 'loading', title);
};

// Close notification function - make it global
window.closePharmacyNotification = function() {
    const overlay = document.getElementById('pharmacyNotificationOverlay');
    if (overlay) {
        overlay.style.display = 'none';
    }
};

// Close notification when clicking outside
document.addEventListener('click', function(event) {
    const overlay = document.getElementById('pharmacyNotificationOverlay');
    if (event.target === overlay) {
        closePharmacyNotification();
    }
});

}); // End DOMContentLoaded
</script>