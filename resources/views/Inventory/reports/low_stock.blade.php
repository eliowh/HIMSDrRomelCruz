<!-- Low Stock Report -->
<div class="report-content">
    @if(isset($data) && $data->count() > 0)
        <div class="report-card">
            <div class="report-card-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Low Stock Items</h3>
                <p>Items that are at or below their reorder level but still have stock remaining.</p>
            </div>
            <div class="report-card-body">
                <div class="table-responsive">
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>Item Code</th>
                        <th>Generic Name</th>
                        <th>Brand Name</th>
                        <th>Current Stock</th>
                        <th>Reorder Level</th>
                        <th>Unit Price</th>
                        <th>Total Value</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $item)
                    <tr class="low-stock-row">
                        <td><strong>{{ $item->item_code }}</strong></td>
                        <td>{{ $item->generic_name ?: '-' }}</td>
                        <td>{{ $item->brand_name ?: '-' }}</td>
                        <td>
                            <span class="quantity-badge low">
                                {{ number_format($item->quantity) }}
                            </span>
                        </td>
                        <td>{{ number_format($item->reorder_level) }}</td>
                        <td>₱{{ number_format($item->price, 2) }}</td>
                        <td>₱{{ number_format($item->quantity * $item->price, 2) }}</td>
                        <td>
                            <span class="status-badge warning">
                                @if($item->quantity <= 0)
                                    Out of Stock
                                @elseif($item->quantity <= $item->reorder_level / 2)
                                    Critical
                                @else
                                    Low Stock
                                @endif
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                </table>
                </div>
            </div>
            
            <!-- Pagination -->
            @if($data->hasPages())
            <div class="report-card-footer">
                {{ $data->appends(request()->query())->links('components.custom-pagination') }}
            </div>
            @endif
        </div>

    @else
        <div class="report-card">
            <div class="report-card-body">
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3>No Low Stock Items</h3>
                    <p>All items are above their reorder levels. Great job!</p>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
.report-header {
    padding: 1.5rem;
    background: #fff3cd;
    border-bottom: 1px solid #ffeaa7;
}

.report-header h3 {
    margin: 0 0 0.5rem 0;
    color: #856404;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.report-header p {
    margin: 0;
    color: #856404;
    font-size: 0.9rem;
}

.reports-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
    background: white;
}

.reports-table th,
.reports-table td {
    padding: 0.75rem 0.5rem;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
}

.reports-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
    position: sticky;
    top: 0;
}

.low-stock-row:hover {
    background: #f8f9fa;
}

.quantity-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-weight: 600;
    font-size: 0.8rem;
}

.quantity-badge.low {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-weight: 500;
    font-size: 0.8rem;
}

.status-badge.warning {
    background: #fff3cd;
    color: #856404;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    background: white;
}

.empty-state .empty-icon {
    font-size: 3rem;
    color: #28a745;
    margin-bottom: 1rem;
}

.empty-state h3 {
    color: #333;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #666;
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .reports-table {
        font-size: 0.8rem;
    }
    
    .reports-table th,
    .reports-table td {
        padding: 0.5rem 0.25rem;
    }
}
</style>