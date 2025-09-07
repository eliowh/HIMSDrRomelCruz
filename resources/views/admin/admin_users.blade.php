<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                        <option value="admin">Admin</option>
                        <option value="doctor">Doctor</option>
                        <option value="nurse">Nurse</option>
                        <option value="lab_technician">Lab Technician</option>
                        <option value="cashier">Cashier</option>
                    </select>
                    <input type="text" id="searchInput" placeholder="Search by name..." class="search-input">
                </div>
                <button class="add-user-btn" onclick="openAddUserModal()">Add New User</button>
            </div>

            <div class="admin-card">
                <!-- Users Table -->
                <table class="admin-table" id="usersTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created</th>
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
                                    {{ $user->role === 'lab_technician' ? 'Lab Technician' : ucfirst($user->role) }}
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
                        <tr class="no-results" style="display: none;">
                            <td colspan="5" style="text-align: center; color: #666; padding: 20px;">No users found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('admin.modal.admin_createUser')

    <script>
    function openAddUserModal() {
        // Use flex so the modal container centers its content (CSS uses flex alignment)
        document.getElementById('addUserModal').style.display = 'flex';
    }
    function closeAddUserModal() {
        document.getElementById('addUserModal').style.display = 'none';
    }

    // Filter and Search Functionality
    const roleFilter = document.getElementById('roleFilter');
    const searchInput = document.getElementById('searchInput');
    const usersTable = document.getElementById('usersTable');
    const tableRows = usersTable.querySelectorAll('tbody tr');

    function filterUsers() {
        const selectedRole = roleFilter.value.toLowerCase();
        const searchTerm = searchInput.value.toLowerCase();

        let visibleCount = 0;
        tableRows.forEach(row => {
            if (row.classList.contains('no-results')) return; // skip placeholder
            const userRole = row.getAttribute('data-role') || '';
            const userName = row.getAttribute('data-name') || '';
            
            const matchesRole = !selectedRole || userRole === selectedRole;
            const matchesSearch = !searchTerm || userName.includes(searchTerm);
            
            if (matchesRole && matchesSearch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const noResultsRow = document.querySelector('#usersTable tbody tr.no-results');
        if (noResultsRow) {
            noResultsRow.style.display = visibleCount === 0 ? '' : 'none';
        }
    }

    roleFilter.addEventListener('change', filterUsers);
    searchInput.addEventListener('input', filterUsers);

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

    function editUser(userId) {
        // Close dropdown
        document.getElementById(`dropdown-${userId}`).classList.remove('show');
        
        // TODO: Implement edit user functionality
        alert(`Edit user ${userId} - Feature coming soon!`);
    }

    function deleteUser(userId) {
        // Close dropdown
        document.getElementById(`dropdown-${userId}`).classList.remove('show');
        
        if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
            // TODO: Implement delete user functionality
            alert(`Delete user ${userId} - Feature coming soon!`);
        }
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