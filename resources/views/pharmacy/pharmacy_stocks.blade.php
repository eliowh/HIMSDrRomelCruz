<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Stocks</title>
    <link rel="stylesheet" href="{{ asset('css/pharmacycss/pharmacy.css') }}">
    <link rel="stylesheet" href="{{ asset('css/inventorycss/inventory.css') }}">
    <link rel="stylesheet" href="{{ asset('css/inventorycss/add_stock_modal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* Highlight styles for stocks page */
        tr.stock-row.highlight-low { background: #fff3cd !important; }
        tr.stock-row.highlight-expiry { background: #f8d7da !important; }
        tr.stock-row.highlight-generic { outline: 2px solid rgba(46,125,50,0.12); }
    </style>
    <script>
        // Safety stubs to avoid inline-onclick ReferenceErrors before full scripts load
        if (typeof window.openEditModalFromDetails !== 'function') window.openEditModalFromDetails = function(){ try{ if(typeof showError==='function') showError('No stock selected','Selection Error'); else alert('No stock selected'); }catch(e){} };
        if (typeof window.confirmDeleteFromDetails !== 'function') window.confirmDeleteFromDetails = function(){ try{ if(typeof showError==='function') showError('No stock selected','Selection Error'); else alert('No stock selected'); }catch(e){} };
        if (typeof window.openAddStockModal !== 'function') window.openAddStockModal = function(){ try{ const m = document.getElementById('addStockModal'); if(m) { m.classList.add('show'); m.classList.add('open'); } }catch(e){} };
    </script>
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
                            <form method="GET" class="search-form" style="display:flex;gap:8px;align-items:center;">
                                <input type="search" name="q" value="{{ $q }}" placeholder="Search stocks..." class="form-control" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;" />
                                <button type="submit" class="search-btn" style="padding: 8px 12px; background: #367F2B; color: white; border: none; border-radius: 4px; cursor: pointer;"><i class="fas fa-search"></i> Search</button>
                                <button type="button" class="search-btn" style="padding: 8px 12px; background: #2a7fdb; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left:8px;" onclick="openAddStockModal()"><i class="fas fa-plus"></i> Add Stock</button>
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
                                        @php
                                            $sData = $s->toArray();
                                            $sData['expiry_date'] = $s->expiry_date ? ($s->expiry_date instanceof \Carbon\Carbon ? $s->expiry_date->format('Y-m-d') : \Carbon\Carbon::parse($s->expiry_date)->format('Y-m-d')) : null;
                                        @endphp
                                        <tr class="stock-row order-row" data-stock='@json($sData)' style="transition: background-color 0.2s;">
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
                            <div style="display:flex;gap:8px;margin-top:12px;">
                                <button type="button" class="pharmacy-btn-primary" id="editStockBtn" onclick="openEditModalFromDetails()"><i class="fas fa-edit"></i> Edit</button>
                                <button type="button" class="pharmacy-btn-danger" id="deleteStockBtn" onclick="confirmDeleteFromDetails()"><i class="fas fa-trash"></i> Delete</button>
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
    // Modal markup injection
    (function(){
        const modalHtml = `
        <div id="stockModal" style="display:none;position:fixed;left:0;top:0;right:0;bottom:0;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;z-index:9999;">
            <div style="background:white;padding:16px;border-radius:6px;max-width:640px;width:100%;">
                <h3 id="stockModalTitle">Add Stock</h3>
                <form id="stockForm">
                    <input type="hidden" name="_method" id="stockFormMethod" value="POST">
                    <input type="hidden" name="id" id="stockFormId">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                        <input name="item_code" id="f_item_code" placeholder="Item Code" required style="padding:8px;border:1px solid #ddd;border-radius:4px;" />
                        <input name="generic_name" id="f_generic_name" placeholder="Generic Name" style="padding:8px;border:1px solid #ddd;border-radius:4px;" />
                        <input name="brand_name" id="f_brand_name" placeholder="Brand Name" style="padding:8px;border:1px solid #ddd;border-radius:4px;" />
                        <input name="price" id="f_price" placeholder="Price" type="number" step="0.01" style="padding:8px;border:1px solid #ddd;border-radius:4px;" />
                        <input name="quantity" id="f_quantity" placeholder="Quantity" type="number" min="0" style="padding:8px;border:1px solid #ddd;border-radius:4px;" />
                        <input name="reorder_level" id="f_reorder_level" placeholder="Reorder Level" type="number" min="0" style="padding:8px;border:1px solid #ddd;border-radius:4px;" />
                        <input name="expiry_date" id="f_expiry_date" placeholder="Expiry Date (YYYY-MM-DD)" style="padding:8px;border:1px solid #ddd;border-radius:4px;" />
                        <input name="supplier" id="f_supplier" placeholder="Supplier" style="padding:8px;border:1px solid #ddd;border-radius:4px;" />
                        <input name="batch_number" id="f_batch_number" placeholder="Batch Number" style="padding:8px;border:1px solid #ddd;border-radius:4px;" />
                        <input name="date_received" id="f_date_received" placeholder="Date Received (YYYY-MM-DD)" style="padding:8px;border:1px solid #ddd;border-radius:4px;" />
                    </div>
                    <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:12px;">
                        <button type="button" onclick="closeStockModal()" style="padding:8px 12px;border-radius:4px;border:1px solid #ccc;background:#fff;">Cancel</button>
                        <button type="submit" style="padding:8px 12px;border-radius:4px;border:none;background:#2a7fdb;color:#fff;">Save</button>
                    </div>
                </form>
            </div>
        </div>`;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    })();

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
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
            // Normalize expiry date display: show YYYY-MM-DD, or '-' if not present
            try {
                const raw = stock.expiry_date;
                if (raw && String(raw).trim() !== '') {
                    const d = new Date(raw);
                    if (!isNaN(d.getTime())) {
                        const y = d.getFullYear();
                        const m = String(d.getMonth()+1).padStart(2,'0');
                        const day = String(d.getDate()).padStart(2,'0');
                        document.getElementById('md-expiry_date').textContent = `${y}-${m}-${day}`;
                    } else {
                        document.getElementById('md-expiry_date').textContent = String(raw);
                    }
                } else {
                    document.getElementById('md-expiry_date').textContent = '-';
                }
            } catch(e) { document.getElementById('md-expiry_date').textContent = or(stock.expiry_date); }
            document.getElementById('md-supplier').textContent = or(stock.supplier);
            document.getElementById('md-batch_number').textContent = or(stock.batch_number);
            document.getElementById('md-date_received').textContent = or(stock.date_received);
            // keep global selection objects in sync so other handlers (Edit/Delete) can read the selection
            try{ window.currentPharmacyStock = stock; }catch(e){}
            try{ window.__currentStock = stock; }catch(e){}
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
                    window.currentPharmacyStock = stock;
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

        // Try to restore previously selected item (after add/update/delete) using sessionStorage
        let restored = false;
        try {
            const wanted = sessionStorage.getItem('pharmacy_selected_item');
            if (wanted) {
                for (const row of rows) {
                    try {
                        const payload = JSON.parse(row.getAttribute('data-stock'));
                        if (payload) {
                            const a = String(payload.item_code || payload.id || '').trim().toLowerCase();
                            const b = String(wanted || '').trim().toLowerCase();
                            if (a === b) {
                                row.querySelector('.js-open-stock').click();
                                restored = true;
                                break;
                            }
                        }
                    } catch(e) { /* ignore parse errors */ }
                }
                // clear it so it doesn't persist
                sessionStorage.removeItem('pharmacy_selected_item');
            }
        } catch(e) { /* ignore session errors */ }

        // If not restored, fall back to selecting first row automatically
        if (!restored && rows.length && !document.querySelector('.stock-row.active')) {
            rows[0].querySelector('.js-open-stock').click();
        }

        // Highlighting based on query params: ?highlight=low or ?highlight=expiry or ?highlight_codes=code1,code2
        try {
            const params = new URLSearchParams(window.location.search);
            const highlight = params.get('highlight');
            const highlightCodesParam = params.get('highlight_codes');
            const highlightCodes = highlightCodesParam ? highlightCodesParam.split(',').map(s=>s.trim().toLowerCase()).filter(Boolean) : [];

            if (highlight === 'low') {
                // Highlight all rows where quantity <= reorder_level, then focus the first match
                let firstLowRow = null;
                for (const row of rows) {
                    try {
                        const stock = JSON.parse(row.getAttribute('data-stock') || '{}');
                        const qty = parseInt(stock.quantity || 0, 10);
                        const rl = parseInt(stock.reorder_level || 0, 10);
                        if (!isNaN(qty) && !isNaN(rl) && qty <= rl) {
                            row.classList.add('highlight-low');
                            if (!firstLowRow) firstLowRow = row;
                        }
                    } catch(e) { /* ignore parse errors */ }
                }
                if (firstLowRow) {
                    firstLowRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    try { firstLowRow.querySelector('.js-open-stock').click(); } catch(e){}
                }
            }

            if (highlight === 'expiry' || highlightCodes.length) {
                // Highlight all matching expiry codes, focus first match
                let firstExpiryRow = null;
                for (const row of rows) {
                    try {
                        const stock = JSON.parse(row.getAttribute('data-stock') || '{}');
                        const code = String(stock.item_code || '').trim().toLowerCase();
                        if (highlightCodes.length && highlightCodes.indexOf(code) !== -1) {
                            row.classList.add('highlight-expiry');
                            if (!firstExpiryRow) firstExpiryRow = row;
                        }
                    } catch(e) { }
                }
                if (firstExpiryRow) {
                    firstExpiryRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    try { firstExpiryRow.querySelector('.js-open-stock').click(); } catch(e){}
                }
            }
        } catch(e) { console.error('Highlighting error', e); }

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
    
    // Additional helper functions for modal CRUD and delete
    (function(){
        // Handle stock form submission
        document.addEventListener('submit', async function(e){
            if (e.target && e.target.id === 'stockForm') {
                e.preventDefault();
                const id = document.getElementById('stockFormId').value;
                const method = document.getElementById('stockFormMethod').value || 'POST';
                const url = method === 'POST' ? '/pharmacy/stocks/add' : '/pharmacy/stocks/' + encodeURIComponent(id);

                const payload = {
                    item_code: document.getElementById('f_item_code').value,
                    generic_name: document.getElementById('f_generic_name').value,
                    brand_name: document.getElementById('f_brand_name').value,
                    price: document.getElementById('f_price').value,
                    quantity: document.getElementById('f_quantity').value,
                    reorder_level: document.getElementById('f_reorder_level').value,
                    expiry_date: document.getElementById('f_expiry_date').value,
                    supplier: document.getElementById('f_supplier').value,
                    batch_number: document.getElementById('f_batch_number').value,
                    date_received: document.getElementById('f_date_received').value,
                };

                try {
                    const res = await fetch(url, {
                        method: method === 'POST' ? 'POST' : 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    if (!res.ok || data.ok === false) {
                        const msg = (data.message || 'Failed to save') + (data.errors ? '\n' + JSON.stringify(data.errors) : '');
                        if (typeof showError === 'function') showError(msg, 'Save Failed'); else alert(msg);
                        return;
                    }
                    // success: notify and refresh
                    if (typeof showSuccess === 'function') showSuccess('Stock saved successfully', 'Saved');
                    location.reload();
                } catch (err) {
                    console.error(err);
                    if (typeof showError === 'function') showError('Failed to save stock: ' + (err.message || err), 'Save Error'); else alert('Failed to save stock');
                }
            }
        });

        // Open the Edit modal (populate edit modal inputs and open included edit modal)
        window.openEditModalFromDetails = function(){
            // Accept either global used by different scripts: prefer window.currentPharmacyStock, fall back to window.__currentStock
            let s = (window.currentPharmacyStock || window.__currentStock) || null;
            // If nothing selected yet (initial page load), try to read the first row's data directly
            if (!s) {
                try {
                    const firstRow = document.querySelector('.stock-row');
                    if (firstRow) {
                        const payload = firstRow.getAttribute('data-stock');
                        if (payload) {
                            try {
                                const parsed = JSON.parse(payload);
                                window.currentPharmacyStock = parsed;
                                window.__currentStock = parsed;
                                s = parsed;
                            } catch (e) { /* ignore parse errors */ }
                        }
                    }
                } catch (e) { /* ignore selection errors */ }
            }
            if (!s) { if(typeof showError === 'function') showError('No stock selected', 'Selection Error'); return; }
            document.getElementById('edit-id').value = s.id || s.item_code || '';
            document.getElementById('edit-item_code').value = s.item_code || '';
            document.getElementById('edit-generic_name').value = s.generic_name || '';
            document.getElementById('edit-brand_name').value = s.brand_name || '';
            // Handle price parsing
            if (s.price !== null && s.price !== undefined && s.price !== '') {
                const cleanPrice = String(s.price).replace(/,/g, '');
                const parsedPrice = parseFloat(cleanPrice);
                document.getElementById('edit-price').value = isNaN(parsedPrice) ? '' : parsedPrice.toFixed(2);
            } else {
                document.getElementById('edit-price').value = '';
            }
            document.getElementById('edit-quantity').value = s.quantity ?? 0;
            document.getElementById('edit-reorder_level').value = s.reorder_level ?? '';
            // Ensure expiry_date input is YYYY-MM-DD for <input type="date">
            try {
                const raw = s.expiry_date;
                if (raw && String(raw).trim() !== '') {
                    const d = new Date(raw);
                    if (!isNaN(d.getTime())) {
                        const val = d.toISOString().slice(0,10);
                        document.getElementById('edit-expiry_date').value = val;
                    } else {
                        document.getElementById('edit-expiry_date').value = s.expiry_date;
                    }
                } else {
                    document.getElementById('edit-expiry_date').value = '';
                }
            } catch(e) { document.getElementById('edit-expiry_date').value = s.expiry_date ?? ''; }
            document.getElementById('edit-supplier').value = s.supplier ?? '';
            document.getElementById('edit-batch_number').value = s.batch_number ?? '';
            document.getElementById('edit-date_received').value = s.date_received ?? '';
            // Open the edit modal from included file
            if (typeof openEditStockModal === 'function') {
                openEditStockModal();
            }
        };

        window.confirmDeleteFromDetails = function(){
            let s = (window.currentPharmacyStock || window.__currentStock) || null;
            if (!s) {
                try {
                    const firstRow = document.querySelector('.stock-row');
                    if (firstRow) {
                        const payload = firstRow.getAttribute('data-stock');
                        if (payload) {
                            try {
                                const parsed = JSON.parse(payload);
                                window.currentPharmacyStock = parsed;
                                window.__currentStock = parsed;
                                s = parsed;
                            } catch (e) { /* ignore parse errors */ }
                        }
                    }
                } catch(e){}
            }
            if (!s) { if(typeof showError === 'function') showError('No stock selected', 'Selection Error'); return; }
            if (typeof showConfirm === 'function'){
                showConfirm('Delete stock "' + (s.generic_name || s.item_code) + '"? This action cannot be undone.', 'Confirm Deletion', function(confirmed){ if(!confirmed) return; window.deleteStock(s.id || s.item_code); });
            } else {
                if (!confirm('Delete stock "' + (s.generic_name || s.item_code) + '"? This action cannot be undone.')) return; window.deleteStock(s.id || s.item_code);
            }
        };

        window.deleteStock = async function(id){
            try {
                const res = await fetch('/pharmacy/stocks/' + encodeURIComponent(id), {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                const text = await res.text();
                let data = null; try{ data = JSON.parse(text); }catch(e){ data = null; }
                if (!res.ok) {
                    const msg = (data && data.message) ? data.message : text || 'Delete failed';
                    if (typeof showError === 'function') showError(msg, 'Delete Failed'); else alert(msg);
                    return;
                }
                if (data && (data.ok === false || data.success === false)){
                    const msg = data.message || 'Delete failed';
                    if (typeof showError === 'function') showError(msg, 'Delete Failed'); else alert(msg);
                    return;
                }
                if (typeof showSuccess === 'function') showSuccess('Stock item deleted successfully', 'Deleted');
                // remove row or reload
                try{ const row = document.querySelector(`tr[data-stock*="${id}"]`); if(row) row.remove(); }catch(e){}
                setTimeout(()=> location.reload(), 400);
            } catch (err) {
                console.error(err);
                if (typeof showError === 'function') showError('Delete failed: ' + (err.message || err), 'Delete Error'); else alert('Delete failed');
            }
        };
    })();
    </script>
    </div>

    @include('pharmacy.modals.add_stock_modal')
    @include('Inventory.modals.notification_system')
    @include('pharmacy.modals.inventory_scripts')
    @include('pharmacy.modals.edit_stock_modal')

</body>
</html>