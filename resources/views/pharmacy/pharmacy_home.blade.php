<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('img/hospital_logo.png') }}">
    <title>Pharmacy Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/pharmacycss/pharmacy.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Ensure reorder modal overlays correctly even if inventory CSS isn't loaded */
        #reorderModal {
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
            transition: opacity 0.25s ease;
        }
        #reorderModal.show { opacity: 1; }
        #reorderModal .inventory-modal-content {
            background-color: #fff;
            padding: 0;
            border-radius: 12px;
            width: 90%;
            max-width: 900px;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
            transform: scale(0.95);
            transition: transform 0.2s ease;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        #reorderModal.show .inventory-modal-content { transform: scale(1); }
        #reorderModal .inventory-modal-header { padding: 16px 20px; border-bottom: 1px solid #eee; display:flex; justify-content:space-between; align-items:center; }
        #reorderModal .inventory-modal-body { padding: 18px; }
        #reorderModal .inventory-modal-close { cursor:pointer; font-size:20px; padding:6px; border-radius:4px; }
        /* small-reorder-btn removed - card is clickable */
        #reorderModal table.table { width:100%; border-collapse:collapse; }
        #reorderModal table.table th, #reorderModal table.table td { padding:8px 10px; text-align:left; border-bottom:1px solid #f0f0f0; }
        @media (max-width:600px) { #reorderModal .inventory-modal-content { width: 96%; } }
    </style>
</head>
<body>
    @php
        $pharmacyName = auth()->check() ? auth()->user()->name : 'Pharmacy Staff';
    @endphp
    @include('pharmacy.pharmacy_header')

    <div class="pharmacy-layout">
        @include('pharmacy.pharmacy_sidebar')

        <main class="main-content">
            <div class="dashboard-header">
                <div>
                    <h1>Pharmacy Dashboard</h1>
                    <p>Welcome back, {{ Auth::user()->name }}! Here's your pharmacy overview.</p>
                </div>
                <div class="header-actions">
                    <button class="btn btn-outline" onclick="refreshDashboard()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    {{-- New Order removed per request --}}
                </div>
            </div>
            
            <!-- (Order summary removed — dashboard now focuses on stock metrics) -->
            
            <!-- Pharmacy Stocks Summary -->
            <div class="dashboard-grid" style="margin-top:16px;">
                <div class="stat-card">
                    <div class="stat-icon total">
                        <i class="fas fa-boxes" style="color:#4e73df"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-number">{{ $totalStocks ?? 0 }}</span>
                        <span class="stat-label">Total Medicines</span>
                        <small class="stat-sublabel">All stocked items</small>
                    </div>
                </div>

                <div id="low-stock-card" class="stat-card {{ ($lowStockCount ?? 0) > 0 ? 'alert-warning' : '' }}" onclick="openReorderModal()" role="button" tabindex="0" style="cursor:pointer;">
                    <div class="stat-icon low" style="color:#ff8c00">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-number">{{ $lowStockCount ?? 0 }}</span>
                        <span class="stat-label">Low Stock</span>
                        <small class="stat-sublabel">At or below reorder level</small>
                    </div>
                    {{-- action button removed; card itself is clickable to open reorder modal --}}
                </div>

                <div class="stat-card">
                    <div class="stat-icon out" style="color:#e74a3b">
                        <i class="fas fa-ban"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-number">{{ $outOfStockCount ?? 0 }}</span>
                        <span class="stat-label">Out of Stock</span>
                        <small class="stat-sublabel">No available units</small>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon value">
                        <i class="fas fa-peso-sign"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-number">₱{{ number_format($totalStockValue ?? 0, 2) }}</span>
                        <span class="stat-label">Stock Value</span>
                        <small class="stat-sublabel">Estimated inventory value</small>
                    </div>
                </div>
            </div>
            
            <!-- Recent Stocks -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h3><i class="fas fa-box-open"></i> Recent Stocks</h3>
                    <a href="{{ route('pharmacy.stockspharmacy') }}" class="view-all-link">
                        View All Stocks <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                @if(isset($recentStocks) && $recentStocks->count() > 0)
                    <div class="orders-grid">
                        @foreach($recentStocks as $stock)
                        <div class="order-card">
                            <div class="order-header">
                                <span class="order-id">{{ $stock->item_code }}</span>
                                <span class="status-badge status-info">{{ $stock->quantity }} pcs</span>
                            </div>
                            <div class="order-content">
                                <div class="medicine-info">
                                    <strong>{{ $stock->generic_name ?: $stock->brand_name }}</strong>
                                    @if($stock->generic_name && $stock->brand_name)
                                        <br><small>Brand: {{ $stock->brand_name }}</small>
                                    @endif
                                    <br><small>Code: {{ $stock->item_code }}</small>
                                </div>
                                <div class="order-details">
                                    <div class="quantity">
                                        <i class="fas fa-boxes"></i>
                                        Reorder: {{ $stock->reorder_level ?? '-' }}
                                    </div>
                                    <div class="total-price">
                                        <i class="fas fa-peso-sign"></i>
                                        ₱{{ number_format($stock->price ?? 0, 2) }}
                                    </div>
                                </div>
                            </div>
                            <div class="order-footer">
                                <small class="text-muted">
                                    <i class="fas fa-calendar-alt"></i>
                                    {{ $stock->created_at ? $stock->created_at->diffForHumans() : '' }}
                                </small>
                                @if($stock->expiry_date)
                                <small class="order-notes">
                                    <i class="fas fa-calendar-times"></i>
                                    Expires: {{ $stock->expiry_date->format('Y-m-d') }}
                                </small>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <h4>No Recent Stocks</h4>
                        <p>No recent stock additions found.</p>
                        <a href="{{ route('pharmacy.stockspharmacy') }}" class="btn pharmacy-btn-primary">
                            <i class="fas fa-plus"></i> Add Stock
                        </a>
                    </div>
                @endif
            </div>
            
            <!-- Reorder Modal -->
            <div id="reorderModal" class="inventory-modal" style="display:none;">
                <div class="inventory-modal-content" style="max-width:800px;">
                            <div class="inventory-modal-header">
                                <h3>Reorder Low Stock Items</h3>
                                <span class="inventory-modal-close" onclick="closeReorderModal()">&times;</span>
                            </div>
                    <div class="inventory-modal-body">
                        @if(isset($lowStockItems) && $lowStockItems->count() > 0)
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Qty</th>
                                        <th>Reorder</th>
                                        <th>Price</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lowStockItems as $li)
                                    <tr>
                                        <td>{{ $li->item_code }}</td>
                                        <td>{{ $li->generic_name ?: $li->brand_name }}</td>
                                        <td>{{ $li->quantity }}</td>
                                        <td>{{ $li->reorder_level }}</td>
                                        <td>₱{{ number_format($li->price ?? 0,2) }}</td>
                                        <td>
                                            <form method="POST" action="{{ url('/pharmacy/orders') }}" style="display:inline;">
                                                @csrf
                                                <input type="hidden" name="item_code" value="{{ $li->item_code }}">
                                                <input type="hidden" name="generic_name" value="{{ $li->generic_name }}">
                                                <input type="hidden" name="brand_name" value="{{ $li->brand_name }}">
                                                <input type="hidden" name="unit_price" value="{{ $li->price }}">
                                                <input type="number" name="quantity" value="{{ max( ($li->reorder_level ?: 1) * 2, 1) }}" min="1" style="width:80px; display:inline-block; margin-right:8px;">
                                                <button class="pharmacy-btn-primary btn-sm" type="submit">Reorder</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p>No low-stock items available.</p>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Quick actions removed per request -->
        </main>
    </div>

    <script>
        function refreshDashboard() {
            // Show loading state
            const refreshBtn = document.querySelector('[onclick="refreshDashboard()"]');
            const originalContent = refreshBtn.innerHTML;
            refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
            refreshBtn.disabled = true;
            
            // Reload the page after a short delay to show the loading state
            setTimeout(() => {
                window.location.reload();
            }, 500);
        }

        // Auto-refresh every 5 minutes
        setInterval(() => {
            // Update timestamp only, not full refresh
            const now = new Date();
            const timeElements = document.querySelectorAll('.stat-sublabel');
            // You can add more sophisticated auto-refresh logic here
        }, 300000); // 5 minutes

        // Add hover effects and animations
        document.addEventListener('DOMContentLoaded', function() {
            // Pulse when there are low-stock items
            try {
                if ({{ $lowStockCount ?? 0 }} > 0) {
                    const el = document.querySelector('.stat-card.alert-warning');
                    if (el) el.classList.add('pulse');
                }
            } catch (e) {
                // Graceful fallback if variables are not present
            }
            // Initialize tooltips or other interactive elements here
        });

        function openReorderModal() {
            const m = document.getElementById('reorderModal');
            if (!m) return;
            // Use flex display so CSS centering (align-items/justify-content) works
            m.style.display = 'flex';
            // Add show class to trigger opacity/transform animations defined in CSS
            m.classList.add('show');
            // Close when clicking outside the content
            m.addEventListener('click', function onBgClick(e) {
                if (e.target === m) {
                    closeReorderModal();
                }
            }, { once: true });
        }

        function closeReorderModal() {
            const m = document.getElementById('reorderModal');
            if (!m) return;
            m.classList.remove('show');
            // allow CSS transition to play then hide
            setTimeout(() => { m.style.display = 'none'; }, 250);
        }
    </script>
</body>
</html>
