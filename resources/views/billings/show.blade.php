@extends('layouts.billing')

@section('title', 'Billing Details - ' . $billing->billing_number)

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-file-invoice text-primary"></i> Billing Details</h2>
                <div class="btn-group">
                    <a href="{{ route('billings.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Billings
                    </a>
                    <a href="{{ route('billings.edit', $billing) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="{{ route('billing.export.receipt', $billing) }}" class="btn btn-success" target="_blank">
                        <i class="fas fa-file-pdf"></i> Export Receipt
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <!-- Billing Information -->
                <div class="col-md-8">
                    <div class="card shadow mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-info-circle"></i> Billing Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Billing Number:</strong></td>
                                            <td>{{ $billing->billing_number }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Patient:</strong></td>
                                            <td>{{ $billing->patient->firstName }} {{ $billing->patient->lastName }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Date of Birth:</strong></td>
                                            <td>{{ $billing->patient->dateOfBirth }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Billing Date:</strong></td>
                                            <td>{{ $billing->billing_date->format('M d, Y g:i A') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Created By:</strong></td>
                                            <td>{{ $billing->createdBy->name }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                @if($billing->status === 'pending')
                                                    <span class="badge bg-warning fs-6">Pending</span>
                                                @elseif($billing->status === 'paid')
                                                    <span class="badge bg-success fs-6">Paid</span>
                                                @elseif($billing->status === 'cancelled')
                                                    <span class="badge bg-danger fs-6">Cancelled</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>PhilHealth Member:</strong></td>
                                            <td>
                                                @if($billing->is_philhealth_member)
                                                    <span class="badge bg-info">Yes</span>
                                                @else
                                                    <span class="badge bg-secondary">No</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Senior Citizen:</strong></td>
                                            <td>
                                                @if($billing->is_senior_citizen)
                                                    <span class="badge bg-warning">Yes</span>
                                                @else
                                                    <span class="badge bg-secondary">No</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>PWD:</strong></td>
                                            <td>
                                                @if($billing->is_pwd)
                                                    <span class="badge bg-warning">Yes</span>
                                                @else
                                                    <span class="badge bg-secondary">No</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            @if($billing->notes)
                                <div class="mt-3">
                                    <h6><strong>Notes:</strong></h6>
                                    <p class="text-muted">{{ $billing->notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Billing Items -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-list-ul"></i> Billing Items</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Type</th>
                                            <th>Description</th>
                                            <th>ICD-10</th>
                                            <th>Qty</th>
                                            <th>Unit Price</th>
                                            <th>Total</th>
                                            <th>Date Charged</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($billing->billingItems as $item)
                                        <tr>
                                            <td>
                                                <span class="badge 
                                                    @if($item->item_type === 'room') bg-primary
                                                    @elseif($item->item_type === 'medicine') bg-success
                                                    @elseif($item->item_type === 'laboratory') bg-info
                                                    @elseif($item->item_type === 'professional') bg-warning
                                                    @else bg-secondary
                                                    @endif">
                                                    {{ $item->getFormattedItemType() }}
                                                </span>
                                            </td>
                                            <td>{{ $item->description }}</td>
                                            <td>
                                                @if($item->icd_code)
                                                    <code>{{ $item->icd_code }}</code>
                                                    @php
                                                        $icdData = $item->icd10NamePriceRate();
                                                    @endphp
                                                    @if($icdData)
                                                        <br><small class="text-muted">{{ Str::limit($icdData->description, 30) }}</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>₱{{ number_format($item->unit_price, 2) }}</td>
                                            <td><strong>₱{{ number_format($item->total_amount, 2) }}</strong></td>
                                            <td>{{ $item->date_charged->format('M d, Y') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Billing Summary -->
                <div class="col-md-4">
                    <div class="card shadow mb-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-calculator"></i> Billing Summary</h5>
                        </div>
                        <div class="card-body">
                            <!-- Breakdown by Category -->
                            <div class="mb-4">
                                <h6 class="text-muted mb-3">Charges Breakdown</h6>
                                <div class="row mb-2">
                                    <div class="col">Room Charges:</div>
                                    <div class="col-auto">₱{{ number_format($billing->room_charges, 2) }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col">Professional Fees:</div>
                                    <div class="col-auto">₱{{ number_format($billing->professional_fees, 2) }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col">Medicine Charges:</div>
                                    <div class="col-auto">₱{{ number_format($billing->medicine_charges, 2) }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col">Laboratory Charges:</div>
                                    <div class="col-auto">₱{{ number_format($billing->lab_charges, 2) }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col">Other Charges:</div>
                                    <div class="col-auto">₱{{ number_format($billing->other_charges, 2) }}</div>
                                </div>
                                <hr>
                            </div>

                            <!-- Total Calculation -->
                            <div class="mb-3">
                                <div class="row mb-2">
                                    <div class="col"><strong>Subtotal:</strong></div>
                                    <div class="col-auto"><strong>₱{{ number_format($billing->total_amount, 2) }}</strong></div>
                                </div>
                                
                                @if($billing->is_philhealth_member)
                                    <div class="row mb-2 text-success">
                                        <div class="col">PhilHealth Deduction:</div>
                                        <div class="col-auto">-₱{{ number_format($billing->philhealth_deduction, 2) }}</div>
                                    </div>
                                @endif

                                @if($billing->senior_pwd_discount > 0)
                                    <div class="row mb-2 text-success">
                                        <div class="col">
                                            @if($billing->is_senior_citizen && $billing->is_pwd)
                                                Senior & PWD Discount:
                                            @elseif($billing->is_senior_citizen)
                                                Senior Citizen Discount:
                                            @else
                                                PWD Discount:
                                            @endif
                                        </div>
                                        <div class="col-auto">-₱{{ number_format($billing->senior_pwd_discount, 2) }}</div>
                                    </div>
                                @endif
                                
                                <hr class="my-3">
                                
                                <div class="row">
                                    <div class="col"><h5><strong>Net Amount:</strong></h5></div>
                                    <div class="col-auto"><h5 class="text-primary"><strong>₱{{ number_format($billing->net_amount, 2) }}</strong></h5></div>
                                </div>
                            </div>

                            <!-- Savings Summary -->
                            @if($billing->philhealth_deduction > 0 || $billing->senior_pwd_discount > 0)
                                <div class="alert alert-success">
                                    <h6 class="mb-2"><i class="fas fa-piggy-bank"></i> Total Savings</h6>
                                    <h5 class="mb-0 text-success">₱{{ number_format($billing->philhealth_deduction + $billing->senior_pwd_discount, 2) }}</h5>
                                    <small class="text-muted">
                                        {{ number_format((($billing->philhealth_deduction + $billing->senior_pwd_discount) / $billing->total_amount) * 100, 1) }}% 
                                        of total charges
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card shadow">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('billings.edit', $billing) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Edit Billing
                                </a>
                                <a href="{{ route('billing.export.receipt', $billing) }}" class="btn btn-success" target="_blank">
                                    <i class="fas fa-file-pdf"></i> Download Receipt
                                </a>
                                @if($billing->status === 'pending')
                                    <form action="{{ route('billings.update', $billing) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="paid">
                                        <input type="hidden" name="professional_fees" value="{{ $billing->professional_fees }}">
                                        <input type="hidden" name="is_senior_citizen" value="{{ $billing->is_senior_citizen }}">
                                        <input type="hidden" name="is_pwd" value="{{ $billing->is_pwd }}">
                                        <input type="hidden" name="notes" value="{{ $billing->notes }}">
                                        <button type="button" class="btn btn-primary w-100" onclick="handlePaymentConfirmation(this)">
                                            <i class="fas fa-check"></i> Mark as Paid
                                        </button>
                                    </form>
                                @endif
                                <button class="btn btn-info" onclick="window.print()">
                                    <i class="fas fa-print"></i> Print
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
@media print {
    .btn, .card-header, .alert, .navbar, .sidebar {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .container-fluid {
        padding: 0 !important;
    }
}
</style>

@include('billings.modals.notification_system')

<script>
// Handle payment confirmation with notification system
async function handlePaymentConfirmation(button) {
    const confirmed = await confirmBillingAction(
        'Mark this billing as paid? This will update the payment status and cannot be undone.',
        'Confirm Payment'
    );
    
    if (confirmed) {
        showBillingLoading('Processing payment...');
        button.closest('form').submit();
    }
}

// Show notifications for session messages
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
        showBillingNotification('success', 'Success', '{{ session('success') }}');
    @endif
    
    @if(session('error'))
        showBillingNotification('error', 'Error', '{{ session('error') }}');
    @endif
});
</script>

@endsection