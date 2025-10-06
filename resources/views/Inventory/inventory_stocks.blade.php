<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Inventory Stocks</title>
    <link rel="stylesheet" href="{{ url('css/inventorycss/inventory.css') }}">
    <link rel="stylesheet" href="{{ url('css/pagination.css') }}">
    <link rel="stylesheet" href="{{ url('css/inventorycss/add_stock_modal.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    @include('Inventory.inventory_header')

    <div class="inventory-layout">
        @include('Inventory.inventory_sidebar')

        <main class="main-content">
            @php $stocks = $stocks ?? collect(); $q = $q ?? ''; @endphp

            <div class="stocks-grid" style="display:grid;grid-template-columns:1fr 380px;gap:16px;align-items:start;">
                <div class="list-column">
                    <div class="inventory-card">
                        @if(!empty($dbError))
                            <div class="alert alert-danger">Database error: {{ Str::limit($dbError, 300) }}</div>
                        @endif

                        <div class="inventory-search">
                            <h2>Stocks</h2>
                            <form method="GET" class="search-form">
                                <input type="search" name="q" value="{{ $q }}" placeholder="Search items..." class="form-control" />
                                <button type="submit" class="search-btn"><i class="fas fa-search"></i> Search</button>
                                <button type="button" id="addStockBtn" class="add-stock-btn"><i class="fas fa-plus"></i> Add Stock</button>
                            </form>
                        </div>

                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        @if($stocks->count())
                            <div class="table-wrap">
                                <table class="stock-table" id="stocksTable">
                                    <thead>
                                        <tr>
                                            <th class="sortable" data-sort="0">Item Code <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                                            <th class="sortable" data-sort="1">Generic Name <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                                            <th class="sortable" data-sort="2">Brand <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                                            <th class="sortable" data-sort="3">Price <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                                            <th class="sortable" data-sort="4">Quantity <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($stocks as $s)
                                        <tr class="stock-row" data-stock='@json($s)'>
                                            <td>{{ $s->item_code }}</td>
                                            <td>{{ $s->generic_name }}</td>
                                            <td>{{ $s->brand_name }}</td>
                                            <td>{{ is_numeric($s->price) ? number_format($s->price,2) : '-' }}</td>
                                            <td>{{ $s->quantity ?? 0 }}</td>
                                            <td>
                                                <button type="button" class="action-btn btn-view js-open-stock">View</button>
                                                <button type="button" class="action-btn btn-delete delete-btn" data-id="{{ $s->id ?? $s->item_code }}">Delete</button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>                            
                        @else
                            <div class="alert alert-info">No stock items found.</div>
                        @endif
                    </div>
                </div>

                <div class="details-column">
                    <div class="details-card" id="detailsCard">
                        <div class="patients-header">
                            <h5>Stock Details</h5>
                            <div>
                                <button type="button" id="editStockBtn" class="action-btn btn-edit" style="display:none;">Edit</button>
                            </div>
                        </div>
                        <div id="detailsEmpty">Select an item to view details.</div>
                        <div id="detailsContent" style="display:none;">
                            <div class="details-item">
                                <div class="details-label">Item Code</div>
                                <div class="details-value" id="md-item_code">-</div>
                            </div>
                            <div class="details-item">
                                <div class="details-label">Generic Name</div>
                                <div class="details-value" id="md-generic_name">-</div>
                            </div>
                            <div class="details-item">
                                <div class="details-label">Brand Name</div>
                                <div class="details-value" id="md-brand_name">-</div>
                            </div>
                            <div class="details-item">
                                <div class="details-label">Price</div>
                                <div class="details-value" id="md-price">-</div>
                            </div>
                            <div class="details-item">
                                <div class="details-label">Quantity</div>
                                <div class="details-value" id="md-quantity">-</div>
                            </div>
                            <div class="details-item">
                                <div class="details-label">Reorder Level</div>
                                <div class="details-value" id="md-reorder_level">-</div>
                            </div>
                            <div class="details-item">
                                <div class="details-label">Expiry Date</div>
                                <div class="details-value" id="md-expiry_date">-</div>
                            </div>
                            <div class="details-item">
                                <div class="details-label">Supplier</div>
                                <div class="details-value" id="md-supplier">-</div>
                            </div>
                            <div class="details-item">
                                <div class="details-label">Batch Number</div>
                                <div class="details-value" id="md-batch_number">-</div>
                            </div>
                            <div class="details-item">
                                <div class="details-label">Date Received</div>
                                <div class="details-value" id="md-date_received">-</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Pagination -->
            @if($stocks->hasPages())
            <div class="inventory-pagination">
                {{ $stocks->appends(['q' => $q])->links('components.custom-pagination') }}
            </div>
            @endif
        </main>
    </div>

    <!-- Include modals from separate files -->
    @include('Inventory.modals.edit_stock_modal')
    @include('Inventory.modals.add_stock_modal')
    @include('Inventory.modals.notification_system')
    @include('Inventory.modals.inventory_scripts')


</body>
</html>
