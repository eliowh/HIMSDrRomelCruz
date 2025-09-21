<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Dashboard</title>
    <link rel="stylesheet" href="{{ url('css/inventory.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    @include('Inventory.inventory_header')
    <div class="inventory-layout">
        @include('Inventory.inventory_sidebar')
        <main class="main-content">
            <div class="inventory-card">
                <h2>Inventory Dashboard</h2>
                <p>Manage medicine stock, suppliers, and orders.</p>
            </div>

            <div class="inventory-card">
                <h3>Quick Actions</h3>
                <div class="quick-actions">
                    <a href="{{ url('/inventory/stocks') }}" class="action-btn primary">View Stocks</a>
                    <a href="{{ url('/inventory/orders') }}" class="action-btn secondary">Create Order</a>
                    <a href="{{ url('/inventory/reports') }}" class="action-btn secondary">View Reports</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
