<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/cashiercss/cashier.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    @include('cashier.cashier_header')

    <div class="cashier-layout">
        @include('cashier.cashier_sidebar')

        <main class="main-content">
            @php
                // Use controller statistics if available, otherwise fallback
                if (!isset($stats)) {
                    $stats = [
                        'total_payments' => App\Models\Billing::where('status', 'paid')->count(),
                        'total_amount' => App\Models\Billing::where('status', 'paid')->sum('net_amount') ?? 0,
                        'pending_billings' => App\Models\Billing::where('status', 'pending')->count(),
                        'filter' => 'week',
                        'recent_payments' => App\Models\Billing::with('patient')->where('status', 'paid')->orderBy('payment_date', 'desc')->take(5)->get()
                    ];
                }
                $todayCollections = App\Models\Billing::where('status', 'paid')
                    ->whereDate('payment_date', today())
                    ->sum('net_amount') ?? 0;
            @endphp

            <div class="cashier-card">
                <h2>Welcome, {{ Auth::user()->name }}</h2>
                <p>This is your dashboard where you can manage billing and payment processing.</p>
                
                <!-- Filter Controls -->
                <div class="filter-controls">
                    <label>Payment Statistics Period:</label>
                    <select id="statisticsFilter" onchange="filterStatistics(this.value)">
                        <option value="week" {{ ($stats['filter'] ?? 'week') === 'week' ? 'selected' : '' }}>Past Week</option>
                        <option value="month" {{ ($stats['filter'] ?? 'week') === 'month' ? 'selected' : '' }}>Past Month</option>
                        <option value="year" {{ ($stats['filter'] ?? 'week') === 'year' ? 'selected' : '' }}>Past Year</option>
                    </select>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-number">{{ $stats['pending_billings'] }}</span>
                        <span class="stat-label">Pending Bills</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon paid">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-number">{{ $stats['total_payments'] }}</span>
                        <span class="stat-label">Paid Bills ({{ ucfirst($stats['filter'] ?? 'week') }})</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon collections">
                        <i class="fas fa-peso-sign"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-number">₱{{ number_format($todayCollections, 2) }}</span>
                        <span class="stat-label">Today's Collections</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon total">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-number">₱{{ number_format($stats['total_amount'], 2) }}</span>
                        <span class="stat-label">Total Revenue ({{ ucfirst($stats['filter'] ?? 'week') }})</span>
                    </div>
                </div>
            </div>
            
            <!-- Recent Payments -->
            <div class="cashier-card">
                <div class="card-header">
                    <h3>Recent Payments ({{ ucfirst($stats['filter'] ?? 'week') }})</h3>
                    <a href="/cashier/billing" class="view-all-link">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                
                @if($stats['recent_payments']->count())
                    <div class="table-wrap">
                        <table class="billings-table">
                            <thead>
                                <tr>
                                    <th>Billing #</th>
                                    <th>Patient</th>
                                    <th>Amount Paid</th>
                                    <th>Change Given</th>
                                    <th>Payment Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['recent_payments'] as $billing)
                                <tr>
                                    <td>{{ $billing->billing_number }}</td>
                                    <td>{{ $billing->patient->display_name ?? 'Unknown Patient' }}</td>
                                    <td>₱{{ number_format($billing->payment_amount ?? $billing->net_amount, 2) }}</td>
                                    <td>
                                        @if($billing->change_amount)
                                            ₱{{ number_format($billing->change_amount, 2) }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ $billing->payment_date ? $billing->payment_date->format('M d, Y H:i') : 'N/A' }}</td>
                                    <td>
                                        <a href="/cashier/billing/{{ $billing->id }}/view" class="action-link">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="{{ route('cashier.billing.receipt', $billing->id) }}" class="action-link" target="_blank">
                                            <i class="fas fa-receipt"></i> Receipt
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                                @else
                    <div class="no-data">
                        <i class="fas fa-receipt"></i>
                        <p>No recent payments found for the selected period</p>
                    </div>
                @endif
            </div>
        </main>
    </div>

    <!-- Filter JavaScript -->
    <script>
        function filterStatistics(filter) {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('filter', filter);
            window.location.href = currentUrl.toString();
        }
        
        // Add styling for filter controls
        const style = document.createElement('style');
        style.textContent = `
            .filter-controls {
                margin: 15px 0;
                padding: 15px;
                background: #f8f9fa;
                border-radius: 8px;
                border: 1px solid #e9ecef;
            }
            .filter-controls label {
                font-weight: bold;
                margin-right: 10px;
                color: #495057;
            }
            .filter-controls select {
                padding: 8px 12px;
                border: 1px solid #ced4da;
                border-radius: 4px;
                background: white;
                font-size: 14px;
                min-width: 150px;
            }
            .filter-controls select:focus {
                border-color: #80bdff;
                box-shadow: 0 0 0 2px rgba(0,123,255,.25);
                outline: none;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
            </div>
                        
        </main>
    </div>
</body>
</html>
