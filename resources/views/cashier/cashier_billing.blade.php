<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Fallback for production -->
    <link rel="stylesheet" href="{{ secure_asset('css/app.css') }}" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    @include('cashier.cashier_header')

    <div class="cashier-layout">
        @include('cashier.cashier_sidebar')

        <main class="main-content">
            <div class="cashier-card">
                <h2>Billing Management</h2>
                <p>Create and manage patient bills and payments.</p>
            </div>
            
            <div class="cashier-card">
                <h3>Pending Bills</h3>
                <div class="search-section">
                    <!-- Placeholder for search functionality -->
                    <div class="search-box">
                        <input type="text" placeholder="Search bills by patient name or ID..." disabled>
                        <button class="search-btn" disabled>
                            <span class="icon">🔍</span>
                        </button>
                    </div>
                </div>
                <!-- Placeholder for pending bills list -->
                <div class="placeholder-content">
                    <p>No pending bills to display.</p>
                </div>
            </div>

            <div class="cashier-card">
                <h3>Recent Payments</h3>
                <div class="table-container">
                    <table class="cashier-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Patient</th>
                                <th>Service</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Placeholder for payment records -->
                            <tr>
                                <td colspan="5" class="empty-state">No recent payments to display</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="cashier-card">
                <h3>Create New Bill</h3>
                <!-- Placeholder for new bill form -->
                <div class="placeholder-content">
                    <p>Bill creation functionality will be implemented soon.</p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
