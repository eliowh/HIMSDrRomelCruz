<link rel="stylesheet" href="{{url('css/admincss/admin.css')}}">
<div id="addRoomModal" class="addUserModal">
    <div class="addUserModalContent">
        <button onclick="closeAddRoomModal()" class="addUserModalClose">&times;</button>
        <h2 class="sign">Add New Room</h2>

        @if(session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="error-message">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="createRoomForm">
            @csrf
            <div class="form-group">
                <label class="form-label">Room Name</label>
                <input type="text" 
                       name="room_name" 
                       class="form-input" 
                       required 
                       pattern="[a-zA-Z0-9\s\-]+"
                       title="Room name can contain letters, numbers, spaces, and hyphens"
                       minlength="2"
                       maxlength="50">
                <div class="error-text" style="display: none;"></div>
            </div>

            <div class="form-group">
                <label class="form-label">Room Price</label>
                <input type="text" 
                       name="room_price" 
                       class="form-input"
                       required 
                       placeholder="e.g., 1,500 or 1500"
                       step="0.01"
                       title="Please enter a valid price">
                <div class="error-text" style="display: none;"></div>
            </div>

            <button type="submit" class="assign-btn">
                Create Room
            </button>
        </form>
    </div>
</div>

<script>
document.getElementById('createRoomForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const nameField = form.querySelector('input[name="room_name"]');
    const priceField = form.querySelector('input[name="room_price"]');
    const submitBtn = form.querySelector('.assign-btn');
    
    // Clear previous error messages
    form.querySelectorAll('.error-text').forEach(error => {
        error.style.display = 'none';
        error.textContent = '';
    });
    form.querySelectorAll('.error').forEach(field => {
        field.classList.remove('error');
    });
    
    let isValid = true;
    
    // Room name validation
    if (!nameField.value.match(/^[a-zA-Z0-9\s\-]{2,50}$/)) {
        showError(nameField, 'Room name must be 2-50 characters and can only contain letters, numbers, spaces, and hyphens.');
        isValid = false;
    }
    
    // Price validation - handle comma-separated values
    const priceValue = priceField.value.replace(/,/g, '');
    if (!priceValue || isNaN(priceValue) || parseFloat(priceValue) < 0) {
        showError(priceField, 'Please enter a valid price (0 or greater).');
        isValid = false;
    }
    
    if (!isValid) return;
    
    // Submit the form
    submitBtn.textContent = 'Creating...';
    submitBtn.disabled = true;
    
    const formData = new FormData(form);
    
    fetch('/admin/rooms/create', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            // Server validation error
            return response.json().then(data => {
                if (data.errors) {
                    // Handle validation errors
                    Object.entries(data.errors).forEach(([field, messages]) => {
                        const input = form.querySelector(`[name="${field}"]`);
                        if (input) {
                            showError(input, messages[0]);
                        }
                    });
                    throw new Error('Validation failed');
                } else {
                    throw new Error(data.message || 'Server error');
                }
            });
        }
        
        // Try to parse as JSON, but handle if it's not valid JSON
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                // If not valid JSON, room was likely created but we got a redirect
                return { success: true, message: 'Room created successfully!' };
            }
        });
    })
    .then(data => {
        showSuccessMessage(data.message || 'Room created successfully!');
        resetForm(form);
        setTimeout(() => {
            try { closeAddRoomModal(); } catch (e) {}
            location.reload();
        }, 1000); // Show success message for 1 second before closing
    })
    .catch(error => {
        if (error.message !== 'Validation failed') {
            showErrorMessage(error.message || 'An error occurred');
        }
    })
    .finally(() => {
        submitBtn.textContent = 'Create Room';
        submitBtn.disabled = false;
    });
    
    function showError(field, message) {
        field.classList.add('error');
        const errorDiv = field.parentNode.querySelector('.error-text');
        if (errorDiv) {
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
        }
    }

    function showSuccessMessage(message) {
        // Remove any existing messages
        const existingMessages = document.querySelectorAll('.alert-success, .error-message');
        existingMessages.forEach(msg => msg.remove());
        
        // Create new success message
        const successDiv = document.createElement('div');
        successDiv.className = 'alert-success';
        successDiv.textContent = message;
        
        // Insert at the top of the modal content
        const modalContent = document.querySelector('#addRoomModal .addUserModalContent');
        const form = document.getElementById('createRoomForm');
        modalContent.insertBefore(successDiv, form);
    }

    function showErrorMessage(message) {
        // Remove any existing messages
        const existingMessages = document.querySelectorAll('.alert-success, .error-message');
        existingMessages.forEach(msg => msg.remove());
        
        // Create new error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        
        // Insert at the top of the modal content
        const modalContent = document.querySelector('#addRoomModal .addUserModalContent');
        const form = document.getElementById('createRoomForm');
        modalContent.insertBefore(errorDiv, form);
    }

    function resetForm(form) {
        form.reset();
        // Clear all error messages
        form.querySelectorAll('.error-text').forEach(error => {
            error.style.display = 'none';
            error.textContent = '';
        });
        form.querySelectorAll('.error').forEach(field => {
            field.classList.remove('error');
        });
    }
});

// Handle close button click
document.querySelector('#addRoomModal .addUserModalClose').addEventListener('click', function() {
    const form = document.getElementById('createRoomForm');
    resetForm(form);
    // Remove any success or error messages
    const messages = document.querySelectorAll('#addRoomModal .alert-success, #addRoomModal .error-message');
    messages.forEach(msg => msg.remove());
});
</script>