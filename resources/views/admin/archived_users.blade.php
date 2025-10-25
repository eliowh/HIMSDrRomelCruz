<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Archived Users Management</title>
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
            <div class="page-header">
                <h2>Archived Users Management</h2>
                <a href="{{ route('admin.users') }}" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Active Users
                </a>
            </div>
            
            <!-- Controls Row -->
            <div class="controls-row">
                <div class="filter-search-controls">
                    <input type="text" id="searchInput" placeholder="Search archived users..." class="search-input" value="{{ request('search') }}">
                    <button id="searchButton" class="search-btn">Search</button>
                    @if(request('search'))
                        <button id="clearButton" class="clear-btn">Clear</button>
                    @endif
                </div>
                <div class="archive-stats">
                    <span class="stat-text">Total Archived: {{ $archivedUsers->total() }}</span>
                </div>
            </div>

            <div class="admin-card">
                <!-- Archived Users Table -->
                <table class="admin-table" id="archivedUsersTable">
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
                            <th class="sortable" data-sort="deleted_at">
                                Archived Date
                                @if(request('sort') == 'deleted_at' || !request('sort'))
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
                        @foreach($archivedUsers as $user)
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
                            <td>{{ $user->deleted_at ? $user->deleted_at->format('M d, Y H:i') : 'N/A' }}</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="restore-btn" onclick="restoreUser({{ $user->id }})" title="Restore User">
                                        <i class="fas fa-undo"></i> Restore
                                    </button>
                                    <button class="permanent-delete-btn" onclick="permanentlyDeleteUser({{ $user->id }})" title="Permanently Delete">
                                        <i class="fas fa-trash-alt"></i> Delete Forever
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @if($archivedUsers->count() == 0)
                        <tr class="no-results">
                            <td colspan="5" style="text-align: center; color: #666; padding: 20px;">
                                @if(request('search'))
                                    No archived users found matching your search criteria.
                                @else
                                    No archived users found.
                                @endif
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination Links -->
            <div class="pagination-wrapper">
                @include('components.custom-pagination', ['paginator' => $archivedUsers])
            </div>
        </div>
    </div>

    <script>
    // Search Functionality
    const searchInput = document.getElementById('searchInput');
    const searchButton = document.getElementById('searchButton');
    const clearButton = document.getElementById('clearButton');

    function performSearch() {
        const searchTerm = searchInput.value.trim();
        
        // Build URL with search parameters
        const url = new URL(window.location.href);
        url.searchParams.delete('page'); // Reset to page 1 when searching
        
        if (searchTerm) {
            url.searchParams.set('search', searchTerm);
        } else {
            url.searchParams.delete('search');
        }
        
        // Redirect to perform server-side search
        window.location.href = url.toString();
    }

    function clearSearch() {
        // Remove all search parameters
        const url = new URL(window.location.href);
        url.searchParams.delete('search');
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }

    // Event listeners
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
    async function restoreUser(userId) {
        adminConfirm(
            'Are you sure you want to restore this user account? The user will be able to log in again.',
            'Confirm Restore',
            () => performUserRestore(userId),
            () => console.log('User restore cancelled')
        );
        return;
    }

    async function performUserRestore(userId) {
        try {
            const response = await fetch(`/admin/users/${userId}/restore`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                adminSuccess('User restored successfully! The account is now active.');
                location.reload(); // Refresh the page to update the user list
            } else {
                adminError('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            adminError('An error occurred while restoring the user.');
        }
    }

    async function permanentlyDeleteUser(userId) {
        adminConfirm(
            'Are you absolutely sure you want to PERMANENTLY delete this user? This action CANNOT be undone and ALL user data will be lost forever!',
            'PERMANENT DELETION WARNING',
            () => performPermanentDeletion(userId),
            () => console.log('Permanent deletion cancelled')
        );
        return;
    }

    async function performPermanentDeletion(userId) {
        try {
            const response = await fetch(`/admin/users/${userId}/permanent`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                adminSuccess('User permanently deleted. This action cannot be undone.');
                location.reload(); // Refresh the page to update the user list
            } else {
                adminError('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            adminError('An error occurred while permanently deleting the user.');
        }
    }
    </script>
    @include('admin.modals.notification_system')

    <style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e9ecef;
    }

    .page-header h2 {
        margin: 0;
        color: #495057;
    }

    .back-btn {
        background: #6c757d;
        color: white;
        padding: 10px 20px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        transition: background-color 0.2s ease;
    }

    .back-btn:hover {
        background: #5a6268;
        text-decoration: none;
        color: white;
    }

    .archive-stats {
        display: flex;
        align-items: center;
    }

    .stat-text {
        color: #6c757d;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .restore-btn {
        background: #28a745;
        color: #fff;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 2px 6px 0 rgba(40, 167, 69, 0.10);
        transition: background-color 0.2s ease;
        margin-right: 8px;
    }

    .restore-btn:hover {
        background: #218838;
    }

    .permanent-delete-btn {
        background: #dc3545;
        color: #fff;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 2px 6px 0 rgba(220, 53, 69, 0.10);
        transition: background-color 0.2s ease;
    }

    .permanent-delete-btn:hover {
        background: #c82333;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
    }
    </style>
</body>
</html>