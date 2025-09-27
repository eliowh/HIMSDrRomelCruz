<!-- Expiring Items Report -->
<div class="report-content">
    @if(isset($data) && $data->count() > 0)
        <div class="report-card">
            <div class="report-card-header">
                <h3><i class="fas fa-clock"></i> Items Expiring Soon</h3>
                <p>Items that will expire within the next 30 days.</p>
            </div>
            <div class="report-card-body">
                <div class="filter-options mb-3">
                    <a href="{{ route('inventory.reports', ['type' => 'expiring', 'days' => 7]) }}" 
                       class="filter-btn {{ request('days') == 7 ? 'active' : '' }}">7 Days</a>
                    <a href="{{ route('inventory.reports', ['type' => 'expiring', 'days' => 15]) }}" 
                       class="filter-btn {{ request('days') == 15 ? 'active' : '' }}">15 Days</a>
                    <a href="{{ route('inventory.reports', ['type' => 'expiring', 'days' => 30]) }}" 
                       class="filter-btn {{ !request('days') || request('days') == 30 ? 'active' : '' }}">30 Days</a>
                </div>
                
                <div class="table-responsive">
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>Item Code</th>
                        <th>Generic Name</th>
                        <th>Brand Name</th>
                        <th>Current Stock</th>
                        <th>Expiry Date</th>
                        <th>Days Until Expiry</th>
                        <th>Total Value at Risk</th>
                        <th>Priority</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $item)
                    <tr class="expiring-row">
                        <td><strong>{{ $item->item_code }}</strong></td>
                        <td>{{ $item->generic_name ?: '-' }}</td>
                        <td>{{ $item->brand_name ?: '-' }}</td>
                        <td>{{ number_format($item->quantity) }}</td>
                        <td>{{ $item->expiry_date ? $item->expiry_date->format('M d, Y') : '-' }}</td>
                        <td>
                            @if($item->expiry_date)
                                @php
                                    $daysUntilExpiry = now()->diffInDays($item->expiry_date, false);
                                @endphp
                                <span class="days-badge {{ $daysUntilExpiry <= 7 ? 'critical' : ($daysUntilExpiry <= 15 ? 'warning' : 'normal') }}">
                                    {{ $daysUntilExpiry }} days
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td>₱{{ number_format($item->quantity * $item->price, 2) }}</td>
                        <td>
                            @if($item->expiry_date)
                                @php
                                    $daysUntilExpiry = now()->diffInDays($item->expiry_date, false);
                                @endphp
                                @if($daysUntilExpiry <= 7)
                                    <span class="priority-badge critical">Critical</span>
                                @elseif($daysUntilExpiry <= 15)
                                    <span class="priority-badge high">High</span>
                                @else
                                    <span class="priority-badge medium">Medium</span>
                                @endif
                            @endif
                        </td>
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
                    <h3>No Items Expiring Soon</h3>
                    <p>No items are expiring within the selected timeframe.</p>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
.report-header.warning {
    background: #fff3cd;
    border-bottom: 1px solid #ffeaa7;
}
.report-header.warning h3,
.report-header.warning p {
    color: #856404;
}
.filter-options {
    margin-top: 1rem;
    display: flex;
    gap: 0.5rem;
}
.filter-btn {
    padding: 0.5rem 1rem;
    background: white;
    border: 1px solid #ffeaa7;
    border-radius: 4px;
    text-decoration: none;
    color: #856404;
    font-size: 0.9rem;
}
.filter-btn:hover,
.filter-btn.active {
    background: #856404;
    color: white;
}
.days-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-weight: 600;
    font-size: 0.8rem;
}
.days-badge.critical {
    background: #f8d7da;
    color: #721c24;
}
.days-badge.warning {
    background: #fff3cd;
    color: #856404;
}
.days-badge.normal {
    background: #d1ecf1;
    color: #0c5460;
}
.priority-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-weight: 500;
    font-size: 0.8rem;
}
.priority-badge.critical {
    background: #dc3545;
    color: white;
}
.priority-badge.high {
    background: #ffc107;
    color: #212529;
}
.priority-badge.medium {
    background: #17a2b8;
    color: white;
}
</style>