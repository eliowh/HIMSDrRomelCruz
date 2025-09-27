<!-- Out of Stock Report -->
<div class="report-content">
    @if(isset($data) && $data->count() > 0)
        <div class="report-card">
            <div class="report-card-header">
                <h3><i class="fas fa-times-circle"></i> Out of Stock Items</h3>
                <p>Items that are completely out of stock and need immediate restocking.</p>
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
                        <th>Last Updated</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $item)
                    <tr class="out-of-stock-row">
                        <td><strong>{{ $item->item_code }}</strong></td>
                        <td>{{ $item->generic_name ?: '-' }}</td>
                        <td>{{ $item->brand_name ?: '-' }}</td>
                        <td>
                            <span class="quantity-badge danger">
                                {{ number_format($item->quantity) }}
                            </span>
                        </td>
                        <td>{{ number_format($item->reorder_level) }}</td>
                        <td>₱{{ number_format($item->price, 2) }}</td>
                        <td>{{ $item->updated_at->format('M d, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
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
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3>No Out of Stock Items</h3>
                    <p>All items are currently in stock!</p>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
.report-header.danger {
    background: #f8d7da;
    border-bottom: 1px solid #f1aeb5;
}
.report-header.danger h3,
.report-header.danger p {
    color: #721c24;
}
.quantity-badge.danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f1aeb5;
}
.out-of-stock-row:hover {
    background: #f8f9fa;
}
</style>