@extends('layouts.billing')

@section('title', 'Billing Management')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-file-invoice-dollar text-primary"></i> Billing Management</h2>
                <a href="{{ route('billing.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create New Billing
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Patient Billings</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-primary">
                                <tr>
                                    <th class="text-black">Billing #</th>
                                    <th class="text-black">Patient</th>
                                    <th class="text-black">Total Amount</th>
                                    <th class="text-black">PhilHealth</th>
                                    <th class="text-black">Discount</th>
                                    <th class="text-black">Net Amount</th>
                                    <th class="text-black">Status</th>
                                    <th class="text-black">Date</th>
                                    <th class="text-black">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($billings as $billing)
                                <tr>
                                    <td>
                                        <strong class="text-primary">{{ $billing->billing_number }}</strong>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $billing->patient->display_name }}</strong>
                                            @if($billing->is_philhealth_member)
                                                <span class="badge bg-info ms-1">PhilHealth</span>
                                            @endif
                                            @if($billing->is_senior_citizen)
                                                <span class="badge bg-warning ms-1">Senior</span>
                                            @endif
                                            @if($billing->is_pwd)
                                                <span class="badge bg-warning ms-1">PWD</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <strong>₱{{ number_format($billing->total_amount ?? 0, 2) }}</strong>
                                    </td>
                                    <td>
                                        @if($billing->is_philhealth_member)
                                            <span class="text-success">-₱{{ number_format($billing->philhealth_deduction ?? 0, 2) }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($billing->senior_pwd_discount > 0)
                                            <span class="text-success">-₱{{ number_format($billing->senior_pwd_discount ?? 0, 2) }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong class="text-dark">₱{{ number_format($billing->net_amount ?? 0, 2) }}</strong>
                                    </td>
                                    <td>
                                        @if($billing->status === 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($billing->status === 'paid')
                                            <span class="badge bg-success">Paid</span>
                                        @elseif($billing->status === 'cancelled')
                                            <span class="badge bg-danger">Cancelled</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $billing->billing_date->format('M d, Y') }}
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('billing.show', $billing) }}" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('billing.edit', $billing) }}" 
                                               class="btn btn-sm btn-outline-warning" 
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            

                                            
                                            <a href="{{ route('billing.export.receipt', $billing) }}" 
                                               class="btn btn-sm btn-outline-success" 
                                               title="Export Receipt" 
                                               target="_blank">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                            <form action="{{ route('billing.destroy', $billing) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return handleBillingDelete(event, this)">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3"></i>
                                            <p>No billing records found.</p>
                                            <a href="{{ route('billing.create') }}" class="btn btn-primary">
                                                Create First Billing
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $billings->links() }}
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5>Total Billings</h5>
                                    <h3>{{ $billings->total() }}</h3>
                                </div>
                                <i class="fas fa-file-invoice-dollar fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5>Paid Bills</h5>
                                    <h3>{{ $billings->where('status', 'paid')->count() }}</h3>
                                </div>
                                <i class="fas fa-check-circle fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5>Pending Bills</h5>
                                    <h3>{{ $billings->where('status', 'pending')->count() }}</h3>
                                </div>
                                <i class="fas fa-clock fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5>PhilHealth Members</h5>
                                    <h3>{{ $billings->where('is_philhealth_member', true)->count() }}</h3>
                                </div>
                                <i class="fas fa-shield-alt fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('billing.modals.notification_system')

<script>
// Handle billing deletion with notification system
async function handleBillingDelete(event, form) {
    event.preventDefault();
    
    const confirmed = await confirmDeleteAction(
        'This billing record will be permanently deleted along with:\n• All billing items and charges\n• Payment history\n• Associated reports\n\nThis action cannot be undone!', 
        'Delete Billing Record'
    );
    
    if (confirmed) {
        showBillingLoading('Deleting billing record...');
        form.submit();
    }
    
    return false;
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

@section('styles')
<style>
/* Billing Card & Table Enhancements */
.table-primary > th {
    background-color: #0d6efd !important;
    border-color: #0d6efd !important;
}

/* Ensure tbody text is ALWAYS dark on light backgrounds */
.table tbody td {
    color: #212529 !important;
    background-color: rgba(255, 255, 255, 0.9) !important;
}

.table-striped > tbody > tr:nth-of-type(odd) > td {
    background-color: rgba(0, 0, 0, 0.05) !important;
    color: #212529 !important;
}

/* Card shadow enhancement */
.card.shadow {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

/* Updated card header gradients */
.card-header.bg-primary {
    background: linear-gradient(135deg, #367F2B, #2d6624) !important;
}
</style>
@endsection