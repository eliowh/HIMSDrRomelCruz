<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Room Management</title>
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
            <h2>Room Management</h2>
            
            <!-- Controls Row -->
            <div class="controls-row">
                <div class="filter-search-controls">
                    <input type="text" id="searchInput" placeholder="Search by room name or price..." class="search-input" value="{{ request('q') }}">
                    <button id="searchButton" class="search-btn">Search</button>
                    @if(request('q'))
                        <button id="clearButton" class="clear-btn">Clear</button>
                    @endif
                </div>
                <button class="add-user-btn" onclick="openAddRoomModal()">Add New Room</button>
            </div>

            <div class="admin-card">
                <!-- Rooms Table -->
                <table class="admin-table" id="roomsTable">
                    <thead>
                        <tr>
                            <th class="sortable" data-sort="COL 1">
                                Room Name
                                @if(request('sort') == 'COL 1' || !request('sort'))
                                    <span class="sort-indicator {{ request('direction') == 'desc' ? 'desc' : 'asc' }}">
                                        {{ request('direction') == 'desc' ? '↓' : '↑' }}
                                    </span>
                                @else
                                    <span class="sort-indicator">↕</span>
                                @endif
                            </th>
                            <th class="sortable" data-sort="COL 2">
                                Price
                                @if(request('sort') == 'COL 2')
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
                        @foreach($rooms as $room)
                        @php
                            $roomName = $room->{'COL 1'} ?? 'N/A';
                            $price = $room->{'COL 2'} ?? '0';
                            $cleanPrice = (float)str_replace(',', '', $price);
                            $rowId = 'roomrow-' . $loop->index;
                        @endphp
                        <tr id="{{ $rowId }}" data-room-name="{{ $roomName }}" data-price="{{ $price }}">
                            <td>{{ $roomName }}</td>
                            <td>₱{{ number_format($cleanPrice, 2) }}</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="edit-btn" onclick="editRoomByRow('{{ $rowId }}')" title="Edit Room">
                                        Edit
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @if($rooms->count() == 0)
                        <tr class="no-results">
                            <td colspan="3" style="text-align: center; color: #666; padding: 20px;">
                                @if(request('q'))
                                    No rooms found matching your search criteria.
                                @else
                                    No rooms found.
                                @endif
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination Links -->
            <div class="pagination-wrapper">
                @if(isset($rooms) && method_exists($rooms, 'hasPages') && $rooms->hasPages())
                    @include('components.custom-pagination', ['paginator' => $rooms])
                @endif
            </div>
        </div>
    </div>

    @include('admin.modals.admin_createRoom')
    @include('admin.modals.admin_editRoom')

    <script>
    function openAddRoomModal() {
        const modal = document.getElementById('addRoomModal');
        if (modal) {
            modal.style.display = 'flex';
        } else {
            adminError('Add room modal not found. Please refresh the page.');
        }
    }
    function closeAddRoomModal() {
        const modal = document.getElementById('addRoomModal');
        if (modal) {
            modal.style.display = 'none';
            // Reset form if the function exists
            if (typeof resetCreateRoomForm === 'function') {
                resetCreateRoomForm();
            }
        }
    }

    function openEditRoomModal() {
        const modal = document.getElementById('editRoomModal');
        if (modal) {
            modal.style.display = 'flex';
            return;
        }
        // If modal missing, try refreshing the rooms list and retry once
        console.warn('Edit modal not found, attempting to refresh DOM and retry.');
        refreshRoomsList().then(() => {
            const m = document.getElementById('editRoomModal');
            if (m) m.style.display = 'flex';
            else {
                console.error('Edit modal still missing after refresh; reloading page.');
                adminError('Edit modal is missing. The page will be reloaded to fix this issue.');
                setTimeout(() => location.reload(), 2000);
            }
        });
    }

    function closeEditRoomModal() {
        const modal = document.getElementById('editRoomModal');
        const form = document.getElementById('editRoomForm');
        
        if (modal) {
            modal.style.display = 'none';
        }
        if (form) {
            form.reset();
        }
        clearEditErrors();
        // Refresh the rooms table portion to ensure DOM is up-to-date and avoid stale ids
        refreshRoomsList();
    }

    function clearEditErrors() {
        const modal = document.getElementById('editRoomModal');
        if (modal) {
            modal.querySelectorAll('.error-text').forEach(error => {
                error.textContent = '';
            });
            modal.querySelectorAll('.form-input').forEach(input => {
                input.classList.remove('error');
            });
        }
        const errorMsg = document.getElementById('editErrorMessage');
        if (errorMsg) {
            errorMsg.style.display = 'none';
        }
    }

    // Filter and Search Functionality - Server-side search for cross-page results
    const searchInput = document.getElementById('searchInput');
    const searchButton = document.getElementById('searchButton');
    const clearButton = document.getElementById('clearButton');

    function performSearch() {
        const searchTerm = searchInput.value.trim();
        
        // Build URL with search parameters
        const url = new URL(window.location.href);
        url.searchParams.delete('page'); // Reset to page 1 when searching
        
        if (searchTerm) {
            url.searchParams.set('q', searchTerm);
        } else {
            url.searchParams.delete('q');
        }
        
        // Redirect to perform server-side search
        window.location.href = url.toString();
    }

    function clearSearch() {
        // Remove all search parameters
        const url = new URL(window.location.href);
        url.searchParams.delete('q');
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

    // Room Actions
    function editRoomByRow(rowId) {
        const row = document.getElementById(rowId);
        if (!row) {
            alert('Unable to find selected room.');
            return;
        }
        const roomName = row.getAttribute('data-room-name');
        if (!roomName) {
            adminError('Unable to determine room name.');
            return;
        }
        editRoom(String(roomName).trim(), rowId);
    }

    async function editRoom(roomName, rowId) {
            const dd = document.getElementById(`dropdown-${rowId}`);
            if (dd) dd.classList.remove('show');
        
        // Try fetch with one retry for transient errors
        try {
            let response;
            try {
                response = await fetch(`/admin/rooms/edit`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ room_name: roomName })
                });
            } catch (networkErr) {
                // network error - retry once after short delay
                console.warn('Network error, retrying...', networkErr);
                await new Promise(r => setTimeout(r, 400));
                response = await fetch(`/admin/rooms/edit`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ room_name: roomName })
                });
            }

            // If server returned non-200, try to show its message for debugging
            if (!response.ok) {
                const text = await response.text();
                console.error('Server error response:', text);
                // try one more time after short delay in case of transient server hiccup
                await new Promise(r => setTimeout(r, 400));
                const retryResponse = await fetch(`/admin/rooms/edit`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ room_name: roomName })
                });
                if (!retryResponse.ok) {
                    const retryText = await retryResponse.text();
                    console.error('Retry server error response:', retryText);
                    adminError('Error loading room data: ' + (retryText || retryResponse.statusText || text));
                    return;
                }
                response = retryResponse;
            }

            let result;
            try {
                result = await response.json();
            } catch (parseErr) {
                const text = await response.text();
                console.error('JSON parse error, server returned:', text);
                adminError('Error parsing server response while loading room data.');
                return;
            }

            if (result && result.success) {
                // Ensure modal elements exist; if not, refresh DOM and retry once
                let editIdEl = document.getElementById('editRoomId');
                if (!editIdEl) {
                    console.warn('Modal inputs missing, refreshing DOM and retrying...');
                    await refreshRoomsList();
                    await new Promise(r => setTimeout(r, 200));
                    editIdEl = document.getElementById('editRoomId');
                    if (!editIdEl) {
                        adminError('Edit modal is missing. The page will be reloaded to fix this issue.');
                        setTimeout(() => location.reload(), 2000);
                        return;
                    }
                }

                editIdEl.value = roomName; // Use room name as identifier
                const editNameEl = document.getElementById('editRoomName');
                if (editNameEl) editNameEl.value = result.room['COL 1'] || '';
                // Format price for display - set as numeric value with two decimals
                const price = result.room['COL 2'] ?? '';
                const numericPrice = parseFloat(String(price).replace(/,/g, ''));
                const priceField = document.getElementById('editRoomPrice');
                if (priceField) {
                    if (!isNaN(numericPrice)) {
                        // set raw numeric string with two decimals for number input
                        priceField.value = numericPrice.toFixed(2);
                    } else {
                        // fallback to whatever value came from server
                        priceField.value = price;
                    }
                }
                
                clearEditErrors();
                openEditRoomModal();
            } else {
                adminError('Error loading room data: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            // Surface the error message to the user for easier debugging
            const msg = error && error.message ? error.message : String(error);
            adminError('An error occurred while loading room data: ' + msg);
        }
    }

    async function toggleRoomStatus(roomId, newStatus) {
    const ddElem = document.getElementById(`dropdown-${roomId}`);
    if (ddElem) ddElem.classList.remove('show');
        
        const action = newStatus === 'active' ? 'activate' : 'deactivate';
        adminConfirm(
            `Are you sure you want to ${action} this room?`,
            'Confirm Action',
            () => performRoomStatusUpdate(roomId, newStatus, action),
            () => console.log('Room status update cancelled')
        );
        return;
    }

    async function performRoomStatusUpdate(roomId, newStatus, action) {
        
        try {
            const response = await fetch(`/admin/rooms/${roomId}/toggle-status`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ status: newStatus })
            });
            
            const result = await response.json();
            
            if (result.success) {
                adminSuccess(`Room ${action}d successfully!`);
                refreshRoomsList();
            } else {
                adminError('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            adminError('An error occurred while updating the room status.');
        }
    }

    // Fetch the current page and replace the rooms table tbody to keep DOM fresh
    async function refreshRoomsList() {
        try {
            const response = await fetch(window.location.href, { method: 'GET' });
            if (!response.ok) {
                console.warn('Failed to refresh rooms list:', response.statusText);
                return;
            }
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newTbody = doc.querySelector('#roomsTable tbody');
            const oldTbody = document.querySelector('#roomsTable tbody');
            if (newTbody && oldTbody) {
                oldTbody.replaceWith(newTbody);
            }
        } catch (err) {
            console.warn('Error refreshing rooms list:', err);
        }
    }

    // Sorting functionality
    document.querySelectorAll('.sortable').forEach(header => {
        header.addEventListener('click', function() {
            const sortBy = this.getAttribute('data-sort');
            const url = new URL(window.location.href);
            
            // Get current sort parameters
            const currentSort = url.searchParams.get('sort');
            const currentDirection = url.searchParams.get('direction') || 'asc';
            
            // Determine new direction
            let newDirection = 'asc';
            if (currentSort === sortBy && currentDirection === 'asc') {
                newDirection = 'desc';
            }
            
            // Set new sort parameters
            url.searchParams.set('sort', sortBy);
            url.searchParams.set('direction', newDirection);
            url.searchParams.delete('page'); // Reset to page 1 when sorting
            
            // Navigate to new URL
            window.location.href = url.toString();
        });
    });
    </script>
    @include('admin.modals.notification_system')
</body>
</html>