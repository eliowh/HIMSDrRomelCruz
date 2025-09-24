<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                <h2>Pharmacy Orders</h2>
                @php
                    $orders = collect();
                    try {
                        $orders = DB::table('stock_orders')
                            ->leftJoin('users', 'stock_orders.requested_by', '=', 'users.id')
                            ->select('stock_orders.*', 'users.name as requested_by_name')
                            ->orderBy('stock_orders.created_at', 'desc')
                            ->get();
                    } catch (\Exception $e) {
                        // Handle case where table doesn't exist yet
                    }
                @endphp

                @if($orders->count())
                    <div class="table-wrap">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Item Code</th>
                                    <th>Generic Name</th>
                                    <th>Brand</th>
                                    <th>Quantity</th>
                                    <th>Requested By</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                <tr>
                                    <td>#{{ $order->id }}</td>
                                    <td>{{ $order->item_code }}</td>
                                    <td>{{ $order->generic_name }}</td>
                                    <td>{{ $order->brand_name }}</td>
                                    <td>{{ $order->quantity }}</td>
                                    <td>{{ $order->requested_by_name ?? 'Unknown' }}</td>
                                    <td>
                                        <span class="status-badge status-{{ $order->status }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y') }}</td>
                                    <td>
                                        <div class="action-dropdown">
                                            <button class="action-btn" onclick="toggleDropdown({{ $order->id }})">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-content" id="dropdown-{{ $order->id }}">
                                                <a href="#" onclick="viewOrderDetails({{ $order->id }})">View Details</a>
                                                @if($order->status === 'pending')
                                                    <a href="#" onclick="updateOrderStatus({{ $order->id }}, 'approved')" class="approve-action">Approve</a>
                                                    <a href="#" onclick="updateOrderStatus({{ $order->id }}, 'cancelled')" class="reject-action">Reject</a>
                                                @elseif($order->status === 'approved')
                                                    <a href="#" onclick="updateOrderStatus({{ $order->id }}, 'completed')" class="complete-action">Mark Completed</a>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info">No pharmacy orders found.</div>
                @endif
            </div>
        </main>
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
            // Implementation for viewing order details
            alert('View order details for order #' + orderId);
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
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-completed { background: #d1ecf1; color: #0c5460; }
        .status-cancelled { background: #f8d7da; color: #721c24; }

        .action-dropdown { position: relative; }
        .action-btn { background: transparent; border: none; padding: 8px; cursor: pointer; }
        .dropdown-content {
            position: absolute; right: 0; top: 100%; background: white; min-width: 120px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-radius: 6px; z-index: 1000; display: none;
        }
        .dropdown-content.show { display: block; }
        .dropdown-content a { display: block; padding: 8px 12px; text-decoration: none; color: #333; }
        .dropdown-content a:hover { background: #f8f9fa; }
        .approve-action { color: #28a745 !important; }
        .reject-action { color: #dc3545 !important; }
        .complete-action { color: #17a2b8 !important; }
        .alert-info { background: #d1ecf1; color: #0c5460; padding: 12px; border-radius: 6px; }
    </style>
</body>
</html>
