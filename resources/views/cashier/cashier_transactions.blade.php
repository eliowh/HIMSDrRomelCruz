<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History</title>
    <link rel="stylesheet" href="{{ asset('css/cashiercss/cashier.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    @include('cashier.cashier_header')

    <div class="cashier-layout">
        @include('cashier.cashier_sidebar')

        <main class="main-content">
            <div class="cashier-card">
                <h2>Transaction History</h2>
                <p>View and manage all financial transactions.</p>
            </div>
            
            <div class="cashier-card">
                <h3>Transaction Overview</h3>
                <div class="stats-container">
                    <div class="stat-item">
                        <h4>Today's Total</h4>
                        <p class="stat-number">₱0.00</p>
                    </div>
                    <div class="stat-item">
                        <h4>Week's Total</h4>
                        <p class="stat-number">₱0.00</p>
                    </div>
                    <div class="stat-item">
                        <h4>Month's Total</h4>
                        <p class="stat-number">₱0.00</p>
                    </div>
                </div>
            </div>

            <div class="cashier-card">
                <h3>Transaction Records</h3>
                <div class="filters-section">
                    <!-- Placeholder for date range and filters -->
                    <div class="date-range">
                        <input type="date" disabled>
                        <span>to</span>
                        <input type="date" disabled>
                        <button class="filter-btn" disabled>
                            <span class="icon">🔍</span> Filter
                        </button>
                    </div>
                </div>
                <div class="table-container">
                    <table class="cashier-table">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Transaction ID</th>
                                <th>Patient</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Placeholder for transaction records -->
                            <tr>
                                <td colspan="7" class="empty-state">No transactions to display</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="cashier-card">
                <h3>Generate Reports</h3>
                <!-- Placeholder for report generation -->
                <div class="placeholder-content">
                    <p>Report generation functionality will be implemented soon.</p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
