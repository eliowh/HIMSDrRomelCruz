<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/pharmacy.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    @php
        $pharmacyName = auth()->check() ? auth()->user()->name : 'Pharmacy Staff';
        
        // Get pharmacy order statistics (placeholder for now - adapt based on your pharmacy order model)
        $pendingOrders = 0; // Replace with actual pharmacy order count when model is created
        $inProgressOrders = 0;
        $completedOrders = 0;
        $cancelledOrders = 0;
        $totalOrders = $pendingOrders + $inProgressOrders + $completedOrders + $cancelledOrders;
        
        // Get recent pharmacy orders (placeholder for now)
        $recentOrders = collect(); // Replace with actual pharmacy orders when model is created
    @endphp
    @include('pharmacy.pharmacy_header')

    <div class="pharmacy-layout">
        @include('pharmacy.pharmacy_sidebar')

        <main class="main-content">
            <div class="pharmacy-card">
                <h2>Welcome, {{ Auth::check() ? Auth::user()->name : 'Pharmacy Staff' }}</h2>
                <p>This is your dashboard where you can manage pharmacy orders and prescriptions.</p>
            </div>
            
            <!-- Statistics Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-number">{{ $pendingOrders }}</span>
                        <span class="stat-label">Pending Orders</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon in-progress">
                        <i class="fas fa-pills"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-number">{{ $inProgressOrders }}</span>
                        <span class="stat-label">In Progress</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon completed">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-number">{{ $completedOrders }}</span>
                        <span class="stat-label">Completed</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon total">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-number">{{ $totalOrders }}</span>
                        <span class="stat-label">Total Orders</span>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="pharmacy-card">
                <div class="card-header">
                    <h3>Recent Orders</h3>
                    <a href="{{ route('pharmacy.orders') }}" class="view-all-link">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                
                @if($recentOrders->count())
                    <div class="table-wrap">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Patient</th>
                                    <th>Prescription</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Requested At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                <tr>
                                    <td>#{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $order->patient_name }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($order->prescription_details ?? 'N/A', 30) }}</td>
                                    <td>
                                        <span class="priority-badge priority-{{ $order->priority ?? 'normal' }}">
                                            {{ ucfirst($order->priority ?? 'normal') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-{{ $order->status ?? 'pending' }}">
                                            {{ ucfirst(str_replace('_', ' ', $order->status ?? 'pending')) }}
                                        </span>
                                    </td>
                                    <td>{{ $order->requested_at ? $order->requested_at->format('M d, Y H:i') : 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="no-data">
                        <i class="fas fa-pills"></i>
                        <p>No pharmacy orders found</p>
                    </div>
                @endif
            </div>
            
            <!-- Quick Actions -->
            <div class="pharmacy-card">
                <h3>Quick Actions</h3>
                <div class="quick-actions">
                    <a href="{{ route('pharmacy.orders') }}" class="quick-action-btn">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Manage Orders</span>
                    </a>
                    <a href="/pharmacy/account" class="quick-action-btn">
                        <i class="fas fa-user-cog"></i>
                        <span>My Account</span>
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
