<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Inventory Orders</title>
    <link rel="stylesheet" href="{{ url('css/inventory.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    @include('Inventory.inventory_header')
    <div class="inventory-layout">
        @include('Inventory.inventory_sidebar')
        <main class="main-content">
            <div class="inventory-card">
                <div class="card-header">
                    <h2>Pharmacy Orders</h2>
                    
                    <!-- Status Filter Tabs -->
                    <div class="status-tabs">
                        <a href="{{ route('inventory.orders', ['status' => 'all']) }}" 
                           class="status-tab {{ $status === 'all' ? 'active' : '' }}">
                            All <span class="count">({{ $statusCounts['all'] }})</span>
                        </a>
                        <a href="{{ route('inventory.orders', ['status' => 'pending']) }}" 
                           class="status-tab {{ $status === 'pending' ? 'active' : '' }}">
                            Pending <span class="count">({{ $statusCounts['pending'] }})</span>
                        </a>
                        <a href="{{ route('inventory.orders', ['status' => 'approved']) }}" 
                           class="status-tab {{ $status === 'approved' ? 'active' : '' }}">
                            Approved <span class="count">({{ $statusCounts['approved'] }})</span>
                        </a>
                        <a href="{{ route('inventory.orders', ['status' => 'completed']) }}" 
                           class="status-tab {{ $status === 'completed' ? 'active' : '' }}">
                            Completed <span class="count">({{ $statusCounts['completed'] }})</span>
                        </a>
                        <a href="{{ route('inventory.orders', ['status' => 'cancelled']) }}" 
                           class="status-tab {{ $status === 'cancelled' ? 'active' : '' }}">
                            Cancelled <span class="count">({{ $statusCounts['cancelled'] }})</span>
                        </a>
                    </div>
                </div>

                @if($orders->count() > 0)
                    <div class="table-container">
                        <div class="table-responsive">
                            <table class="orders-table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Item Code</th>
                                        <th>Generic Name</th>
                                        <th>Brand Name</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Total Price</th>
                                        <th>Requested By</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                    <tr>
                                        <td><strong>#{{ $order->id }}</strong></td>
                                        <td>{{ $order->item_code }}</td>
                                        <td>{{ $order->generic_name ?: '-' }}</td>
                                        <td>{{ $order->brand_name ?: '-' }}</td>
                                        <td>{{ number_format($order->quantity) }}</td>
                                        <td>₱{{ number_format($order->unit_price, 2) }}</td>
                                        <td>₱{{ number_format($order->total_price, 2) }}</td>
                                        <td>{{ $order->user->name ?? 'Unknown' }}</td>
                                        <td>
                                            <span class="status-badge status-{{ $order->status }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $order->requested_at ? $order->requested_at->format('M d, Y') : '-' }}</td>
                                        <td>
                                            <div class="action-dropdown">
                                                <button class="action-btn" onclick="toggleDropdown({{ $order->id }})">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <div class="dropdown-content" id="dropdown-{{ $order->id }}">
                                                    <a href="#" onclick="viewOrderDetails({{ $order->id }})">
                                                        <i class="fas fa-eye"></i> View Details
                                                    </a>
                                                    @if($order->status === 'pending')
                                                        <a href="#" onclick="updateOrderStatus({{ $order->id }}, 'approved')" class="approve-action">
                                                            <i class="fas fa-check"></i> Approve
                                                        </a>
                                                        <a href="#" onclick="updateOrderStatus({{ $order->id }}, 'cancelled')" class="reject-action">
                                                            <i class="fas fa-times"></i> Reject
                                                        </a>
                                                    @elseif($order->status === 'approved')
                                                        <a href="#" onclick="updateOrderStatus({{ $order->id }}, 'completed')" class="complete-action">
                                                            <i class="fas fa-check-double"></i> Mark Completed
                                                        </a>
                                                        <a href="#" onclick="updateOrderStatus({{ $order->id }}, 'cancelled')" class="reject-action">
                                                            <i class="fas fa-times"></i> Cancel
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <h3>No {{ $status !== 'all' ? $status : '' }} orders found</h3>
                        <p>{{ $status === 'all' ? 'There are no pharmacy orders at the moment.' : 'No orders with ' . $status . ' status found.' }}</p>
                    </div>
                @endif
            </div>
        </main>
    </div>

    <!-- Order Details Modal -->
    <div id="orderDetailsModal" class="inventory-modal" style="display: none;">
        <div class="inventory-modal-content">
            <div class="inventory-modal-header">
                <h3>Order Details</h3>
                <span class="inventory-modal-close" onclick="closeOrderDetailsModal()">&times;</span>
            </div>
            <div class="inventory-modal-body" id="orderDetailsContent">
                <!-- Order details will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        function toggleDropdown(orderId) {
            const dropdown = document.getElementById(`dropdown-${orderId}`);
            
            // Close all other dropdowns
            document.querySelectorAll('.dropdown-content').forEach(dd => {
                if (dd.id !== `dropdown-${orderId}`) {
                    dd.classList.remove('show');
                }
            });
            
            dropdown.classList.toggle('show');
        }

        function viewOrderDetails(orderId) {
            document.getElementById(`dropdown-${orderId}`).classList.remove('show');
            
            // Find the order data from the table
            const orderRow = Array.from(document.querySelectorAll('.orders-table tbody tr')).find(row => {
                const firstCell = row.querySelector('td:first-child');
                return firstCell && firstCell.textContent.includes('#' + orderId);
            });
            
            if (!orderRow) return;
            
            const cells = orderRow.querySelectorAll('td');
            const orderData = {
                id: orderId,
                item_code: cells[1].textContent,
                generic_name: cells[2].textContent,
                brand_name: cells[3].textContent,
                quantity: cells[4].textContent,
                unit_price: cells[5].textContent,
                total_price: cells[6].textContent,
                requested_by: cells[7].textContent,
                status: cells[8].textContent.trim(),
                date: cells[9].textContent
            };
            
            // Populate modal with order details
            document.getElementById('orderDetailsContent').innerHTML = `
                <div class="order-detail-grid">
                    <div class="order-detail-item">
                        <label>Order ID:</label>
                        <span>#${orderData.id}</span>
                    </div>
                    <div class="order-detail-item">
                        <label>Item Code:</label>
                        <span>${orderData.item_code}</span>
                    </div>
                    <div class="order-detail-item">
                        <label>Generic Name:</label>
                        <span>${orderData.generic_name !== '-' ? orderData.generic_name : 'Not specified'}</span>
                    </div>
                    <div class="order-detail-item">
                        <label>Brand Name:</label>
                        <span>${orderData.brand_name !== '-' ? orderData.brand_name : 'Not specified'}</span>
                    </div>
                    <div class="order-detail-item">
                        <label>Quantity:</label>
                        <span>${orderData.quantity}</span>
                    </div>
                    <div class="order-detail-item">
                        <label>Unit Price:</label>
                        <span>${orderData.unit_price}</span>
                    </div>
                    <div class="order-detail-item">
                        <label>Total Price:</label>
                        <span>${orderData.total_price}</span>
                    </div>
                    <div class="order-detail-item">
                        <label>Requested By:</label>
                        <span>${orderData.requested_by}</span>
                    </div>
                    <div class="order-detail-item">
                        <label>Status:</label>
                        <span class="status-badge status-${orderData.status.toLowerCase()}">${orderData.status}</span>
                    </div>
                    <div class="order-detail-item">
                        <label>Date Requested:</label>
                        <span>${orderData.date}</span>
                    </div>
                </div>
            `;
            
            openOrderDetailsModal();
        }

        function openOrderDetailsModal() {
            const modal = document.getElementById('orderDetailsModal');
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('show'), 10);
        }

        function closeOrderDetailsModal() {
            const modal = document.getElementById('orderDetailsModal');
            modal.classList.remove('show');
            setTimeout(() => modal.style.display = 'none', 300);
        }

        function updateOrderStatus(orderId, status) {
            document.getElementById(`dropdown-${orderId}`).classList.remove('show');
            
            const statusText = status.charAt(0).toUpperCase() + status.slice(1);
            if(confirm(`Are you sure you want to ${statusText.toLowerCase()} this order?`)) {
                fetch(`/inventory/orders/${orderId}/update-status`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: status })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        alert(`Order ${statusText.toLowerCase()} successfully!`);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating order status');
                });
            }
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.matches('.action-btn') && !event.target.matches('.fas')) {
                document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                    dropdown.classList.remove('show');
                });
            }
        });
    </script>

    <style>
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .status-tabs {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .status-tab {
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            color: #666;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .status-tab:hover {
            background: #e9ecef;
            color: #495057;
        }

        .status-tab.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }

        .count {
            font-size: 12px;
            opacity: 0.8;
        }

        .table-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .table-responsive {
            overflow-x: auto;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }

        .orders-table th,
        .orders-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        .orders-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .orders-table tbody tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-completed { background: #d1ecf1; color: #0c5460; }
        .status-cancelled { background: #f8d7da; color: #721c24; }

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
        }

        .action-btn:hover {
            background: #f8f9fa;
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

        .approve-action { color: #28a745 !important; }
        .reject-action { color: #dc3545 !important; }
        .complete-action { color: #17a2b8 !important; }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state h3 {
            margin-bottom: 10px;
            color: #495057;
        }

        .empty-state p {
            font-size: 14px;
            max-width: 400px;
            margin: 0 auto;
        }

        /* Modal Styles */
        .inventory-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .inventory-modal.show {
            opacity: 1;
        }

        .inventory-modal-content {
            background-color: white;
            padding: 0;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }

        .inventory-modal.show .inventory-modal-content {
            transform: scale(1);
        }

        .inventory-modal-header {
            padding: 20px 25px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8f9fa;
            border-radius: 12px 12px 0 0;
        }

        .inventory-modal-header h3 {
            margin: 0;
            color: #495057;
            font-size: 18px;
        }

        .inventory-modal-close {
            color: #aaa;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            padding: 5px;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .inventory-modal-close:hover {
            color: #000;
            background: rgba(0,0,0,0.1);
        }

        .inventory-modal-body {
            padding: 25px;
        }

        .order-detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .order-detail-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .order-detail-item label {
            font-weight: 600;
            color: #495057;
            font-size: 14px;
        }

        .order-detail-item span {
            color: #6c757d;
            font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .card-header {
                flex-direction: column;
                align-items: stretch;
            }

            .status-tabs {
                overflow-x: auto;
                padding-bottom: 5px;
            }

            .status-tab {
                white-space: nowrap;
            }

            .orders-table th,
            .orders-table td {
                padding: 8px 10px;
                font-size: 14px;
            }

            .order-detail-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>
