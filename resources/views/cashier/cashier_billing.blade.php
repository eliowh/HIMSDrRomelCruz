<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Payment Management - Cashier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
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
                             <!-- Search and Filter Form -->
                        <form method="GET" action="{{ url('/cashier/billing') }}" class="mb-4">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="input-group">
                                                <input type="text" name="search" class="form-control" placeholder="Search by billing number, patient name, or patient number..." value="{{ request('search') }}">
                                                <button class="btn btn-outline-success" type="submit">
                                                    <i class="fas fa-search"></i> Search
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <select name="status" class="form-select">
                                                <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Payment</option>
                                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="d-flex gap-2">
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fas fa-filter"></i> Filter
                                                </button>
                                                <a href="{{ url('/cashier/billing') }}" class="btn btn-outline-secondary">
                                                    <i class="fas fa-times"></i> Clear
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                            <div class="card-body">
                                <!-- Search Results Summary -->
                                @if(request('search') || (request('status') && request('status') !== 'all'))
                                    <div class="alert alert-info mb-3">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Search Results:</strong> Found {{ $billings->total() }} billing record(s)
                                        @if(request('search'))
                                            matching "{{ request('search') }}"
                                        @endif
                                        @if(request('status') && request('status') !== 'all')
                                            with status "{{ ucfirst(request('status')) }}"
                                        @endif
                                    </div>
                                @endif

                                <!-- Filter Tabs -->
                                <ul class="nav nav-tabs mb-3" id="billingTabs">
                                    <li class="nav-item">
                                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#all-billings">
                                            <i class="fas fa-list-alt"></i> All Billings ({{ $billings->total() }})
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
                                                                            class="btn btn-success btn-sm process-payment-btn" 
                                                                            title="Process Payment"
                                                                            data-billing-id="{{ $billing->id }}"
                                                                            data-billing-number="{{ $billing->billing_number }}"
                                                                            data-net-amount="{{ $billing->net_amount }}"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#paymentModal">
                                                                        <i class="fas fa-cash-register"></i> Process Payment
                                                                    </button>
                                                                @elseif($billing->status === 'paid')
                                                                    <span class="btn btn-outline-success btn-sm disabled">
                                                                        <i class="fas fa-check-circle"></i> Payment Complete
                                                                    </span>
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
                                                                {{ intval($billing->created_at->diffInDays(now())) }} days
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <button type="button" 
                                                                    class="btn btn-success btn-sm process-payment-btn" 
                                                                    data-billing-id="{{ $billing->id }}"
                                                                    data-billing-number="{{ $billing->billing_number }}"
                                                                    data-net-amount="{{ $billing->net_amount }}"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#paymentModal">
                                                                <i class="fas fa-cash-register"></i> Process Payment
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
                                                            <span class="btn btn-outline-success btn-sm disabled">
                                                                <i class="fas fa-check-circle"></i> Payment Complete
                                                            </span>
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
                        
                        <!-- Pagination -->
                        @if($billings->hasPages())
                            <div class="pagination-wrapper mt-4">
                                @include('components.custom-pagination', ['paginator' => $billings])
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>

    @include('cashier.modals.notification_system')

    <!-- Payment Processing Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="paymentModalLabel">
                        <i class="fas fa-cash-register"></i> Process Payment
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="paymentForm">
                        <div class="row mb-3">
                            <div class="col-sm-4">
                                <strong>Billing #:</strong>
                            </div>
                            <div class="col-sm-8">
                                <span id="modal-billing-number"></span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4">
                                <strong>Amount Due:</strong>
                            </div>
                            <div class="col-sm-8">
                                <span id="modal-net-amount" class="text-primary fw-bold fs-5"></span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="paymentAmount" class="form-label">
                                <i class="fas fa-money-bill-wave"></i> Payment Amount Received <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" 
                                       class="form-control" 
                                       id="paymentAmount" 
                                       name="payment_amount" 
                                       step="0.01" 
                                       min="0"
                                       max="999999.99"
                                       placeholder="0.00" 
                                       required>
                            </div>
                            <div class="form-text">Enter the exact amount received from the customer</div>
                        </div>
                        <div class="mb-3" id="changeDisplay" style="display: none;">
                            <div class="alert alert-info">
                                <strong><i class="fas fa-exchange-alt"></i> Change to Return:</strong>
                                <span id="changeAmount" class="fw-bold fs-5"></span>
                            </div>
                        </div>
                        <div id="paymentError" class="alert alert-danger d-none"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-success" id="confirmPaymentBtn">
                        <i class="fas fa-check-circle"></i> Confirm Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Payment Processing Functions
    document.addEventListener('DOMContentLoaded', function() {
        let currentBillingId = null;
        let currentNetAmount = 0;
        
        // Process Payment buttons
        document.querySelectorAll('.process-payment-btn').forEach(button => {
            button.addEventListener('click', function() {
                currentBillingId = this.dataset.billingId;
                currentNetAmount = parseFloat(this.dataset.netAmount);
                
                document.getElementById('modal-billing-number').textContent = this.dataset.billingNumber;
                document.getElementById('modal-net-amount').textContent = '₱' + currentNetAmount.toFixed(2);
                
                // Reset form
                document.getElementById('paymentAmount').value = '';
                document.getElementById('changeDisplay').style.display = 'none';
                document.getElementById('paymentError').classList.add('d-none');
            });
        });
        
        // Payment amount input change handler
        document.getElementById('paymentAmount').addEventListener('input', function() {
            const paymentInput = this.value;
            const paymentAmount = parseFloat(paymentInput) || 0;
            
            // Clear previous errors
            document.getElementById('paymentError').classList.add('d-none');
            document.getElementById('changeDisplay').style.display = 'none';
            
            if (paymentInput && paymentInput.trim() !== '') {
                if (isNaN(paymentAmount)) {
                    document.getElementById('paymentError').textContent = 'Please enter a valid number.';
                    document.getElementById('paymentError').classList.remove('d-none');
                    return;
                }
                
                if (paymentAmount > 999999.99) {
                    document.getElementById('paymentError').textContent = 'Amount too large. Maximum: ₱999,999.99';
                    document.getElementById('paymentError').classList.remove('d-none');
                    return;
                }
                
                if (paymentAmount > 0) {
                    const changeAmount = paymentAmount - currentNetAmount;
                    
                    if (changeAmount >= 0) {
                        document.getElementById('changeAmount').textContent = '₱' + changeAmount.toFixed(2);
                        document.getElementById('changeDisplay').style.display = 'block';
                    } else {
                        const shortfall = Math.abs(changeAmount);
                        document.getElementById('paymentError').textContent = 'Insufficient payment. Short by ₱' + shortfall.toFixed(2);
                        document.getElementById('paymentError').classList.remove('d-none');
                    }
                }
            }
        });
        
        // Confirm payment button
        document.getElementById('confirmPaymentBtn').addEventListener('click', async function() {
            const paymentInput = document.getElementById('paymentAmount').value;
            const paymentAmount = parseFloat(paymentInput);
            
            // Validate input
            if (!paymentInput || paymentInput.trim() === '') {
                document.getElementById('paymentError').textContent = 'Please enter a payment amount.';
                document.getElementById('paymentError').classList.remove('d-none');
                return;
            }
            
            if (isNaN(paymentAmount) || paymentAmount <= 0) {
                document.getElementById('paymentError').textContent = 'Please enter a valid payment amount.';
                document.getElementById('paymentError').classList.remove('d-none');
                return;
            }
            
            if (paymentAmount > 999999.99) {
                document.getElementById('paymentError').textContent = 'Payment amount is too large. Maximum allowed is ₱999,999.99.';
                document.getElementById('paymentError').classList.remove('d-none');
                return;
            }
            
            if (paymentAmount < currentNetAmount) {
                document.getElementById('paymentError').textContent = 'Payment amount is insufficient.';
                document.getElementById('paymentError').classList.remove('d-none');
                return;
            }
            
            await processPayment(currentBillingId, paymentAmount);
        });
    });

    async function processPayment(billingId, paymentAmount) {
        try {
            // Show loading state
            showBillingLoading('Processing payment...');
            
            const response = await fetch(`/cashier/billing/${billingId}/mark-as-paid`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    payment_amount: paymentAmount
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Close payment modal
                const paymentModal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
                paymentModal.hide();
                
                closeBillingNotification();
                
                let message = 'Payment processed successfully!';
                if (data.change > 0) {
                    message += `\n\nChange to return: ${data.change_formatted}`;
                }
                
                showBillingNotification('success', 'Payment Complete', message);
                
                // Auto refresh after 2 seconds
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                closeBillingNotification();
                showBillingNotification('error', 'Payment Error', data.message);
            }
        } catch (error) {
            closeBillingNotification();
            showBillingNotification('error', 'Network Error', 'Failed to process payment: ' + error.message);
        }
    }

    // Unpaid functionality removed for security - preventing payment theft
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

    <!-- Enhanced Search JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-submit form when status filter changes
        const statusSelect = document.querySelector('select[name="status"]');
        if (statusSelect) {
            statusSelect.addEventListener('change', function() {
                this.closest('form').submit();
            });
        }

        // Add Enter key submit for search input
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    this.closest('form').submit();
                }
            });

            // Add search icon animation
            searchInput.addEventListener('focus', function() {
                const searchBtn = this.parentElement.querySelector('button');
                if (searchBtn) {
                    searchBtn.classList.add('btn-success');
                    searchBtn.classList.remove('btn-outline-success');
                }
            });

            searchInput.addEventListener('blur', function() {
                const searchBtn = this.parentElement.querySelector('button');
                if (searchBtn && !this.value) {
                    searchBtn.classList.remove('btn-success');
                    searchBtn.classList.add('btn-outline-success');
                }
            });
        }

        // Highlight search terms in results
        const searchTerm = '{{ request("search") }}';
        if (searchTerm) {
            highlightSearchTerm(searchTerm);
        }
    });

    function highlightSearchTerm(term) {
        if (!term) return;
        
        const tables = document.querySelectorAll('table tbody');
        tables.forEach(table => {
            const cells = table.querySelectorAll('td');
            cells.forEach(cell => {
                if (cell.innerHTML && typeof cell.innerHTML === 'string') {
                    const regex = new RegExp(`(${term})`, 'gi');
                    cell.innerHTML = cell.innerHTML.replace(regex, '<mark class="bg-warning">$1</mark>');
                }
            });
        });
    }
    </script>
</body>
</html>
