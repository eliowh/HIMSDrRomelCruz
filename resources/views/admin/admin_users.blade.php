<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Users Management</title>
    <link rel="stylesheet" href="{{asset('css/admincss/admin.css')}}">
    <link rel="stylesheet" href="{{asset('css/pagination.css')}}">
</head>
<body>
    @php
        $adminName = auth()->user()->name ?? 'Admin';
    @endphp
    @include('admin.admin_header')
    <div class="admin-layout">
        @include('admin.admin_sidebar')
        <div class="main-content">
            <h2>Users Management</h2>
            
            <!-- Controls Row -->
            <div class="controls-row">
                <div class="filter-search-controls">
                    <select id="roleFilter" class="role-select">
                        <option value="">All Roles</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="doctor" {{ request('role') == 'doctor' ? 'selected' : '' }}>Doctor</option>
                        <option value="nurse" {{ request('role') == 'nurse' ? 'selected' : '' }}>Nurse</option>
                        <option value="lab_technician" {{ request('role') == 'lab_technician' ? 'selected' : '' }}>Lab Technician</option>
                        <option value="inventory" {{ request('role') == 'inventory' ? 'selected' : '' }}>Inventory</option>
                        <option value="cashier" {{ request('role') == 'cashier' ? 'selected' : '' }}>Cashier</option>
                        <option value="pharmacy" {{ request('role') == 'pharmacy' ? 'selected' : '' }}>Pharmacy</option>
                        <option value="billing" {{ request('role') == 'billing' ? 'selected' : '' }}>Billing</option>
                    </select>
                    <input type="text" id="searchInput" placeholder="Search by name..." class="search-input" value="{{ request('search') }}">
                    <button id="searchButton" class="search-btn">Search</button>
                    @if(request('search') || request('role'))
                        <button id="clearButton" class="clear-btn">Clear</button>
                    @endif
                </div>
                <div class="action-controls">
                    <a href="{{ route('admin.users.archived') }}" class="archived-users-btn">
                        <i class="fas fa-archive"></i> View Archived Users
                    </a>
                    <button class="add-user-btn" onclick="openAddUserModal()">Add New User</button>
                </div>
            </div>

            <div class="admin-card">
                <!-- Users Table -->
                <table class="admin-table" id="usersTable">
                    <thead>
                        <tr>
                            <th class="sortable" data-sort="name">
                                Name
                                @if(request('sort') == 'name')
                                    <span class="sort-indicator {{ request('direction') == 'asc' ? 'asc' : 'desc' }}">
                                        {{ request('direction') == 'asc' ? '↑' : '↓' }}
                                    </span>
                                @else
                                    <span class="sort-indicator">↕</span>
                                @endif
                            </th>
                            <th class="sortable" data-sort="email">
                                Email
                                @if(request('sort') == 'email')
                                    <span class="sort-indicator {{ request('direction') == 'asc' ? 'asc' : 'desc' }}">
                                        {{ request('direction') == 'asc' ? '↑' : '↓' }}
                                    </span>
                                @else
                                    <span class="sort-indicator">↕</span>
                                @endif
                            </th>
                            <th class="sortable" data-sort="role">
                                Role
                                @if(request('sort') == 'role')
                                    <span class="sort-indicator {{ request('direction') == 'asc' ? 'asc' : 'desc' }}">
                                        {{ request('direction') == 'asc' ? '↑' : '↓' }}
                                    </span>
                                @else
                                    <span class="sort-indicator">↕</span>
                                @endif
                            </th>
                            <th class="sortable" data-sort="created_at">
                                Created
                                @if(request('sort') == 'created_at' || !request('sort'))
                                    <span class="sort-indicator {{ request('direction') == 'asc' ? 'asc' : 'desc' }}">
                                        {{ request('direction') == 'asc' ? '↑' : '↓' }}
                                    </span>
                                @else
                                    <span class="sort-indicator">↕</span>
                                @endif
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr data-role="{{ $user->role }}" data-name="{{ strtolower($user->name) }}">
                            <td>{{ $user->display_name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="role-badge role-{{ $user->role }}">
                                    @if($user->role === 'lab_technician')
                                        Lab Technician
                                    @else
                                        {{ ucfirst($user->role) }}
                                    @endif
                                </span>
                            </td>
                            <td>{{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="edit-btn" onclick="editUser({{ $user->id }})" title="Edit User">
                                        Edit
                                    </button>
                                    <button class="archive-btn" onclick="archiveUser({{ $user->id }})" title="Archive User">
                                        Archive
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @if($users->count() == 0)
                        <tr class="no-results">
                            <td colspan="5" style="text-align: center; color: #666; padding: 20px;">
                                @if(request('search') || request('role'))
                                    No users found matching your search criteria.
                                @else
                                    No users found.
                                @endif
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination Links -->
            <div class="pagination-wrapper">
                @include('components.custom-pagination', ['paginator' => $users])
            </div>
        </div>
    </div>

    @include('admin.modals.admin_createUser')
    @include('admin.modals.admin_editUser')

    <script>
    function openAddUserModal() {
        // Use flex so the modal container centers its content (CSS uses flex alignment)
        document.getElementById('addUserModal').style.display = 'flex';
    }
    function closeAddUserModal() {
        document.getElementById('addUserModal').style.display = 'none';
    }

    function openEditUserModal() {
        document.getElementById('editUserModal').style.display = 'flex';
    }

    function closeEditUserModal() {
        document.getElementById('editUserModal').style.display = 'none';
        // Reset form
        document.getElementById('editUserForm').reset();
        clearEditErrors();
    }

    function clearEditErrors() {
        document.querySelectorAll('#editUserModal .error-text').forEach(error => {
            error.textContent = '';
        });
        document.querySelectorAll('#editUserModal .form-input').forEach(input => {
            input.classList.remove('error');
        });
        document.getElementById('editErrorMessage').style.display = 'none';
        // Remove any existing success messages inside edit modal
        const existingSuccess = document.querySelectorAll('#editUserModal .alert-success');
        existingSuccess.forEach(el => el.remove());
    }

    // Filter and Search Functionality - Server-side search for cross-page results
    const roleFilter = document.getElementById('roleFilter');
    const searchInput = document.getElementById('searchInput');
    const searchButton = document.getElementById('searchButton');
    const clearButton = document.getElementById('clearButton');

    function performSearch() {
        const searchTerm = searchInput.value.trim();
        const selectedRole = roleFilter.value;
        
        // Build URL with search parameters
        const url = new URL(window.location.href);
        url.searchParams.delete('page'); // Reset to page 1 when searching
        
        if (searchTerm) {
            url.searchParams.set('search', searchTerm);
        } else {
            url.searchParams.delete('search');
        }
        
        if (selectedRole) {
            url.searchParams.set('role', selectedRole);
        } else {
            url.searchParams.delete('role');
        }
        
        // Redirect to perform server-side search
        window.location.href = url.toString();
    }

    function clearSearch() {
        // Remove all search parameters
        const url = new URL(window.location.href);
        url.searchParams.delete('search');
        url.searchParams.delete('role');
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }

    // Event listeners
    roleFilter.addEventListener('change', performSearch);
    searchButton.addEventListener('click', performSearch);
    
    if (clearButton) {
        clearButton.addEventListener('click', clearSearch);
    }
    
    // Allow Enter key to trigger search
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            performSearch();
        }
    });

    // Sorting Functionality
    function sortTable(column) {
        const url = new URL(window.location.href);
        const currentSort = url.searchParams.get('sort');
        const currentDirection = url.searchParams.get('direction');
        
        // If clicking the same column, toggle direction
        let newDirection = 'asc';
        if (currentSort === column && currentDirection === 'asc') {
            newDirection = 'desc';
        }
        
        // Set sort parameters
        url.searchParams.set('sort', column);
        url.searchParams.set('direction', newDirection);
        url.searchParams.delete('page'); // Reset to page 1 when sorting
        
        // Redirect with new sort parameters
        window.location.href = url.toString();
    }

    // Add click event listeners to sortable headers
    document.querySelectorAll('.sortable').forEach(header => {
        header.style.cursor = 'pointer';
        header.addEventListener('click', function() {
            const sortColumn = this.getAttribute('data-sort');
            sortTable(sortColumn);
        });
    });

    // User Actions
    async function editUser(userId) {
        try {
            // Fetch user data
            const response = await fetch(`/admin/users/${userId}/edit`);
            const result = await response.json();
            
            if (result.success) {
                // Populate form with user data
                document.getElementById('editUserId').value = result.user.id;
                document.getElementById('editUserName').value = result.user.name;
                document.getElementById('editUserEmail').value = result.user.email;
                document.getElementById('editUserRole').value = result.user.role;
                // Populate newly added fields if available
                try {
                    document.getElementById('editUserTitle').value = result.user.title || '';
                } catch (e) {}
                try {
                    document.getElementById('editUserLicense').value = result.user.license_number || '';
                } catch (e) {}
                
                // Clear any previous errors
                clearEditErrors();
                
                // Open modal
                openEditUserModal();
            } else {
                adminError('Error loading user data: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            adminError('An error occurred while loading user data.');
        }
    }

    async function archiveUser(userId) {
        adminConfirm(
            'Are you sure you want to archive this user? The account will be deactivated but all data will be preserved and can be restored later.',
            'Confirm Archive',
            () => performUserArchival(userId),
            () => console.log('User archival cancelled')
        );
        return;
    }

    async function performUserArchival(userId) {
        
        try {
            const response = await fetch(`/admin/users/${userId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                adminSuccess('User archived successfully! Account data has been preserved.');
                location.reload(); // Refresh the page to update the user list
            } else {
                adminError('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            adminError('An error occurred while archiving the user.');
        }
    }

    // Edit User Form Submission
    document.getElementById('editUserForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const userId = document.getElementById('editUserId').value;
        const submitBtn = this.querySelector('.assign-btn');
        const originalText = submitBtn.textContent;
        
        submitBtn.textContent = 'Updating...';
        submitBtn.disabled = true;
        
        // Clear previous errors
        clearEditErrors();
        
        // Create a proper FormData object and add method override
        const formData = new FormData();
    formData.append('name', document.getElementById('editUserName').value);
    formData.append('title', document.getElementById('editUserTitle') ? document.getElementById('editUserTitle').value : '');
    formData.append('license_number', document.getElementById('editUserLicense') ? document.getElementById('editUserLicense').value : '');
    formData.append('email', document.getElementById('editUserEmail').value);
    formData.append('role', document.getElementById('editUserRole').value);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}');
        formData.append('_method', 'PUT');
        
        console.log('Form data being sent:');
        for (let [key, value] of formData.entries()) {
            console.log(key, value);
        }
        
        try {
            const response = await fetch(`/admin/users/${userId}`, {
                method: 'POST', // Use POST with method override
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                }
            });
            
            const result = await response.json();
            console.log('Server response:', result);
            
            if (result.success) {
                // Show in-modal success message and refresh shortly after
                showUserSuccessMessage(result.message || 'User updated successfully!');
                setTimeout(() => {
                    try { closeEditUserModal(); } catch (e) {}
                    location.reload(); // Refresh to show updated data
                }, 1000);
            } else {
                if (result.errors) {
                    console.log('Validation errors:', result.errors);
                    // Display validation errors. Be resilient to different id/name conventions.
                    Object.keys(result.errors).forEach(field => {
                        // Build a couple of variants for error element and input element lookup
                        const fieldIdSuffix = field.charAt(0).toUpperCase() + field.slice(1);

                        // Prefer an error element with the standard id (e.g., editNameError or editLicense_numberError)
                        let errorElement = document.getElementById(`edit${fieldIdSuffix}Error`);
                        if (!errorElement) {
                            // Try a compacted version (e.g., editLicenseNumberError) to cover different id styles
                            const compact = `edit${field.replace(/_([a-z])/g, (m, p) => p.toUpperCase()).replace(/_/g, '')}Error`;
                            errorElement = document.getElementById(compact);
                        }

                        // For the input element, prefer name-based lookup inside the form; fallback to id-based lookup
                        const formInputByName = document.querySelector(`#editUserForm [name="${field}"]`);
                        const idVariant = `editUser${fieldIdSuffix.replace(/_([a-z])/g, (m, p) => p.toUpperCase())}`;
                        const inputElement = formInputByName || document.getElementById(idVariant) || document.getElementById(`editUser${fieldIdSuffix}`);

                        // Set the error text if we have an element for it
                        if (errorElement) {
                            errorElement.textContent = result.errors[field][0];
                            errorElement.style.display = 'block';
                        } else {
                            // If no specific error element, show a generic in-modal error area
                            document.getElementById('editErrorMessage').textContent = result.errors[field][0];
                            document.getElementById('editErrorMessage').style.display = 'block';
                        }

                        // Mark the input invalid if we found it
                        if (inputElement) {
                            inputElement.classList.add('error');
                        }
                    });
                } else {
                    document.getElementById('editErrorMessage').textContent = result.message;
                    document.getElementById('editErrorMessage').style.display = 'block';
                }
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('editErrorMessage').textContent = 'An error occurred while updating the user.';
            document.getElementById('editErrorMessage').style.display = 'block';
        } finally {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    });

    // Show success message inside the edit user modal (keeps behavior consistent with create flows)
    function showUserSuccessMessage(message) {
        // Remove any existing messages
        const existingMessages = document.querySelectorAll('#editUserModal .alert-success, #editUserModal .error-message');
        existingMessages.forEach(msg => msg.remove());

        const successDiv = document.createElement('div');
        successDiv.className = 'alert-success';
        successDiv.textContent = message;

        const form = document.getElementById('editUserForm');
        form.parentElement.insertBefore(successDiv, form);
    }

    function showUserErrorMessage(message) {
        // Remove existing messages
        const existingMessages = document.querySelectorAll('#editUserModal .alert-success, #editUserModal .error-message');
        existingMessages.forEach(msg => msg.remove());

        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;

        const form = document.getElementById('editUserForm');
        form.parentElement.insertBefore(errorDiv, form);
    }
    </script>
    @include('admin.modals.notification_system')
</body>
</html>