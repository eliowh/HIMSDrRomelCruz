<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Dashboard</title>
    <link rel="stylesheet" href="{{ url('css/inventory.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Dashboard-specific styles */
        .dashboard-header {
            margin-bottom: 30px;
        }
        .dashboard-header h1 {
            color: #367F2B;
            font-size: 2rem;
            margin: 0 0 8px 0;
        }
        .dashboard-header p {
            color: #6c757d;
            margin: 0;
            font-size: 1.1rem;
        }
        
        /* Widget Grid */
        .widgets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .widget {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(54,127,43,0.1);
            padding: 24px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .widget:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(54,127,43,0.15);
        }
        
        .widget-header {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
        }
        
        .widget-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
            font-size: 20px;
            color: white;
        }
        
        .widget-icon.primary { background: #367F2B; }
        .widget-icon.warning { background: #ffc107; }
        .widget-icon.danger { background: #dc3545; }
        .widget-icon.info { background: #17a2b8; }
        .widget-icon.success { background: #28a745; }
        
        .widget-title {
            font-size: 0.9rem;
            color: #6c757d;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .widget-value {
            font-size: 2.2rem;
            font-weight: 700;
            color: #367F2B;
            margin: 8px 0 0 0;
        }
        
        .widget-subtitle {
            font-size: 0.85rem;
            color: #6c757d;
            margin: 4px 0 0 0;
        }
        
        /* Recent Activity Widgets */
        .activity-widget {
            grid-column: 1 / -1;
        }
        
        .activity-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .activity-section h4 {
            color: #367F2B;
            margin: 0 0 16px 0;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
        }
        
        .activity-section h4 i {
            margin-right: 8px;
        }
        
        .activity-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .activity-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-info {
            flex: 1;
        }
        
        .activity-name {
            font-weight: 600;
            color: #333;
            margin: 0 0 4px 0;
        }
        
        .activity-meta {
            font-size: 0.85rem;
            color: #6c757d;
            margin: 0;
        }
        
        .activity-value {
            font-weight: 600;
            color: #367F2B;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-approved { background: #cce7ff; color: #004085; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        /* Quick Actions */
        .quick-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .action-btn {
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        
        .action-btn i {
            margin-right: 8px;
        }
        
        .action-btn.primary {
            background: #367F2B;
            color: white;
        }
        
        .action-btn.primary:hover {
            background: #2d6b25;
            transform: translateY(-1px);
        }
        
        .action-btn.secondary {
            background: #6c757d;
            color: white;
        }
        
        .action-btn.secondary:hover {
            background: #545b62;
            transform: translateY(-1px);
        }
        
        @media (max-width: 768px) {
            .activity-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .widgets-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    @include('Inventory.inventory_header')
    <div class="inventory-layout">
        @include('Inventory.inventory_sidebar')
        <main class="main-content">
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h1><i class="fas fa-tachometer-alt"></i> Inventory Dashboard</h1>
                        <p>Monitor your medicine stock levels, orders, and inventory operations</p>
                    </div>
                    <div>
                        <button onclick="refreshDashboard()" class="action-btn primary" style="margin: 0;">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                        <span id="lastUpdated" class="widget-subtitle" style="margin-left: 10px;">
                            Last updated: {{ now()->format('M d, Y H:i:s') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Statistics Widgets -->
            <div class="widgets-grid">
                <!-- Total Stocks Widget -->
                <div class="widget">
                    <div class="widget-header">
                        <div class="widget-icon primary">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div>
                            <h3 class="widget-title">Total Stock Items</h3>
                            <p class="widget-value">{{ $totalStocks }}</p>
                            <p class="widget-subtitle">Different medicines in inventory</p>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Widget -->
                <div class="widget {{ $lowStockCount > 5 ? 'alert-warning' : '' }}">
                    <div class="widget-header">
                        <div class="widget-icon warning">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div>
                            <h3 class="widget-title">Low Stock Alert</h3>
                            <p class="widget-value">{{ $lowStockCount }}</p>
                            <p class="widget-subtitle">Items with ≤10 units remaining</p>
                            @if($lowStockCount > 10)
                                <div class="widget-trend">
                                    <span class="trend-indicator trend-down">
                                        <i class="fas fa-arrow-down"></i>
                                    </span>
                                    <span>Many items need restocking</span>
                                </div>
                            @elseif($lowStockCount > 0)
                                <div class="widget-trend">
                                    <span class="trend-indicator trend-neutral">
                                        <i class="fas fa-minus"></i>
                                    </span>
                                    <span>Monitor stock levels</span>
                                </div>
                            @else
                                <div class="widget-trend">
                                    <span class="trend-indicator trend-up">
                                        <i class="fas fa-check"></i>
                                    </span>
                                    <span>Stock levels healthy</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Out of Stock Widget -->
                <div class="widget {{ $outOfStockCount > 0 ? 'alert-critical' : '' }}">
                    <div class="widget-header">
                        <div class="widget-icon danger">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div>
                            <h3 class="widget-title">Out of Stock</h3>
                            <p class="widget-value">{{ $outOfStockCount }}</p>
                            <p class="widget-subtitle">Items requiring immediate restock</p>
                            @if($outOfStockCount > 0)
                                <div class="widget-trend">
                                    <span class="trend-indicator trend-down">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </span>
                                    <span>Immediate attention needed</span>
                                </div>
                            @else
                                <div class="widget-trend">
                                    <span class="trend-indicator trend-up">
                                        <i class="fas fa-check-circle"></i>
                                    </span>
                                    <span>All items in stock</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Total Orders Widget -->
                <div class="widget">
                    <div class="widget-header">
                        <div class="widget-icon info">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div>
                            <h3 class="widget-title">Total Orders</h3>
                            <p class="widget-value">{{ $totalOrders }}</p>
                            <p class="widget-subtitle">All time stock orders</p>
                        </div>
                    </div>
                </div>

                <!-- Pending Orders Widget -->
                <div class="widget">
                    <div class="widget-header">
                        <div class="widget-icon warning">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <h3 class="widget-title">Pending Orders</h3>
                            <p class="widget-value">{{ $pendingOrders }}</p>
                            <p class="widget-subtitle">Orders awaiting approval</p>
                        </div>
                    </div>
                </div>

                <!-- Inventory Value Widget -->
                <div class="widget">
                    <div class="widget-header">
                        <div class="widget-icon success">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div>
                            <h3 class="widget-title">Inventory Value</h3>
                            <p class="widget-value">₱{{ $totalStockValue }}</p>
                            <p class="widget-subtitle">Total stock value</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="widget activity-widget">
                <div class="activity-grid">
                    <!-- Recent Stocks -->
                    <div class="activity-section">
                        <h4><i class="fas fa-plus-circle"></i> Recent Stock Items</h4>
                        @if($recentStocks->count() > 0)
                            <ul class="activity-list">
                                @foreach($recentStocks as $stock)
                                <li class="activity-item">
                                    <div class="activity-info">
                                        <p class="activity-name">{{ $stock->generic_name ?: $stock->item_code }}</p>
                                        <p class="activity-meta">{{ $stock->brand_name ?: 'No brand' }} | Code: {{ $stock->item_code }}</p>
                                    </div>
                                    <div class="activity-value">
                                        {{ $stock->quantity ?? 0 }} units
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="activity-meta">No stock items found</p>
                        @endif
                    </div>

                    <!-- Recent Orders -->
                    <div class="activity-section">
                        <h4><i class="fas fa-list-alt"></i> Recent Orders</h4>
                        @if($recentOrders->count() > 0)
                            <ul class="activity-list">
                                @foreach($recentOrders as $order)
                                <li class="activity-item">
                                    <div class="activity-info">
                                        <p class="activity-name">{{ $order->generic_name ?: $order->item_code }}</p>
                                        <p class="activity-meta">
                                            Requested by: {{ $order->user->name ?? 'Unknown' }} |
                                            {{ $order->requested_at ? $order->requested_at->format('M d, Y') : 'No date' }}
                                        </p>
                                    </div>
                                    <div>
                                        <span class="status-badge status-{{ $order->status }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="activity-meta">No recent orders found</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="inventory-card">
                <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                <div class="quick-actions">
                    <a href="{{ url('/inventory/stocks') }}" class="action-btn primary">
                        <i class="fas fa-boxes"></i> Manage Stocks
                    </a>
                    <a href="{{ url('/inventory/orders') }}" class="action-btn secondary">
                        <i class="fas fa-shopping-cart"></i> View Orders
                    </a>
                    <a href="{{ url('/inventory/reports') }}" class="action-btn secondary">
                        <i class="fas fa-chart-bar"></i> Generate Reports
                    </a>
                    <a href="{{ url('/inventory/account') }}" class="action-btn secondary">
                        <i class="fas fa-user-cog"></i> Account Settings
                    </a>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Dashboard functionality
        function refreshDashboard() {
            // Show loading state
            document.querySelectorAll('.widget').forEach(widget => {
                widget.classList.add('loading');
            });
            
            // Simulate refresh (in real app, you'd make an AJAX call)
            setTimeout(() => {
                location.reload();
            }, 1000);
        }

        // Add alert classes based on values
        document.addEventListener('DOMContentLoaded', function() {
            // Add alert class to out of stock widget if value > 0
            const outOfStockValue = parseInt(document.querySelector('.widget:nth-child(3) .widget-value').textContent);
            if (outOfStockValue > 0) {
                document.querySelector('.widget:nth-child(3)').classList.add('alert-critical');
            }
            
            // Add alert class to low stock widget if value > 5
            const lowStockValue = parseInt(document.querySelector('.widget:nth-child(2) .widget-value').textContent);
            if (lowStockValue > 5) {
                document.querySelector('.widget:nth-child(2)').classList.add('alert-warning');
            }
            
            // Add hover effects to activity items
            document.querySelectorAll('.activity-item').forEach(item => {
                item.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = '#f8f9fa';
                    this.style.transform = 'translateX(4px)';
                    this.style.transition = 'all 0.2s ease';
                });
                
                item.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = '';
                    this.style.transform = '';
                });
            });
        });

        // Auto-refresh every 5 minutes
        setInterval(function() {
            const lastUpdatedElement = document.getElementById('lastUpdated');
            if (lastUpdatedElement) {
                const now = new Date();
                const timeString = now.toLocaleDateString('en-US', { 
                    month: 'short', 
                    day: 'numeric', 
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
                lastUpdatedElement.textContent = `Last updated: ${timeString}`;
            }
        }, 300000); // 5 minutes
    </script>
</body>
</html>
