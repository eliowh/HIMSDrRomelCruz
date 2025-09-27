<!-- Stock Movement Report -->
<div class="report-content">
    @if(isset($data) && $data->count() > 0)
        <div class="report-card">
            <div class="report-card-header">
                <h3><i class="fas fa-exchange-alt"></i> Stock Movement (Last 30 Days)</h3>
                <p>Recent completed stock orders showing inventory movement patterns.</p>
            </div>
            <div class="report-card-body">
                <div class="table-responsive">
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Item</th>
                        <th>Requested By</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total Value</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $order)
                    <tr class="movement-row">
                        <td><strong>#{{ $order->id }}</strong></td>
                        <td>{{ $order->requested_at ? $order->requested_at->format('M d, Y') : '-' }}</td>
                        <td>
                            <div class="item-info">
                                <strong>{{ $order->generic_name ?: $order->item_code }}</strong>
                                @if($order->brand_name)
                                    <small>{{ $order->brand_name }}</small>
                                @endif
                            </div>
                        </td>
                        <td>{{ $order->user->name ?? 'Unknown' }}</td>
                        <td>{{ number_format($order->quantity) }}</td>
                        <td>₱{{ number_format($order->unit_price, 2) }}</td>
                        <td>₱{{ number_format($order->total_price, 2) }}</td>
                        <td>
                            <span class="status-badge completed">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="summary-row">
                        <td colspan="6"><strong>Total Movement Value:</strong></td>
                        <td><strong>₱{{ number_format($data->sum('total_price'), 2) }}</strong></td>
                        <td></td>
                    </tr>
                </tfoot>
                </table>
                </div>
            </div>
            
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
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <h3>No Stock Movement</h3>
                    <p>No completed stock orders in the last 30 days.</p>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
.report-header.info {
    background: #d1ecf1;
    border-bottom: 1px solid #bee5eb;
}
.report-header.info h3,
.report-header.info p {
    color: #0c5460;
}
.movement-row:hover {
    background: #f8f9fa;
}
.status-badge.completed {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
.summary-row {
    background: #f8f9fa;
    font-weight: 600;
}
.summary-row td {
    border-top: 2px solid #dee2e6;
}
.item-info strong {
    display: block;
    color: #333;
}
.item-info small {
    display: block;
    color: #666;
    font-size: 0.8rem;
}
</style>