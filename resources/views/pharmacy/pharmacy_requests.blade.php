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

            @if(isset($completedRequests) && $completedRequests->count())
            <div class="pharmacy-card" style="margin-top:32px;">
                <h3 style="margin-top:0;margin-bottom:16px;display:flex;align-items:center;gap:10px;">
                    <span style="font-size:20px;">Completed / Dispensed (Recent)</span>
                    <span style="background:#eef5ef;color:#2E7D32;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;">{{ $completedRequests->count() }}</span>
                </h3>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Req ID</th>
                            <th>Patient</th>
                            <th>Medicine</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Processed At</th>
                            <th>Processed By</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($completedRequests as $c)
                        <tr>
                            <td>#{{ $c->id }}</td>
                            <td>{{ $c->patient_name }}</td>
                            <td>{{ $c->generic_name ?? $c->brand_name ?? '-' }}</td>
                            <td>{{ $c->quantity }}</td>
                            <td>₱{{ number_format($c->total_price,2) }}</td>
                            <td>{{ ucfirst(str_replace('_',' ',$c->status)) }}</td>
                            <td>
                                @if($c->dispensed_at)
                                    {{ $c->dispensed_at->format('M d, Y h:i A') }}
                                @elseif($c->completed_at)
                                    {{ $c->completed_at->format('M d, Y h:i A') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($c->dispensedBy)
                                    {{ $c->dispensedBy->name }}
                                @elseif($c->requestedBy)
                                    {{ $c->requestedBy->name }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <button class="action-btn" onclick="viewRequest({{ $c->id }})" title="View"><i class="fas fa-eye"></i></button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <p style="margin-top:12px;font-size:12px;color:#666;">Showing latest {{ $completedRequests->count() }} processed requests (dispensed or completed). Use filters above for full history.</p>
            </div>
            @endif
        </main>
    </div>

    <!-- Include notification system before scripts -->
    @include('pharmacy.notification-system')

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
        // Simple and clean dropdown toggle function
        function toggleDropdown(requestId) {
            console.log('toggleDropdown called for ID:', requestId);
            
            // Close all dropdowns first
            document.querySelectorAll('.dropdown-content').forEach(function(dd) {
                dd.classList.remove('show');
            });
            
            // Open the requested dropdown
            const dropdown = document.getElementById('dropdown-' + requestId);
            if (dropdown) {
                dropdown.classList.add('show');
                console.log('Dropdown shown for ID:', requestId);
            } else {
                console.error('Dropdown not found for ID:', requestId);
            }
        }
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.action-dropdown')) {
                document.querySelectorAll('.dropdown-content').forEach(function(dropdown) {
                    dropdown.classList.remove('show');
                });
            }
        });
        
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

        // Removed duplicate function - using simple version above

        // Essential pharmacy functions
        function dispenseRequest(id) {
            // Check if notification system is available, fallback to confirm if not
            if (typeof showPharmacyConfirm === 'function') {
                showPharmacyConfirm(
                    'Are you sure you want to dispense this medicine request? This action will reduce the pharmacy stock quantity.',
                    'Confirm Dispensing',
                    function(confirmed) {
                        if (confirmed) {
                            showPharmacyLoading('Dispensing medicine...', 'Processing Request');
                            
                            fetch('/pharmacy/requests/' + id + '/dispense', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                }
                            })
                            .then(function(response) {
                                return response.json();
                            })
                            .then(function(data) {
                                closePharmacyNotification(); // Close loading
                                
                                if (data.success) {
                                    showPharmacySuccess(
                                        data.message || 'Medicine dispensed successfully!',
                                        'Dispensed Successfully'
                                    );
                                    // Auto-reload after 2 seconds
                                    setTimeout(() => {
                                        location.reload();
                                    }, 2000);
                                } else {
                                    showPharmacyError(
                                        data.message || 'Failed to dispense medicine',
                                        'Dispensing Failed'
                                    );
                                }
                            })
                            .catch(function(error) {
                                closePharmacyNotification(); // Close loading
                                console.error('Error:', error);
                                showPharmacyError(
                                    'Network error occurred while dispensing medicine. Please try again.',
                                    'Connection Error'
                                );
                            });
                        }
                    }
                );
            } else {
                // Fallback to browser confirm if notification system not available
                console.warn('Notification system not available, using fallback');
                if (confirm('Are you sure you want to dispense this medicine request?')) {
                    fetch('/pharmacy/requests/' + id + '/dispense', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        if (data.success) {
                            alert('Success: ' + (data.message || 'Medicine dispensed successfully!'));
                            location.reload();
                        } else {
                            alert('Error: ' + (data.message || 'Failed to dispense medicine'));
                        }
                    })
                    .catch(function(error) {
                        console.error('Error:', error);
                        alert('Error: Network error occurred');
                    });
                }
            }
        }
        
        function cancelRequest(id) {
            // Check if notification system is available, fallback to confirm if not
            if (typeof showPharmacyConfirm === 'function') {
                showPharmacyConfirm(
                    'Are you sure you want to cancel this medicine request? This action cannot be undone.',
                    'Confirm Cancellation',
                    function(confirmed) {
                        if (confirmed) {
                            showPharmacyLoading('Cancelling request...', 'Processing Request');
                            
                            fetch('/pharmacy/requests/' + id + '/cancel', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                }
                            })
                            .then(function(response) {
                                return response.json();
                            })
                            .then(function(data) {
                                closePharmacyNotification(); // Close loading
                                
                                if (data.success) {
                                    showPharmacySuccess(
                                        data.message || 'Request cancelled successfully!',
                                        'Cancelled Successfully'
                                    );
                                    // Auto-reload after 2 seconds
                                    setTimeout(() => {
                                        location.reload();
                                    }, 2000);
                                } else {
                                    showPharmacyError(
                                        data.message || 'Failed to cancel request',
                                        'Cancellation Failed'
                                    );
                                }
                            })
                            .catch(function(error) {
                                closePharmacyNotification(); // Close loading
                                console.error('Error:', error);
                                showPharmacyError(
                                    'Network error occurred while cancelling the request. Please try again.',
                                    'Connection Error'
                                );
                            });
                        }
                    }
                );
            } else {
                // Fallback to browser confirm if notification system not available
                console.warn('Notification system not available, using fallback');
                if (confirm('Are you sure you want to cancel this medicine request?')) {
                    fetch('/pharmacy/requests/' + id + '/cancel', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        if (data.success) {
                            alert('Success: ' + (data.message || 'Request cancelled successfully!'));
                            location.reload();
                        } else {
                            alert('Error: ' + (data.message || 'Failed to cancel request'));
                        }
                    })
                    .catch(function(error) {
                        console.error('Error:', error);
                        alert('Error: Network error occurred');
                    });
                }
            }
        }

        function viewRequest(id) {
            // Fetch request details and show in modal
            fetch(`/pharmacy/requests/${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const request = data.request;
                        let details = `<div style="text-align: left; line-height: 1.6;">
                            <h4 style="margin: 0 0 15px 0; color: #2E7D32; border-bottom: 2px solid #4CAF50; padding-bottom: 8px;">
                                Request #${request.id} Details
                            </h4>
                            <div style="display: grid; gap: 12px;">
                                <div><strong>Patient:</strong> ${request.patient_name || 'N/A'}</div>
                                <div><strong>Medicine:</strong> ${request.generic_name || request.brand_name || 'N/A'}</div>
                                <div><strong>Item Code:</strong> ${request.item_code || 'N/A'}</div>
                                <div><strong>Quantity:</strong> ${request.quantity} units</div>
                                <div><strong>Unit Price:</strong> ₱${parseFloat(request.unit_price || 0).toFixed(2)}</div>
                                <div><strong>Total Price:</strong> ₱${parseFloat(request.total_price || 0).toFixed(2)}</div>
                                <div><strong>Status:</strong> ${request.status}</div>
                                <div><strong>Requested By:</strong> ${request.requested_by || 'N/A'}</div>
                                <div><strong>Requested Date:</strong> ${request.requested_at || 'N/A'}</div>
                            </div>
                        </div>`;
                        
                        if (typeof showPharmacyInfo === 'function') {
                            showPharmacyInfo(details, 'Request Details');
                        } else {
                            alert('Request Details\n\n' + details.replace(/<[^>]*>/g, ''));
                        }
                    } else {
                        alert('Failed to load request details');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading request details');
                });
        }
        
        console.log('Pharmacy page loaded successfully');
        
        // Debug: Check if notification functions are available
        console.log('showPharmacyConfirm available:', typeof showPharmacyConfirm);
        console.log('showPharmacySuccess available:', typeof showPharmacySuccess);
        console.log('showPharmacyError available:', typeof showPharmacyError);
        console.log('PharmacyNotificationSystem available:', typeof PharmacyNotificationSystem);
        
        // Test notification system
        window.testNotification = function() {
            if (typeof showPharmacyConfirm === 'function') {
                showPharmacyConfirm('Test message', 'Test Title', function(result) {
                    console.log('Test result:', result);
                });
            } else {
                console.error('showPharmacyConfirm not available');
            }
        };
        
        console.log('You can test notifications by running: testNotification()');
        
    </script>
</body>
</html>