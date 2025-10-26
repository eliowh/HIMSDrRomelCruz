<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Stocks</title>
    <link rel="stylesheet" href="{{ asset('css/pharmacycss/pharmacy.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    @php
        $pharmacyName = auth()->user()->name ?? 'Pharmacy Staff';
    @endphp
    @include('pharmacy.pharmacy_header')

    <div class="pharmacy-layout">
        @include('pharmacy.pharmacy_sidebar')

        <main class="main-content">
            @php $stockspharmacy = $stockspharmacy ?? collect(); $q = $q ?? ''; @endphp

            <div class="stocks-grid" style="display:grid;grid-template-columns:1fr 380px;gap:16px;align-items:start;">
                <div class="list-column">
                    <div class="pharmacy-card">
                        @if(!empty($dbError))
                            <div class="alert alert-danger">Database error: {{ Str::limit($dbError, 300) }}</div>
                        @endif

                        <div class="pharmacy-search">
                            <h2>Pharmacy Stocks</h2>
                            <form method="GET" class="search-form">
                                <input type="search" name="q" value="{{ $q }}" placeholder="Search stocks..." class="form-control" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; margin-right: 8px;" />
                                <button type="submit" class="search-btn" style="padding: 8px 16px; background: #367F2B; color: white; border: none; border-radius: 4px; cursor: pointer;"><i class="fas fa-search"></i> Search</button>
                            </form>
                        </div>

                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        @if($stockspharmacy->count())
                            <div class="table-wrap">
                                <table class="orders-table" id="stocksTable" style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr>
                                            <th class="sortable" data-sort="0" style="padding: 12px; text-align: left; border-bottom: 1px solid #eee; background: #f8f9fa; font-weight: 600; color: #1a4931;">Item Code <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                                            <th class="sortable" data-sort="1" style="padding: 12px; text-align: left; border-bottom: 1px solid #eee; background: #f8f9fa; font-weight: 600; color: #1a4931;">Generic Name <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                                            <th class="sortable" data-sort="2" style="padding: 12px; text-align: left; border-bottom: 1px solid #eee; background: #f8f9fa; font-weight: 600; color: #1a4931;">Brand Name <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                                            <th class="sortable" data-sort="3" style="padding: 12px; text-align: left; border-bottom: 1px solid #eee; background: #f8f9fa; font-weight: 600; color: #1a4931;">Price <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                                            <th class="sortable" data-sort="4" style="padding: 12px; text-align: left; border-bottom: 1px solid #eee; background: #f8f9fa; font-weight: 600; color: #1a4931;">Quantity <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                                            <th style="padding: 12px; text-align: left; border-bottom: 1px solid #eee; background: #f8f9fa; font-weight: 600; color: #1a4931;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($stockspharmacy as $s)
                                        <tr class="stock-row order-row" data-stock='@json($s)' style="transition: background-color 0.2s;">
                                            <td style="padding: 12px; text-align: left; border-bottom: 1px solid #eee;">{{ $s->item_code }}</td>
                                            <td style="padding: 12px; text-align: left; border-bottom: 1px solid #eee;">{{ $s->generic_name }}</td>
                                            <td style="padding: 12px; text-align: left; border-bottom: 1px solid #eee;">{{ $s->brand_name }}</td>
                                            <td style="padding: 12px; text-align: left; border-bottom: 1px solid #eee;">₱{{ is_numeric($s->price) ? number_format($s->price,2) : '0.00' }}</td>
                                            <td style="padding: 12px; text-align: left; border-bottom: 1px solid #eee;">{{ $s->quantity ?? 0 }}</td>
                                            <td style="padding: 12px; text-align: left; border-bottom: 1px solid #eee;">
                                                <button type="button" class="pharmacy-btn-primary btn-sm js-open-stock">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>                            
                        @else
                            <div class="alert alert-info" style="padding: 15px; background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; border-radius: 4px;">No stock items found.</div>
                        @endif
                    </div>
                </div>

                <div class="details-column">
                    <div class="pharmacy-card details-card" id="detailsCard">
                        <div class="patients-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #eee;">
                            <h5>Stock Details</h5>
                        </div>
                        <div id="detailsEmpty" style="padding: 30px; text-align: center; color: #999;">Select an item to view details.</div>
                        <div id="detailsContent" style="display:none;">
                            <div class="details-item" style="margin-bottom: 12px; display: flex; justify-content: space-between;">
                                <div class="details-label" style="font-weight: 600; color: #1a4931;">Item Code</div>
                                <div class="details-value" id="md-item_code" style="color: #666;">-</div>
                            </div>
                            <div class="details-item" style="margin-bottom: 12px; display: flex; justify-content: space-between;">
                                <div class="details-label" style="font-weight: 600; color: #1a4931;">Generic Name</div>
                                <div class="details-value" id="md-generic_name" style="color: #666;">-</div>
                            </div>
                            <div class="details-item" style="margin-bottom: 12px; display: flex; justify-content: space-between;">
                                <div class="details-label" style="font-weight: 600; color: #1a4931;">Brand Name</div>
                                <div class="details-value" id="md-brand_name" style="color: #666;">-</div>
                            </div>
                            <div class="details-item" style="margin-bottom: 12px; display: flex; justify-content: space-between;">
                                <div class="details-label" style="font-weight: 600; color: #1a4931;">Price</div>
                                <div class="details-value" id="md-price" style="color: #666;">-</div>
                            </div>
                            <div class="details-item" style="margin-bottom: 12px; display: flex; justify-content: space-between;">
                                <div class="details-label" style="font-weight: 600; color: #1a4931;">Quantity</div>
                                <div class="details-value" id="md-quantity" style="color: #666;">-</div>
                            </div>
                            <div class="details-item" style="margin-bottom: 12px; display: flex; justify-content: space-between;">
                                <div class="details-label" style="font-weight: 600; color: #1a4931;">Reorder Level</div>
                                <div class="details-value" id="md-reorder_level" style="color: #666;">-</div>
                            </div>
                            <div class="details-item" style="margin-bottom: 12px; display: flex; justify-content: space-between;">
                                <div class="details-label" style="font-weight: 600; color: #1a4931;">Expiry Date</div>
                                <div class="details-value" id="md-expiry_date" style="color: #666;">-</div>
                            </div>
                            <div class="details-item" style="margin-bottom: 12px; display: flex; justify-content: space-between;">
                                <div class="details-label" style="font-weight: 600; color: #1a4931;">Supplier</div>
                                <div class="details-value" id="md-supplier" style="color: #666;">-</div>
                            </div>
                            <div class="details-item" style="margin-bottom: 12px; display: flex; justify-content: space-between;">
                                <div class="details-label" style="font-weight: 600; color: #1a4931;">Batch Number</div>
                                <div class="details-value" id="md-batch_number" style="color: #666;">-</div>
                            </div>
                            <div class="details-item" style="margin-bottom: 12px; display: flex; justify-content: space-between;">
                                <div class="details-label" style="font-weight: 600; color: #1a4931;">Date Received</div>
                                <div class="details-value" id="md-date_received" style="color: #666;">-</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Pagination -->
            @if($stockspharmacy->hasPages())
            <div class="pharmacy-pagination" style="margin-top: 20px;">
                {{ $stockspharmacy->appends(['q' => $q])->links('components.custom-pagination') }}
            </div>
            @endif
        </main>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const table = document.getElementById('stocksTable');
        const rows = table ? table.querySelectorAll('.stock-row') : [];
        const detailsCard = document.getElementById('detailsCard');
        const detailsEmpty = document.getElementById('detailsEmpty');
        const detailsContent = document.getElementById('detailsContent');

        function or(v){ return v===null||v===undefined||v==='' ? '-' : v; }

        function renderStock(stock){
            document.getElementById('md-item_code').textContent = or(stock.item_code);
            document.getElementById('md-generic_name').textContent = or(stock.generic_name);
            document.getElementById('md-brand_name').textContent = or(stock.brand_name);
            document.getElementById('md-price').textContent = '₱' + (stock.price ? parseFloat(stock.price).toFixed(2) : '0.00');
            document.getElementById('md-quantity').textContent = or(stock.quantity || 0);
            document.getElementById('md-reorder_level').textContent = or(stock.reorder_level);
            document.getElementById('md-expiry_date').textContent = or(stock.expiry_date);
            document.getElementById('md-supplier').textContent = or(stock.supplier);
            document.getElementById('md-batch_number').textContent = or(stock.batch_number);
            document.getElementById('md-date_received').textContent = or(stock.date_received);
        }

        function clearActive(){
            rows.forEach(r => r.classList.remove('active'));
        }

        rows.forEach(row => {
            const btn = row.querySelector('.js-open-stock');
            btn.addEventListener('click', function(){
                const payload = row.getAttribute('data-stock');
                try {
                    const stock = JSON.parse(payload);
                    clearActive();
                    row.classList.add('active');
                    row.style.backgroundColor = '#f8f9fa';
                    detailsEmpty.style.display = 'none';
                    detailsContent.style.display = '';
                    renderStock(stock);
                } catch(e){
                    console.error('Invalid stock JSON', e);
                }
            });
        });

        // Hover effects for table rows
        rows.forEach(row => {
            row.addEventListener('mouseenter', function(){
                if (!this.classList.contains('active')) {
                    this.style.backgroundColor = '#f8f9fa';
                }
            });
            row.addEventListener('mouseleave', function(){
                if (!this.classList.contains('active')) {
                    this.style.backgroundColor = '';
                }
            });
        });

        // Optionally auto-select first row
        if (rows.length && !document.querySelector('.stock-row.active')) {
            rows[0].querySelector('.js-open-stock').click();
        }

        // Simple table sorting functionality
        const sortableHeaders = document.querySelectorAll('.sortable');
        let sortColumn = -1;
        let sortDirection = 'asc';

        sortableHeaders.forEach((header, index) => {
            header.addEventListener('click', function() {
                const columnIndex = parseInt(this.getAttribute('data-sort'));
                
                if (sortColumn === columnIndex) {
                    sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    sortDirection = 'asc';
                    sortColumn = columnIndex;
                }

                // Update sort icons
                sortableHeaders.forEach(h => {
                    const icon = h.querySelector('.sort-icon i');
                    icon.className = 'fas fa-sort';
                });
                
                const currentIcon = this.querySelector('.sort-icon i');
                currentIcon.className = sortDirection === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';

                // Sort the table
                sortTable(columnIndex, sortDirection);
            });
        });

        function sortTable(columnIndex, direction) {
            const tbody = table.querySelector('tbody');
            const rowsArray = Array.from(tbody.querySelectorAll('tr'));
            
            rowsArray.sort((a, b) => {
                const aText = a.cells[columnIndex].textContent.trim();
                const bText = b.cells[columnIndex].textContent.trim();
                
                // Handle numeric columns (price, quantity)
                if (columnIndex === 3 || columnIndex === 4) {
                    const aNum = parseFloat(aText.replace(/[₱,]/g, '')) || 0;
                    const bNum = parseFloat(bText.replace(/[₱,]/g, '')) || 0;
                    return direction === 'asc' ? aNum - bNum : bNum - aNum;
                }
                
                // Handle text columns
                return direction === 'asc' ? aText.localeCompare(bText) : bText.localeCompare(aText);
            });

            // Remove all rows and re-add them in sorted order
            rowsArray.forEach(row => tbody.removeChild(row));
            rowsArray.forEach(row => tbody.appendChild(row));
        }
    });
    </script>
    </div>
</body>
</html>