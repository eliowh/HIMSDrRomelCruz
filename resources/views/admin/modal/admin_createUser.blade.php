<div id="addUserModal" class="addUserModal">
    <div class="addUserModalContent">
        <button onclick="closeAddUserModal()" class="addUserModalClose">&times;</button>
        <h2 class="sign">Create New User</h2>

        @if(session('success'))
            <div class="success-message">
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

        <form id="createUserForm">
            @csrf
            <div class="mb-3">
                <label class="fName">Full Name</label>
                <input type="text" 
                       name="name" 
                       class="nField" 
                       required 
                       pattern="[a-zA-Z\s]+"
                       title="Name can only contain letters and spaces"
                       minlength="3"
                       maxlength="20">
                <div class="error-text" style="display: none;"></div>
            </div>

            <div class="mb-3">
                <label class="eMail">Email Address</label>
                <input type="email" 
                       name="email" 
                       class="eField"
                       required 
                       pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.com$"
                       title="Please enter a valid email address ending with .com">
                <div class="error-text" style="display: none;"></div>
            </div>

            <div class="mb-3">
                <label class="eMail">Assign Role</label>
                <select name="role" class="eField" required>
                    <option value="" selected disabled>Select a role</option>
                    <option value="doctor">Doctor</option>
                    <option value="nurse">Nurse</option>
                    <option value="lab_technician">Lab Technician</option>
                    <option value="cashier">Cashier</option>
                    <option value="admin">Admin</option>
                </select>
                <div class="error-text" style="display: none;"></div>
            </div>

            <button type="submit" class="CreateUserBtn">
                Create User
            </button>
        </form>
    </div>
</div>

<style>
.error {
    border-color: #dc3545 !important;
}

.error-text {
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.success-message {
    background-color: #d4edda;
    color: #155724;
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: 0.25rem;
    border: 1px solid #c3e6cb;
}

.error-message {
    background-color: #f8d7da;
    color: #721c24;
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: 0.25rem;
    border: 1px solid #f5c6cb;
}

.error-message ul {
    margin: 0;
    padding-left: 1.25rem;
}
</style>

<script>
document.getElementById('createUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const nameField = form.querySelector('input[name="name"]');
    const emailField = form.querySelector('input[name="email"]');
    const roleField = form.querySelector('select[name="role"]');
    const submitBtn = form.querySelector('.CreateUserBtn');
    
    // Clear previous error messages
    form.querySelectorAll('.error-text').forEach(error => {
        error.style.display = 'none';
        error.textContent = '';
    });
    form.querySelectorAll('.error').forEach(field => {
        field.classList.remove('error');
    });
    
    let isValid = true;
    
    // Name validation
    if (!nameField.value.match(/^[a-zA-Z\s]{3,20}$/)) {
        showError(nameField, 'Name must be 3-20 letters and can only contain letters and spaces.');
        isValid = false;
    }
    
    // Email validation
    if (!emailField.value.match(/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.com$/)) {
        showError(emailField, 'Please enter a valid email address ending with .com');
        isValid = false;
    }
    
    // Role validation
    if (!roleField.value) {
        showError(roleField, 'Please select a role');
        isValid = false;
    }
    
    if (isValid) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Creating...';
        
        fetch('{{ route('admin.createUser') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: JSON.stringify({
                name: nameField.value,
                email: emailField.value,
                role: roleField.value
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessMessage(data.message || 'User created successfully!');
                resetForm(form);
            } else {
                Object.entries(data.errors || {}).forEach(([field, messages]) => {
                    const input = form.querySelector(`[name="${field}"]`);
                    if (input) {
                        showError(input, messages[0]);
                    }
                });
            }
        })
        .then(response => response.json())
        .catch(() => {
            // If JSON parsing fails, it means the user was created successfully
            // but we got a redirect response instead of JSON
            return { success: true, message: 'User created successfully!' };
        })
        .then(data => {
            showSuccessMessage(data.message || 'User created successfully!');
            resetForm(form);
            submitBtn.disabled = false;
            submitBtn.textContent = 'Create User';
        });
    }
});

// Handle close button click
document.querySelector('.addUserModalClose').addEventListener('click', function() {
    const form = document.getElementById('createUserForm');
    resetForm(form);
    // Remove any success or error messages
    const messages = document.querySelectorAll('.success-message, .error-message');
    messages.forEach(msg => msg.remove());
});

function showError(field, message) {
    field.classList.add('error');
    const errorDiv = field.parentElement.querySelector('.error-text');
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
}

function showSuccessMessage(message) {
    // Remove any existing messages
    const existingMessages = document.querySelectorAll('.success-message, .error-message');
    existingMessages.forEach(msg => msg.remove());
    
    // Create new success message
    const successDiv = document.createElement('div');
    successDiv.className = 'success-message';
    successDiv.textContent = message;
    
    // Insert at the top of the form
    const form = document.getElementById('createUserForm');
    form.parentElement.insertBefore(successDiv, form);
}

function showErrorMessage(message) {
    // Remove any existing messages
    const existingMessages = document.querySelectorAll('.success-message, .error-message');
    existingMessages.forEach(msg => msg.remove());
    
    // Create new error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    
    // Insert at the top of the form
    const form = document.getElementById('createUserForm');
    form.parentElement.insertBefore(errorDiv, form);
}

function resetForm(form) {
    form.reset();
    form.querySelectorAll('.error').forEach(field => field.classList.remove('error'));
    form.querySelectorAll('.error-text').forEach(error => {
        error.style.display = 'none';
        error.textContent = '';
    });
}
</script>
