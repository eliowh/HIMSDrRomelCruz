<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Payment Management - Cashier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/cashiercss/cashier.css') }}">
</head>
<body>
    @include('cashier.cashier_header')

    <div class="cashier-layout">
        @include('cashier.cashier_sidebar')

        <main class="main-content">
            <div class="container-fluid mt-4">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2><i class="fas fa-cash-register text-success"></i> Payment Management</h2>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-primary" onclick="location.reload()">
                                    <i class="fas fa-sync-alt"></i> Refresh
                                </button>
                            </div>
                        </div>

                        <!-- Statistics Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-warning text-dark">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h6 class="card-title">Pending Payments</h6>
                                                <h4 class="mb-0">{{ $billings->where('status', 'pending')->count() }}</h4>
                                            </div>
                                            <i class="fas fa-clock fa-2x opacity-75"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h6 class="card-title">Paid Today</h6>
                                                <h4 class="mb-0">{{ $billings->where('status', 'paid')->where('payment_date', '>=', now()->startOfDay())->count() }}</h4>
                                            </div>
                                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h6 class="card-title">Total Amount Pending</h6>
                                                <h4 class="mb-0">₱{{ number_format($billings->where('status', 'pending')->sum('net_amount'), 2) }}</h4>
                                            </div>
                                            <i class="fas fa-peso-sign fa-2x opacity-75"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h6 class="card-title">Today's Revenue</h6>
                                                <h4 class="mb-0">₱{{ number_format($billings->where('status', 'paid')->where('payment_date', '>=', now()->startOfDay())->sum('net_amount'), 2) }}</h4>
                                            </div>
                                            <i class="fas fa-chart-line fa-2x opacity-75"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Billings List -->
                        <div class="card shadow">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-list"></i> Patient Billings - Payment Processing</h5>
                            </div>
                            <div class="card-body">
                                <!-- Filter Tabs -->
                                <ul class="nav nav-tabs mb-3" id="billingTabs">
                                    <li class="nav-item">
                                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#all-billings">
                                            <i class="fas fa-list-alt"></i> All Billings ({{ $billings->count() }})
                                        </button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pending-billings">
                                            <i class="fas fa-clock text-warning"></i> Pending ({{ $billings->where('status', 'pending')->count() }})
                                        </button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#paid-billings">
                                            <i class="fas fa-check-circle text-success"></i> Paid ({{ $billings->where('status', 'paid')->count() }})
                                        </button>
                                    </li>
                                </ul>

                                <!-- Tab Content -->
                                <div class="tab-content">
                                    <!-- All Billings Tab -->
                                    <div class="tab-pane fade show active" id="all-billings">
                                        <div class="table-responsive">
                                            <table class="table table-hover table-striped">
                                                <thead class="table-success">
                                                    <tr>
                                                        <th>Billing #</th>
                                                        <th>Patient</th>
                                                        <th>Total Amount</th>
                                                        <th>PhilHealth</th>
                                                        <th>Discount</th>
                                                        <th>Net Amount</th>
                                                        <th>Status</th>
                                                        <th>Date</th>
                                                        <th>Payment Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($billings as $billing)
                                                    <tr>
                                                        <td><strong>{{ $billing->billing_number }}</strong></td>
                                                        <td>{{ $billing->patient->full_name ?? 'Unknown Patient' }}</td>
                                                        <td>₱{{ number_format($billing->total_amount ?? 0, 2) }}</td>
                                                        <td class="text-success">
                                                            @if($billing->is_philhealth_member)
                                                                -₱{{ number_format($billing->philhealth_deduction ?? 0, 2) }}
                                                            @else
                                                                <span class="text-muted">N/A</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-success">
                                                            @if($billing->senior_pwd_discount > 0)
                                                                -₱{{ number_format($billing->senior_pwd_discount ?? 0, 2) }}
                                                            @else
                                                                <span class="text-muted">N/A</span>
                                                            @endif
                                                        </td>
                                                        <td><strong class="text-primary">₱{{ number_format($billing->net_amount ?? 0, 2) }}</strong></td>
                                                        <td>
                                                            @if($billing->status === 'paid')
                                                                <span class="badge bg-success"><i class="fas fa-check-circle"></i> PAID</span>
                                                                @if($billing->payment_date)
                                                                    <br><small class="text-muted">{{ $billing->payment_date->format('M d, Y h:i A') }}</small>
                                                                @endif
                                                            @else
                                                                <span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> PENDING</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $billing->created_at->format('M d, Y') }}</td>
                                                        <td>
                                                            <div class="btn-group-vertical gap-1">
                                                                @if($billing->status === 'pending')
                                                                    <button type="button" 
                                                                            class="btn btn-success btn-sm mark-as-paid-btn" 
                                                                            title="Mark as Paid"
                                                                            data-billing-id="{{ $billing->id }}"
                                                                            data-billing-number="{{ $billing->billing_number }}">
                                                                        <i class="fas fa-check-circle"></i> Mark as Paid
                                                                    </button>
                                                                @elseif($billing->status === 'paid')
                                                                    <button type="button" 
                                                                            class="btn btn-outline-secondary btn-sm mark-as-unpaid-btn" 
                                                                            title="Revert to Unpaid"
                                                                            data-billing-id="{{ $billing->id }}"
                                                                            data-billing-number="{{ $billing->billing_number }}">
                                                                        <i class="fas fa-undo"></i> Revert Payment
                                                                    </button>
                                                                @endif
                                                                <a href="/cashier/billing/{{ $billing->id }}/view" 
                                                                   class="btn btn-outline-info btn-sm" 
                                                                   title="View Details">
                                                                    <i class="fas fa-eye"></i> View
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Pending Billings Tab -->
                                    <div class="tab-pane fade" id="pending-billings">
                                        <div class="table-responsive">
                                            <table class="table table-hover table-striped">
                                                <thead class="table-warning">
                                                    <tr>
                                                        <th>Billing #</th>
                                                        <th>Patient</th>
                                                        <th>Net Amount</th>
                                                        <th>Date Created</th>
                                                        <th>Days Pending</th>
                                                        <th>Payment Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($billings->where('status', 'pending') as $billing)
                                                    <tr>
                                                        <td><strong>{{ $billing->billing_number }}</strong></td>
                                                        <td>{{ $billing->patient->full_name ?? 'Unknown Patient' }}</td>
                                                        <td><strong class="text-primary">₱{{ number_format($billing->net_amount ?? 0, 2) }}</strong></td>
                                                        <td>{{ $billing->created_at->format('M d, Y h:i A') }}</td>
                                                        <td>
                                                            <span class="badge bg-warning text-dark">
                                                                {{ $billing->created_at->diffInDays(now()) }} days
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <button type="button" 
                                                                    class="btn btn-success btn-sm mark-as-paid-btn" 
                                                                    data-billing-id="{{ $billing->id }}"
                                                                    data-billing-number="{{ $billing->billing_number }}">
                                                                <i class="fas fa-check-circle"></i> Mark as Paid
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted py-4">
                                                            <i class="fas fa-check-circle fa-3x mb-3"></i>
                                                            <br>No pending payments! All billings are up to date.
                                                        </td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Paid Billings Tab -->
                                    <div class="tab-pane fade" id="paid-billings">
                                        <div class="table-responsive">
                                            <table class="table table-hover table-striped">
                                                <thead class="table-success">
                                                    <tr>
                                                        <th>Billing #</th>
                                                        <th>Patient</th>
                                                        <th>Net Amount</th>
                                                        <th>Payment Date</th>
                                                        <th>Time Since Payment</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($billings->where('status', 'paid') as $billing)
                                                    <tr>
                                                        <td><strong>{{ $billing->billing_number }}</strong></td>
                                                        <td>{{ $billing->patient->full_name ?? 'Unknown Patient' }}</td>
                                                        <td><strong class="text-success">₱{{ number_format($billing->net_amount ?? 0, 2) }}</strong></td>
                                                        <td>
                                                            @if($billing->payment_date)
                                                                {{ $billing->payment_date->format('M d, Y h:i A') }}
                                                            @else
                                                                <span class="text-muted">Unknown</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($billing->payment_date)
                                                                <span class="badge bg-success">
                                                                    {{ $billing->payment_date->diffForHumans() }}
                                                                </span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <button type="button" 
                                                                    class="btn btn-outline-secondary btn-sm mark-as-unpaid-btn" 
                                                                    data-billing-id="{{ $billing->id }}"
                                                                    data-billing-number="{{ $billing->billing_number }}">
                                                                <i class="fas fa-undo"></i> Revert
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted py-4">
                                                            <i class="fas fa-receipt fa-3x mb-3"></i>
                                                            <br>No payments recorded yet.
                                                        </td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    @include('cashier.modals.notification_system')

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Payment Processing Functions
    document.addEventListener('DOMContentLoaded', function() {
        // Mark as Paid buttons
        document.querySelectorAll('.mark-as-paid-btn').forEach(button => {
            button.addEventListener('click', function() {
                const billingId = this.dataset.billingId;
                markBillingAsPaid(billingId, this);
            });
        });
        
        // Mark as Unpaid buttons
        document.querySelectorAll('.mark-as-unpaid-btn').forEach(button => {
            button.addEventListener('click', function() {
                const billingId = this.dataset.billingId;
                markBillingAsUnpaid(billingId, this);
            });
        });
    });

    async function markBillingAsPaid(billingId, button) {
        const billingNumber = button.dataset.billingNumber;
        
        // Use the specialized payment confirmation dialog
        const confirmed = await confirmPaymentAction(
            `Mark billing ${billingNumber} as PAID?\n\nThis will:\n• Record the payment timestamp\n• Update the billing status to PAID\n• Complete the payment process`, 
            'Confirm Payment Processing'
        );
        
        if (!confirmed) return;
        
        try {
            // Show loading state
            showBillingLoading('Processing payment confirmation...');
            
            // Disable button to prevent double clicks
            button.disabled = true;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            
            const response = await fetch(`/cashier/billing/${billingId}/mark-as-paid`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Don't close the modal immediately, show success state
                showBillingNotification('success', 'Payment Processed', 
                    `Billing ${billingNumber} has been successfully marked as PAID. Click OK to refresh and see the updated status.`);
                
                // Wait for user to click OK before refreshing
                // The notification system will handle this automatically
            } else {
                closeBillingNotification();
                showBillingNotification('error', 'Payment Error', data.message);
                // Re-enable button on error
                button.disabled = false;
                button.innerHTML = originalText;
            }
        } catch (error) {
            closeBillingNotification();
            showBillingNotification('error', 'Network Error', 'Failed to process payment: ' + error.message);
            // Re-enable button on error
            button.disabled = false;
            button.innerHTML = originalText;
        }
    }

    async function markBillingAsUnpaid(billingId, button) {
        const billingNumber = button.dataset.billingNumber;
        
        // Use the specialized payment confirmation dialog
        const confirmed = await confirmPaymentAction(
            `Revert billing ${billingNumber} to UNPAID status?\n\nThis will:\n• Clear the payment timestamp\n• Change status back to PENDING\n• Require payment processing again`, 
            'Revert Payment Status'
        );
        
        if (!confirmed) return;
        
        try {
            // Show loading state
            showBillingLoading('Reverting payment status...');
            
            // Disable button to prevent double clicks
            button.disabled = true;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            
            const response = await fetch(`/cashier/billing/${billingId}/mark-as-unpaid`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Don't close the modal immediately, show success state
                showBillingNotification('success', 'Payment Status Reverted', 
                    `Billing ${billingNumber} has been reverted to UNPAID status. Click OK to refresh and see the updated status.`);
                
                // Wait for user to click OK before refreshing
                // The notification system will handle this automatically
            } else {
                closeBillingNotification();
                showBillingNotification('error', 'Revert Error', data.message);
                // Re-enable button on error
                button.disabled = false;
                button.innerHTML = originalText;
            }
        } catch (error) {
            closeBillingNotification();
            showBillingNotification('error', 'Network Error', 'Failed to revert payment status: ' + error.message);
            // Re-enable button on error
            button.disabled = false;
            button.innerHTML = originalText;
        }
    }
    </script>

    <style>
    /* Enhanced Styling for Cashier Billing */
    .main-content {
        padding: 20px;
    }

    .card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .card-header.bg-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
    }

    .table {
        margin-bottom: 0;
    }

    .table thead th {
        border-top: none;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.5em 0.75em;
    }

    .btn-group-vertical .btn {
        margin-bottom: 0.25rem;
    }

    .btn-group-vertical .btn:last-child {
        margin-bottom: 0;
    }

    .nav-tabs .nav-link {
        color: #6c757d;
        border: none;
        border-bottom: 2px solid transparent;
    }

    .nav-tabs .nav-link.active {
        background-color: transparent;
        border-bottom: 2px solid #28a745;
        color: #28a745;
        font-weight: 600;
    }

    .table-responsive {
        border-radius: 8px;
    }

    /* Statistics cards hover effect */
    .card {
        transition: transform 0.2s ease;
    }

    .card:hover {
        transform: translateY(-2px);
    }
    </style>
</body>
</html>
