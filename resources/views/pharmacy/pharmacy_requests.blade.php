<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy - Nurse Requests</title>
    <link rel="stylesheet" href="{{ asset('css/pharmacycss/pharmacy.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    @include('pharmacy.pharmacy_header')
    <div class="pharmacy-layout">
        @include('pharmacy.pharmacy_sidebar')
        <main class="main-content">
            <div class="page-header">
                <h2>Nurse Medicine Requests</h2>
            </div>

            <div class="pharmacy-card">
                @if($requests->count() > 0)
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th onclick="sortTable('id')" class="sortable {{ $sort === 'id' ? 'sorted-' . $direction : '' }}">
                                    Request ID
                                    <i class="fas fa-sort {{ $sort === 'id' ? ($direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down') : '' }}"></i>
                                </th>
                                <th onclick="sortTable('patient_name')" class="sortable {{ $sort === 'patient_name' ? 'sorted-' . $direction : '' }}">
                                    Patient
                                    <i class="fas fa-sort {{ $sort === 'patient_name' ? ($direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down') : '' }}"></i>
                                </th>
                                <th>Item Code</th>
                                <th onclick="sortTable('generic_name')" class="sortable {{ $sort === 'generic_name' ? 'sorted-' . $direction : '' }}">
                                    Medicine
                                    <i class="fas fa-sort {{ $sort === 'generic_name' ? ($direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down') : '' }}"></i>
                                </th>
                                <th onclick="sortTable('quantity')" class="sortable {{ $sort === 'quantity' ? 'sorted-' . $direction : '' }}">
                                    Qty
                                    <i class="fas fa-sort {{ $sort === 'quantity' ? ($direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down') : '' }}"></i>
                                </th>
                                <th>Unit Price</th>
                                <th>Total Amount</th>
                                <th>Requested By</th>
                                <th onclick="sortTable('requested_at')" class="sortable {{ $sort === 'requested_at' ? 'sorted-' . $direction : '' }}">
                                    Requested At
                                    <i class="fas fa-sort {{ $sort === 'requested_at' ? ($direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down') : '' }}"></i>
                                </th>
                                <th onclick="sortTable('status')" class="sortable {{ $sort === 'status' ? 'sorted-' . $direction : '' }}">
                                    Status
                                    <i class="fas fa-sort {{ $sort === 'status' ? ($direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down') : '' }}"></i>
                                </th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $request)
                            <tr>
                                <td>#{{ $request->id }}</td>
                                <td>{{ $request->patient_name ?? '-' }}</td>
                                <td>{{ $request->item_code ?? '-' }}</td>
                                <td>{{ $request->generic_name ?? $request->brand_name ?? '-' }}</td>
                                <td>{{ $request->quantity }}</td>
                                <td>₱{{ number_format($request->unit_price, 2) }}</td>
                                <td><strong>₱{{ number_format($request->total_price, 2) }}</strong></td>
                                <td>{{ optional($request->requestedBy)->name }}</td>
                                <td>{{ $request->requested_at ? $request->requested_at->format('M d, Y h:i A') : '-' }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $request->status)) }}</td>
                                <td>
                                    <div class="action-dropdown" id="action-dropdown-container-{{ $request->id }}">
                                        <button class="action-btn" onclick="toggleDropdown({{ $request->id }})">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-content" id="dropdown-{{ $request->id }}">
                                            @if($request->status === 'pending')
                                                <a href="#" onclick="dispenseRequest({{ $request->id }})" class="dispense-action">
                                                    <i class="fas fa-pills"></i> Dispense
                                                </a>
                                                <a href="#" onclick="cancelRequest({{ $request->id }})" class="cancel-action">
                                                    <i class="fas fa-times"></i> Cancel
                                                </a>
                                            @else
                                                <a href="#" onclick="viewRequest({{ $request->id }})">
                                                    <i class="fas fa-eye"></i> View Details
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="no-orders">
                        <h3>No Nurse Requests</h3>
                        <p>No medicine requests submitted by nurses at this time.</p>
                    </div>
                @endif
            </div>

            <!-- Pagination outside the card -->
            @if($requests->count() > 0)
                <div class="custom-pagination">
                    <x-custom-pagination :paginator="$requests" />
                </div>
            @endif
        </main>
    </div>

    @include('pharmacy.modals.notification_system')

    <style>
        /* Action Dropdown Styles */
        .action-dropdown { 
            position: relative; 
        }

        .action-btn { 
            background: transparent; 
            border: none; 
            padding: 8px; 
            cursor: pointer; 
            border-radius: 4px;
            transition: background 0.2s ease;
            color: #0066cc;
        }

        .action-btn:hover {
            background: #f0f8ff;
        }

        .dropdown-content {
            position: absolute; 
            right: 0; 
            top: 100%; 
            background: white; 
            min-width: 160px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.15); 
            border-radius: 8px; 
            z-index: 1000; 
            display: none;
            border: 1px solid #dee2e6;
        }

        .dropdown-content[style*="bottom:"] {
            box-shadow: 0 -8px 16px rgba(0,0,0,0.15);
        }

        .dropdown-content.show { 
            display: block; 
            animation: slideDown 0.2s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .dropdown-content a { 
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 15px; 
            text-decoration: none; 
            color: #333;
            font-size: 14px;
            transition: background 0.2s ease;
        }

        .dropdown-content a:first-child {
            border-radius: 8px 8px 0 0;
        }

        .dropdown-content a:last-child {
            border-radius: 0 0 8px 8px;
        }

        .dropdown-content a:hover { 
            background: #f8f9fa; 
        }

        .dispense-action { color: #28a745 !important; }
        .cancel-action { color: #dc3545 !important; }

        /* Sorting styles */
        .sortable {
            cursor: pointer;
            user-select: none;
            position: relative;
            transition: background-color 0.2s;
        }

        .sortable:hover {
            background-color: #f8f9fa;
        }

        .sortable i {
            margin-left: 8px;
            opacity: 0.5;
            font-size: 12px;
        }

        .sortable.sorted-asc i,
        .sortable.sorted-desc i {
            opacity: 1;
            color: #007bff;
        }

        .pagination-wrapper {
            margin-top: 20px;
            display: flex;
            justify-content: center;
        }
    </style>

    <script>
        // Sorting and pagination functions
        function sortTable(column) {
            const currentSort = '{{ $sort }}';
            const currentDirection = '{{ $direction }}';
            let newDirection = 'asc';
            
            if (currentSort === column && currentDirection === 'asc') {
                newDirection = 'desc';
            }
            
            const url = new URL(window.location);
            url.searchParams.set('sort', column);
            url.searchParams.set('direction', newDirection);
            window.location.href = url.toString();
        }

        function toggleDropdown(requestId) {
            const dropdown = document.getElementById(`dropdown-${requestId}`);
            
            // Close all other dropdowns first
            document.querySelectorAll('.dropdown-content.show').forEach(dd => {
                if (dd.id !== `dropdown-${requestId}`) {
                    dd.classList.remove('show');
                    dd.style.position = '';
                    dd.style.top = '';
                    dd.style.left = '';
                    dd.style.right = '';
                    dd.style.bottom = '';
                }
            });

            // Toggle the current dropdown
            if (dropdown.classList.contains('show')) {
                dropdown.classList.remove('show');
                dropdown.style.position = '';
                dropdown.style.top = '';
                dropdown.style.left = '';
                dropdown.style.right = '';
                dropdown.style.bottom = '';
            } else {
                const button = dropdown.previousElementSibling;
                const rect = button.getBoundingClientRect();
                const dropdownRect = dropdown.getBoundingClientRect();
                const viewportHeight = window.innerHeight;
                const viewportWidth = window.innerWidth;

                // Check if dropdown would overflow at the bottom
                if (rect.bottom + 160 > viewportHeight) {
                    dropdown.style.position = 'fixed';
                    dropdown.style.bottom = (viewportHeight - rect.top) + 'px';
                    dropdown.style.top = 'auto';
                } else {
                    dropdown.style.position = 'fixed';
                    dropdown.style.top = rect.bottom + 'px';
                    dropdown.style.bottom = 'auto';
                }

                // Check if dropdown would overflow on the right
                if (rect.right - 160 < 0) {
                    dropdown.style.left = rect.left + 'px';
                    dropdown.style.right = 'auto';
                } else {
                    dropdown.style.right = (viewportWidth - rect.right) + 'px';
                    dropdown.style.left = 'auto';
                }

                dropdown.classList.add('show');
            }
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.matches('.action-btn') && !event.target.matches('.fas')) {
                document.querySelectorAll('.dropdown-content.show').forEach(dropdown => {
                    dropdown.classList.remove('show');
                    dropdown.style.position = '';
                    dropdown.style.top = '';
                    dropdown.style.left = '';
                    dropdown.style.right = '';
                    dropdown.style.bottom = '';
                });
            }
        });

        function viewRequest(id){
            fetch(`/pharmacy/requests/${id}`)
                .then(r=>r.json())
                .then(j=>{
                    if(j.success){
                        const o = j.request;
                        
                        // Format the request details nicely
                        let details = `<div style="text-align: left; line-height: 1.6;">
                            <h4 style="margin: 0 0 15px 0; color: #2E7D32; border-bottom: 2px solid #4CAF50; padding-bottom: 8px;">
                                Request #${o.id} Details
                            </h4>
                            
                            <div style="display: grid; gap: 12px;">
                                <div><strong>Patient:</strong> ${o.patient_name}${o.patient_no ? ' (ID: ' + o.patient_no + ')' : ''}</div>
                                <div><strong>Medicine:</strong> ${o.medicine_name}</div>
                                ${o.item_code ? '<div><strong>Item Code:</strong> ' + o.item_code + '</div>' : ''}
                                <div><strong>Quantity:</strong> ${o.quantity} units</div>
                                ${o.unit_price ? '<div><strong>Unit Price:</strong> ₱' + parseFloat(o.unit_price).toFixed(2) + '</div>' : ''}
                                ${o.total_price ? '<div><strong>Total Price:</strong> ₱' + parseFloat(o.total_price).toFixed(2) + '</div>' : ''}
                                <div><strong>Status:</strong> <span style="color: ${getStatusColor(o.status.toLowerCase())}; font-weight: bold;">${o.status}</span></div>
                                <div><strong>Priority:</strong> ${o.priority}</div>
                                <div><strong>Requested By:</strong> ${o.requested_by}</div>
                                <div><strong>Requested Date:</strong> ${o.requested_at || 'Not specified'}</div>
                                ${o.pharmacist ? '<div><strong>Assigned Pharmacist:</strong> ' + o.pharmacist + '</div>' : ''}
                                ${o.dispensed_by ? '<div><strong>Dispensed By:</strong> ' + o.dispensed_by + '</div>' : ''}
                                ${o.dispensed_at ? '<div><strong>Dispensed Date:</strong> ' + o.dispensed_at + '</div>' : ''}
                                <div style="margin-top: 8px;"><strong>Notes:</strong><br><em style="color: #666;">${o.notes}</em></div>
                            </div>
                        </div>`;
                        
                        // Use notification system directly without fallback to alert
                        showPharmacyInfo(details, 'Request Details');
                    } else {
                        showPharmacyError('Failed to load request details: ' + (j.message || 'Unknown error'), 'Error');
                    }
                }).catch(e=>{ 
                    console.error(e); 
                    showPharmacyError('Network error while loading request details', 'Connection Error');
                });
        }

        // Helper function to get status colors
        function getStatusColor(status) {
            switch(status) {
                case 'pending': return '#FF9800';
                case 'in_progress': return '#2196F3';
                case 'completed': return '#4CAF50';
                case 'dispensed': return '#4CAF50';
                case 'cancelled': return '#F44336';
                default: return '#666';
            }
        }

        function dispenseRequest(id) {
            // Check if notification system is available
            if (typeof showPharmacyConfirm === 'function') {
                showPharmacyConfirm(
                    'Are you sure you want to dispense this medicine request? This action will reduce the pharmacy stock quantity.',
                    'Confirm Dispensing',
                    function(confirmed) {
                        if (confirmed) {
                            showPharmacyLoading('Dispensing medicine...', 'Processing Request');
                        
                        fetch(`/pharmacy/requests/${id}/dispense`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                        .then(response => {
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                showPharmacySuccess(
                                    'Medicine dispensed successfully!',
                                    'Dispensed'
                                );
                                // Auto-reload after 2 seconds
                                setTimeout(() => {
                                    location.reload();
                                }, 2000);
                            } else {
                                showPharmacyError(data.message || 'Failed to dispense medicine', 'Dispensing Failed');
                            }
                        })
                        .catch(error => {
                            console.error('Fetch error:', error);
                            showPharmacyError('A network error occurred while dispensing medicine', 'Connection Error');
                        });
                    }
                }
            );
            } else {
                // Force use of notification system even when initially not detected
                PharmacyNotificationSystem.show(
                    'Are you sure you want to dispense this medicine request? This action will reduce the pharmacy stock quantity.',
                    'confirm',
                    'Confirm Dispensing',
                    function(confirmed) {
                        if (confirmed) {
                            PharmacyNotificationSystem.show('Dispensing medicine...', 'loading', 'Processing Request');
                            
                            fetch(`/pharmacy/requests/${id}/dispense`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    PharmacyNotificationSystem.show(
                                        'Medicine dispensed successfully!',
                                        'success',
                                        'Dispensed'
                                    );
                                    // Auto-reload after 2 seconds
                                    setTimeout(() => {
                                        location.reload();
                                    }, 2000);
                                } else {
                                    PharmacyNotificationSystem.show(
                                        data.message || 'Failed to dispense medicine',
                                        'error',
                                        'Dispensing Failed'
                                    );
                                }
                            })
                            .catch(error => {
                                console.error('Fetch error:', error);
                                PharmacyNotificationSystem.show(
                                    'Network error occurred while dispensing medicine',
                                    'error',
                                    'Connection Error'
                                );
                            });
                        }
                    }
                );
            }
        }

        function cancelRequest(id) {
            // Check if notification system is available
            if (typeof showPharmacyConfirm === 'function') {
                showPharmacyConfirm(
                    'Are you sure you want to cancel this medicine request? This action cannot be undone.',
                    'Confirm Cancellation',
                    function(confirmed) {
                        if (confirmed) {
                            showPharmacyLoading('Cancelling request...', 'Processing Request');
                            
                            fetch(`/pharmacy/requests/${id}/cancel`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    showPharmacySuccess(
                                        'Request cancelled successfully!',
                                        'Cancelled'
                                    );
                                    // Auto-reload after 2 seconds
                                    setTimeout(() => {
                                        location.reload();
                                    }, 2000);
                                } else {
                                    showPharmacyError(data.message || 'Failed to cancel request', 'Cancellation Failed');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                showPharmacyError('A network error occurred while cancelling the request', 'Connection Error');
                            });
                        }
                    }
                );
            } else {
                // Force use of notification system even when initially not detected
                PharmacyNotificationSystem.show(
                    'Are you sure you want to cancel this medicine request? This action cannot be undone.',
                    'confirm',
                    'Confirm Cancellation',
                    function(confirmed) {
                        if (confirmed) {
                            PharmacyNotificationSystem.show('Cancelling request...', 'loading', 'Processing Request');
                            
                            fetch(`/pharmacy/requests/${id}/cancel`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    PharmacyNotificationSystem.show(
                                        'Request cancelled successfully!',
                                        'success',
                                        'Cancelled'
                                    );
                                    // Auto-reload after 2 seconds
                                    setTimeout(() => {
                                        location.reload();
                                    }, 2000);
                                } else {
                                    PharmacyNotificationSystem.show(
                                        data.message || 'Failed to cancel request',
                                        'error',
                                        'Cancellation Failed'
                                    );
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                PharmacyNotificationSystem.show(
                                    'Network error occurred while cancelling the request',
                                    'error',
                                    'Connection Error'
                                );
                            });
                        }
                    }
                );
            }
        }
    </script>
</body>
</html>