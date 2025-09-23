<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Users Management</title>
    <link rel="stylesheet" href="{{url('css/admin.css')}}">
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
                <button class="add-user-btn" onclick="openAddUserModal()">Add New User</button>
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
                            <td>{{ $user->name }}</td>
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
                                <div class="action-dropdown">
                                    <button class="action-btn" onclick="toggleDropdown({{ $user->id }})">
                                        <span>⋯</span>
                                    </button>
                                    <div class="dropdown-content" id="dropdown-{{ $user->id }}">
                                        <a href="#" onclick="editUser({{ $user->id }})">Edit</a>
                                        <a href="#" onclick="deleteUser({{ $user->id }})" class="delete-action">Delete</a>
                                    </div>
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

    // Dropdown Actions
    function toggleDropdown(userId) {
        const dropdown = document.getElementById(`dropdown-${userId}`);
        
        // Close all other dropdowns
        document.querySelectorAll('.dropdown-content').forEach(dd => {
            if (dd.id !== `dropdown-${userId}`) {
                dd.classList.remove('show');
            }
        });
        
        dropdown.classList.toggle('show');
    }

    async function editUser(userId) {
        // Close dropdown
        document.getElementById(`dropdown-${userId}`).classList.remove('show');
        
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
                
                // Clear any previous errors
                clearEditErrors();
                
                // Open modal
                openEditUserModal();
            } else {
                alert('Error loading user data: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while loading user data.');
        }
    }

    async function deleteUser(userId) {
        // Close dropdown
        document.getElementById(`dropdown-${userId}`).classList.remove('show');
        
        if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
            return;
        }
        
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
                alert('User deleted successfully!');
                location.reload(); // Refresh the page to update the user list
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while deleting the user.');
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
                    // Display validation errors
                    Object.keys(result.errors).forEach(field => {
                        const errorElement = document.getElementById(`edit${field.charAt(0).toUpperCase() + field.slice(1)}Error`);
                        const inputElement = document.getElementById(`editUser${field.charAt(0).toUpperCase() + field.slice(1)}`);
                        
                        if (errorElement && inputElement) {
                            errorElement.textContent = result.errors[field][0];
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

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.matches('.action-btn') && !event.target.matches('.action-btn span')) {
            document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                dropdown.classList.remove('show');
            });
        }
    });
    </script>
</body>
</html>