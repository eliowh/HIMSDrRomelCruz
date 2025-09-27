<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Reports</title>
    <link rel="stylesheet" href="{{ url('css/inventorycss/inventory.css') }}">
    <link rel="stylesheet" href="{{ url('css/inventorycss/inventory_reports.css') }}">
    <link rel="stylesheet" href="{{ url('css/pagination.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    @include('Inventory.inventory_header')
    <div class="inventory-layout">
        @include('Inventory.inventory_sidebar')
        <main class="main-content">
            @if(isset($error))
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>{{ $error }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>{{ session('warning') }}</span>
                </div>
            @endif

            <!-- Report Navigation -->
            <div class="reports-nav">
                <h2><i class="fas fa-chart-bar"></i> Inventory Reports</h2>
                <div class="report-tabs">
                    <a href="{{ route('inventory.reports', ['type' => 'overview']) }}" 
                       class="report-tab {{ ($reportType ?? 'overview') === 'overview' ? 'active' : '' }}">
                        <i class="fas fa-dashboard"></i> Overview
                    </a>
                    <a href="{{ route('inventory.reports', ['type' => 'low-stock']) }}" 
                       class="report-tab {{ ($reportType ?? '') === 'low-stock' ? 'active' : '' }}">
                        <i class="fas fa-exclamation-triangle"></i> Low Stock
                    </a>
                    <a href="{{ route('inventory.reports', ['type' => 'out-of-stock']) }}" 
                       class="report-tab {{ ($reportType ?? '') === 'out-of-stock' ? 'active' : '' }}">
                        <i class="fas fa-times-circle"></i> Out of Stock
                    </a>
                    <a href="{{ route('inventory.reports', ['type' => 'expiring']) }}" 
                       class="report-tab {{ ($reportType ?? '') === 'expiring' ? 'active' : '' }}">
                        <i class="fas fa-clock"></i> Expiring Soon
                    </a>
                    <a href="{{ route('inventory.reports', ['type' => 'expired']) }}" 
                       class="report-tab {{ ($reportType ?? '') === 'expired' ? 'active' : '' }}">
                        <i class="fas fa-ban"></i> Expired
                    </a>
                    <a href="{{ route('inventory.reports', ['type' => 'stock-movement']) }}" 
                       class="report-tab {{ ($reportType ?? '') === 'stock-movement' ? 'active' : '' }}">
                        <i class="fas fa-exchange-alt"></i> Stock Movement
                    </a>
                </div>
            </div>

            <!-- Summary Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="stat-content">
                        <h3>{{ number_format($stats['total_items'] ?? 0) }}</h3>
                        <p>Total Items</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-peso-sign"></i>
                    </div>
                    <div class="stat-content">
                        <h3>₱{{ number_format($stats['total_value'] ?? 0, 2) }}</h3>
                        <p>Total Value</p>
                    </div>
                </div>
                <div class="stat-card low-stock">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-content">
                        <h3>{{ $stats['low_stock'] ?? 0 }}</h3>
                        <p>Low Stock Items</p>
                    </div>
                </div>
                <div class="stat-card expiring">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3>{{ $stats['expiring_soon'] ?? 0 }}</h3>
                        <p>Expiring Soon</p>
                    </div>
                </div>
            </div>

            <!-- Report Content -->
            @if(($reportType ?? 'overview') === 'overview')
                @include('Inventory.reports.overview')
            @elseif($reportType === 'low-stock')
                @include('Inventory.reports.low_stock')
            @elseif($reportType === 'out-of-stock')
                @include('Inventory.reports.out_of_stock')
            @elseif($reportType === 'expiring')
                @include('Inventory.reports.expiring')
            @elseif($reportType === 'expired')
                @include('Inventory.reports.expired')
            @elseif($reportType === 'stock-movement')
                @include('Inventory.reports.stock_movement')
            @endif

        </main>
    </div>
</body>
</html>