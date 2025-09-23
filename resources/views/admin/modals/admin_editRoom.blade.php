<!-- Edit Room Modal -->
<div id="editRoomModal" class="addUserModal">
    <div class="addUserModalContent">
        <button class="addUserModalClose" onclick="closeEditRoomModal()">&times;</button>
        <div class="sign">Edit Room</div>
        
        <form id="editRoomForm">
            @csrf
            @method('PUT')
            <input type="hidden" id="editRoomId" name="id">
            
            <div class="form-group">
                <label for="editRoomName" class="form-label">Room Name</label>
                <input type="text" id="editRoomName" name="room_name" class="form-input" required>
                <span class="error-text" id="editNameError"></span>
            </div>
            
            <div class="form-group">
                <label for="editRoomPrice" class="form-label">Room Price</label>
                <input type="text" id="editRoomPrice" name="room_price" class="form-input" required placeholder="e.g., 1,500 or 1500" step="1.00">
                <span class="error-text" id="editPriceError"></span>
            </div>
            
            <div id="editErrorMessage" class="error-message" style="display: none;"></div>
            
            <button type="submit" class="assign-btn">Update Room</button>
        </form>
    </div>
</div>

<script>
document.getElementById('editRoomForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('.assign-btn');
    const originalText = submitBtn.textContent;
    
    // Update button state
    submitBtn.textContent = 'Updating...';
    submitBtn.disabled = true;
    
    // Clear previous errors
    document.querySelectorAll('#editRoomModal .error-text').forEach(error => {
        error.textContent = '';
    });
    document.querySelectorAll('#editRoomModal .form-input').forEach(input => {
        input.classList.remove('error');
    });
    document.getElementById('editErrorMessage').style.display = 'none';
    
    try {
        const response = await fetch(`/admin/rooms/update`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(Object.fromEntries(formData))
        });
        
        if (!response.ok) {
            // Server validation error
            const data = await response.json();
            if (data.errors) {
                // Handle validation errors
                Object.entries(data.errors).forEach(([field, messages]) => {
                    let errorElementId = '';
                    let inputElementId = '';
                    
                    // Map field names to actual element IDs
                    if (field === 'room_name') {
                        errorElementId = 'editNameError';
                        inputElementId = 'editRoomName';
                    } else if (field === 'room_price') {
                        errorElementId = 'editPriceError';
                        inputElementId = 'editRoomPrice';
                    }
                    
                    const errorElement = document.getElementById(errorElementId);
                    const inputElement = document.getElementById(inputElementId);
                    
                    if (errorElement && inputElement) {
                        inputElement.classList.add('error');
                        errorElement.textContent = messages[0];
                        errorElement.style.display = 'block';
                    }
                });
                throw new Error('Validation failed');
            } else {
                throw new Error(data.message || 'Server error');
            }
        }
        
        // Try to parse as JSON, but handle if it's not valid JSON
        const text = await response.text();
        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            // If not valid JSON, room was likely updated but we got a redirect
            result = { success: true, message: 'Room updated successfully!' };
        }
        
        showSuccessMessage(result.message || 'Room updated successfully!');
        resetEditForm();
        setTimeout(() => {
            try { closeEditRoomModal(); } catch (e) {}
            location.reload();
        }, 1000); // Show success message for 1 second before closing
        
    } catch (error) {
        if (error.message !== 'Validation failed') {
            showErrorMessage(error.message || 'An error occurred');
        }
    } finally {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    }
});

function showSuccessMessage(message) {
    // Remove any existing messages
    const existingMessages = document.querySelectorAll('#editRoomModal .alert-success, #editRoomModal .error-message');
    existingMessages.forEach(msg => msg.remove());
    
    // Create new success message
    const successDiv = document.createElement('div');
    successDiv.className = 'alert-success';
    successDiv.textContent = message;
    
    // Insert at the top of the modal content
    const modalContent = document.querySelector('#editRoomModal .addUserModalContent');
    const form = document.getElementById('editRoomForm');
    modalContent.insertBefore(successDiv, form);
}

function showErrorMessage(message) {
    // Remove any existing messages
    const existingMessages = document.querySelectorAll('#editRoomModal .alert-success, #editRoomModal .error-message');
    existingMessages.forEach(msg => msg.remove());
    
    // Create new error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    
    // Insert at the top of the modal content
    const modalContent = document.querySelector('#editRoomModal .addUserModalContent');
    const form = document.getElementById('editRoomForm');
    modalContent.insertBefore(errorDiv, form);
}

function resetEditForm() {
    const form = document.getElementById('editRoomForm');
    // Clear all error messages
    form.querySelectorAll('.error-text').forEach(error => {
        error.style.display = 'none';
        error.textContent = '';
    });
    form.querySelectorAll('.form-input').forEach(field => {
        field.classList.remove('error');
    });
}

// Handle close button click
document.querySelector('#editRoomModal .addUserModalClose').addEventListener('click', function() {
    resetEditForm();
    // Remove any success or error messages
    const messages = document.querySelectorAll('#editRoomModal .alert-success, #editRoomModal .error-message');
    messages.forEach(msg => msg.remove());
});
</script>