<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Orders</title>
    <link rel="stylesheet" href="{{ url('css/pharmacy.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    @php
        $pharmacyName = auth()->user()->name ?? 'Pharmacy Staff';
        // Placeholder for orders - replace with actual pharmacy orders when model is created
        $orders = collect();
    @endphp
    @include('pharmacy.pharmacy_header')

    <div class="pharmacy-layout">
        @include('pharmacy.pharmacy_sidebar')

        <main class="main-content">
            <h2>Pharmacy Orders</h2>
            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <button class="tab-btn active" data-status="all">All Orders</button>
                <button class="tab-btn" data-status="pending">Pending</button>
                <button class="tab-btn" data-status="in_progress">In Progress</button>
                <button class="tab-btn" data-status="completed">Completed</button>
                <button class="tab-btn" data-status="cancelled">Cancelled</button>
            </div>

            <div class="pharmacy-card">
                @if($orders->count() > 0)
                    <div class="orders-table-container">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th class="sortable" data-sort="order-id">
                                        Order ID <span class="sort-icon"><i class="fas fa-sort"></i></span>
                                    </th>
                                    <th>Patient</th>
                                    <th>Prescription Details</th>
                                    <th class="sortable" data-sort="requester">
                                        Requested By <span class="sort-icon"><i class="fas fa-sort"></i></span>
                                    </th>
                                    <th class="sortable" data-sort="priority">
                                        Priority <span class="sort-icon"><i class="fas fa-sort"></i></span>
                                    </th>
                                    <th class="sortable" data-sort="status">
                                        Status <span class="sort-icon"><i class="fas fa-sort"></i></span>
                                    </th>
                                    <th class="sortable" data-sort="requested-at">
                                        Requested At <span class="sort-icon"><i class="fas fa-sort"></i></span>
                                    </th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                <tr class="order-row" data-status="{{ $order->status }}">
                                    <td class="order-id" data-value="{{ $order->id }}">#{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</td>
                                    <td class="patient-info">
                                        <strong>{{ $order->patient_name }}</strong><br>
                                        <small>ID: {{ $order->patient_no }}</small>
                                    </td>
                                    <td class="prescription-info">
                                        <div class="prescription-details">{{ $order->prescription_details }}</div>
                                        @if($order->notes)
                                            <small class="notes">Notes: {{ $order->notes }}</small>
                                        @endif
                                    </td>
                                    <td class="requester" data-value="{{ $order->requestedBy->name }}">{{ $order->requestedBy->name }}</td>
                                    <td class="priority" data-value="{{ $order->priority }}">
                                        <span class="priority-badge priority-{{ $order->priority }}">
                                            {{ ucfirst($order->priority) }}
                                        </span>
                                    </td>
                                    <td class="status" data-value="{{ $order->status }}">
                                        <span class="status-badge status-{{ $order->status }}">
                                            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                        </span>
                                    </td>
                                    <td class="requested-at" data-value="{{ $order->requested_at->timestamp }}">
                                        {{ $order->requested_at->format('M d, Y') }}<br>
                                        <small>{{ $order->requested_at->format('h:i A') }}</small>
                                    </td>
                                    <td class="actions">
                                        @if($order->status === 'pending')
                                            <button class="btn start-btn" onclick="updateStatus({{ $order->id }}, 'in_progress')">
                                                Start
                                            </button>
                                        @elseif($order->status === 'in_progress')
                                            <button class="btn complete-btn" onclick="completeOrder({{ $order->id }})">
                                                Complete
                                            </button>
                                            <button class="btn cancel-btn" onclick="cancelOrder({{ $order->id }})">
                                                Cancel
                                            </button>
                                        @endif
                                        
                                        <button class="btn view-btn" onclick="viewOrder({{ $order->id }})">
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Empty state placeholders for filtered tabs -->
                    <div id="empty-pending" class="empty-state-placeholder" style="display: none;">
                        <i class="fas fa-clock"></i>
                        <h3>No Pending Orders</h3>
                        <p>There are currently no pending pharmacy orders to display.</p>
                    </div>
                    
                    <div id="empty-in-progress" class="empty-state-placeholder" style="display: none;">
                        <i class="fas fa-spinner"></i>
                        <h3>No In Progress Orders</h3>
                        <p>There are currently no pharmacy orders in progress.</p>
                    </div>
                    
                    <div id="empty-completed" class="empty-state-placeholder" style="display: none;">
                        <i class="fas fa-check-circle"></i>
                        <h3>No Completed Orders</h3>
                        <p>There are currently no completed pharmacy orders to display.</p>
                    </div>
                    
                    <div id="empty-cancelled" class="empty-state-placeholder" style="display: none;">
                        <i class="fas fa-ban"></i>
                        <h3>No Cancelled Orders</h3>
                        <p>There are currently no cancelled pharmacy orders to display.</p>
                    </div>
                @else
                    <div class="no-orders">
                        <i class="fas fa-pills"></i>
                        <h3>No Pharmacy Orders</h3>
                        <p>No pharmacy orders have been requested yet.</p>
                    </div>
                @endif
            </div>
        </main>
    </div>

    <script>
        let currentOrderId = null;
        let currentSort = { column: null, direction: 'asc' };
        let currentStatus = 'all';

        // Initialize on document ready
        document.addEventListener('DOMContentLoaded', function() {
            // Set up filter tabs
            const tabButtons = document.querySelectorAll('.tab-btn');
            tabButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    filterByStatus(this.dataset.status);
                });
            });
            
            // Set up sortable columns
            setupSortableColumns();
            
            // Initial filter
            filterByStatus('all');
        });

        function filterByStatus(status) {
            currentStatus = status;
            
            // Update active tab
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector(`[data-status="${status}"]`).classList.add('active');
            
            // Show/hide orders
            const rows = document.querySelectorAll('.order-row');
            let visibleCount = 0;
            
            rows.forEach(row => {
                if (status === 'all' || row.dataset.status === status) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Show/hide empty states
            document.querySelectorAll('.empty-state-placeholder').forEach(placeholder => {
                placeholder.style.display = 'none';
            });
            
            if (visibleCount === 0 && status !== 'all') {
                const emptyElement = document.getElementById(`empty-${status.replace('_', '-')}`);
                if (emptyElement) {
                    emptyElement.style.display = 'block';
                }
            }
            
            // Show/hide table and pagination
            const tableContainer = document.querySelector('.orders-table-container');
            if (tableContainer) {
                tableContainer.style.display = visibleCount > 0 ? 'block' : 'none';
            }
        }

        function setupSortableColumns() {
            const sortableHeaders = document.querySelectorAll('.sortable');
            sortableHeaders.forEach(header => {
                header.addEventListener('click', function() {
                    const column = this.dataset.sort;
                    let direction = 'asc';
                    
                    if (currentSort.column === column && currentSort.direction === 'asc') {
                        direction = 'desc';
                    }
                    
                    sortTable(column, direction);
                });
            });
        }

        function sortTable(column, direction) {
            currentSort = { column, direction };
            
            // Update sort icons
            document.querySelectorAll('.sort-icon i').forEach(icon => {
                icon.className = 'fas fa-sort';
            });
            
            const activeHeader = document.querySelector(`[data-sort="${column}"] .sort-icon i`);
            if (activeHeader) {
                activeHeader.className = direction === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
            }
            
            // Sort table rows
            const tbody = document.querySelector('.orders-table tbody');
            const rows = Array.from(tbody.querySelectorAll('.order-row'));
            
            rows.sort((a, b) => {
                let aVal, bVal;
                
                if (column === 'order-id') {
                    aVal = parseInt(a.querySelector('.order-id').dataset.value);
                    bVal = parseInt(b.querySelector('.order-id').dataset.value);
                } else if (column === 'requested-at') {
                    aVal = parseInt(a.querySelector('.requested-at').dataset.value);
                    bVal = parseInt(b.querySelector('.requested-at').dataset.value);
                } else {
                    const aCell = a.querySelector(`.${column}`);
                    const bCell = b.querySelector(`.${column}`);
                    aVal = aCell?.dataset.value || aCell?.textContent || '';
                    bVal = bCell?.dataset.value || bCell?.textContent || '';
                }
                
                if (typeof aVal === 'string') {
                    aVal = aVal.toLowerCase();
                    bVal = bVal.toLowerCase();
                }
                
                if (direction === 'asc') {
                    return aVal > bVal ? 1 : -1;
                } else {
                    return aVal < bVal ? 1 : -1;
                }
            });
            
            // Re-append sorted rows
            rows.forEach(row => tbody.appendChild(row));
            
            // Re-apply current filter
            filterByStatus(currentStatus);
        }

        // Placeholder functions - implement based on your pharmacy order model
        function updateStatus(orderId, status) {
            console.log(`Update order ${orderId} to status: ${status}`);
            // Implement actual status update logic
        }

        function completeOrder(orderId) {
            console.log(`Complete order: ${orderId}`);
            // Implement order completion logic
        }

        function cancelOrder(orderId) {
            console.log(`Cancel order: ${orderId}`);
            // Implement order cancellation logic
        }

        function viewOrder(orderId) {
            console.log(`View order details: ${orderId}`);
            // Implement order details view
        }
    </script>
</body>
</html>
