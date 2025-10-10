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
                // Get billing statistics
                $pendingBillings = App\Models\Billing::where('status', 'pending')->count();
                $paidBillings = App\Models\Billing::where('status', 'paid')->count();
                $totalBillings = $pendingBillings + $paidBillings;
                
                // Get today's collections
                $todayCollections = App\Models\Billing::where('status', 'paid')
                    ->whereDate('payment_date', today())
                    ->sum('total_amount');
                
                // Get recent billings
                $recentBillings = App\Models\Billing::with(['patient'])
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get();
            @endphp

            <div class="cashier-card">
                <h2>Welcome, {{ Auth::user()->name }}</h2>
                <p>This is your dashboard where you can manage billing and payment processing.</p>
            </div>
            
            <!-- Statistics Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-number">{{ $pendingBillings }}</span>
                        <span class="stat-label">Pending Bills</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon paid">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-number">{{ $paidBillings }}</span>
                        <span class="stat-label">Paid Bills</span>
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
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-number">{{ $totalBillings }}</span>
                        <span class="stat-label">Total Billings</span>
                    </div>
                </div>
            </div>
            
            <!-- Recent Billings -->
            <div class="cashier-card">
                <div class="card-header">
                    <h3>Recent Billings</h3>
                    <a href="/cashier/billing" class="view-all-link">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                
                @if($recentBillings->count())
                    <div class="table-wrap">
                        <table class="billings-table">
                            <thead>
                                <tr>
                                    <th>Billing #</th>
                                    <th>Patient</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentBillings as $billing)
                                <tr>
                                    <td>{{ $billing->billing_number }}</td>
                                    <td>{{ $billing->patient->full_name ?? 'Unknown Patient' }}</td>
                                    <td>₱{{ number_format($billing->total_amount, 2) }}</td>
                                    <td>
                                        <span class="status-badge status-{{ $billing->status }}">
                                            {{ ucfirst($billing->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $billing->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <a href="/cashier/billing/{{ $billing->id }}/view" class="action-link">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="no-data">
                        <i class="fas fa-file-invoice"></i>
                        <p>No billings found</p>
                    </div>
                @endif
            </div>
                        
        </main>
    </div>
</body>
</html>
