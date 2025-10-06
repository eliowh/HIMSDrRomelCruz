<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Dashboard</title>
    <link rel="stylesheet" href="{{ url('css/cashiercss/cashier.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    @include('cashier.cashier_header')

    <div class="cashier-layout">
        @include('cashier.cashier_sidebar')

        <main class="main-content">
            <div class="cashier-card">
                <h2>Welcome, {{ Auth::user()->name }}</h2>
                <p>This is your dashboard where you can manage billing and transactions.</p>
            </div>
            
            <div class="cashier-card">
                <h3>Today's Overview</h3>
                <div class="stats-container">
                    <!-- Placeholder for statistics -->
                    <div class="stat-item">
                        <h4>Pending Bills</h4>
                        <p class="stat-number">0</p>
                    </div>
                    <div class="stat-item">
                        <h4>Today's Collections</h4>
                        <p class="stat-number">₱0.00</p>
                    </div>
                    <div class="stat-item">
                        <h4>Total Transactions</h4>
                        <p class="stat-number">0</p>
                    </div>
                </div>
            </div>

            <div class="cashier-card">
                <h3>Recent Activity</h3>
                <!-- Placeholder for recent activity -->
                <div class="placeholder-content">
                    <p>No recent activity to display.</p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
