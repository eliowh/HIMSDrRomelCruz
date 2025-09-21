<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Inventory Stocks</title>
    <link rel="stylesheet" href="{{ url('css/inventory.css') }}">
    <!-- Reuse nurse modal styles for consistent popups -->
    <link rel="stylesheet" href="{{ url('css/nursecss/nurse_patients.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

                        <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                            <h2 style="margin:0;">Stocks</h2>
                            <form method="GET" style="margin-left:auto;display:flex;align-items:center;gap:8px;">
                                <input type="search" name="q" value="{{ $q }}" placeholder="Search item code, generic or brand..." style="padding:8px 10px;border:1px solid #ddd;border-radius:6px;" />
                                <button type="submit" class="action-btn primary">Search</button>
                                <button type="button" id="addStockBtn" class="action-btn primary" style="margin-left:8px;">Add Stock</button>
                            </form>
                        </div>

                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        @if($stocks->count())
                            <div class="table-wrap">
                                <table class="patients-table" id="stocksTable" style="width:100%;border-collapse:collapse;">
                                    <thead>
                                        <tr>
                                            <th style="text-align:left;padding:10px;border-bottom:1px solid #eee;">Item Code</th>
                                            <th style="text-align:left;padding:10px;border-bottom:1px solid #eee;">Generic Name</th>
                                            <th style="text-align:left;padding:10px;border-bottom:1px solid #eee;">Brand</th>
                                            <th style="text-align:right;padding:10px;border-bottom:1px solid #eee;">Price</th>
                                            <th style="text-align:right;padding:10px;border-bottom:1px solid #eee;">Quantity</th>
                                            <th style="text-align:center;padding:10px;border-bottom:1px solid #eee;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($stocks as $s)
                                        <tr class="stock-row" data-stock='@json($s)'>
                                            <td style="padding:10px;border-bottom:1px solid #f2f4f7;">{{ $s->item_code }}</td>
                                            <td style="padding:10px;border-bottom:1px solid #f2f4f7;">{{ $s->generic_name }}</td>
                                            <td style="padding:10px;border-bottom:1px solid #f2f4f7;">{{ $s->brand_name }}</td>
                                            <td style="padding:10px;border-bottom:1px solid #f2f4f7;text-align:right;">{{ is_numeric($s->price) ? number_format($s->price,2) : '-' }}</td>
                                            <td style="padding:10px;border-bottom:1px solid #f2f4f7;text-align:right;">{{ $s->quantity ?? 0 }}</td>
                                            <td style="padding:10px;border-bottom:1px solid #f2f4f7;text-align:center;">
                                                <button type="button" class="btn view-btn js-open-stock">View</button>
                                                <button type="button" class="btn delete-btn" style="background:#dc3545;color:#fff;margin-left:8px;" data-id="{{ $s->id ?? $s->item_code }}">Delete</button>
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
                    <div class="inventory-card" id="detailsCard">
                        <div class="patients-header"><h3>Stock Details</h3></div>
                        <div id="detailsEmpty">Select an item to view details.</div>
                        <div id="detailsContent" style="display:none;margin-top:12px;">
                            <dl style="display:grid;grid-template-columns:140px 1fr;gap:8px;">
                                <dt>Item Code</dt><dd id="md-item_code">-</dd>
                                <dt>Generic Name</dt><dd id="md-generic_name">-</dd>
                                <dt>Brand Name</dt><dd id="md-brand_name">-</dd>
                                <dt>Price</dt><dd id="md-price">-</dd>
                                <dt>Quantity</dt><dd id="md-quantity">-</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Pagination -->
            <div class="pagination-wrapper">
                {{ $stocks->appends(['q' => $q])->links('components.custom-pagination') }}
            </div>
        </main>
    </div>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Add Stock Modal (nurse-style, mini) -->
    <style>
    /* Mini modal override: reuse nurse modal styles but make it compact */
    .modal.mini .modal-content { width:520px; max-width:92%; padding:12px; border-radius:10px; }
    .modal.mini .modal-header, .modal.mini .modal-footer { padding:10px 12px; }
    .modal.mini h3 { margin:6px 0 10px; font-size:18px; }
    .modal.mini .close { font-size:20px; top:8px; right:10px; }
    .icd-suggestions { background:#fff; border:1px solid #ddd; max-height:240px; overflow:auto; border-radius:6px; box-shadow:0 6px 18px rgba(0,0,0,0.08); }
    .icd-suggestion { padding:8px 10px; cursor:pointer; border-bottom:1px solid #f1f1f1; }
    .icd-suggestion:last-child{ border-bottom:none; }
    .icd-suggestion:hover, .icd-suggestion.active { background:#f0f7f1; }
    </style>

    <div id="addStockModal" class="modal mini">
        <div class="modal-content">
            <span class="close" onclick="closeAddStockModal()">&times;</span>
            <h3>Add Stock</h3>
            <form id="addStockForm">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                    <div class="form-group">
                        <label>Item Code</label>
                        <div style="position:relative;">
                            <input id="input-item_code" name="item_code" placeholder="Item code" autocomplete="off" />
                            <div id="suggestions-item_code" class="icd-suggestions" style="display:none;position:absolute;z-index:2000;left:0;right:0;margin-top:6px;padding:6px;"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Generic Name</label>
                        <div style="position:relative;">
                            <input id="input-generic_name" name="generic_name" placeholder="Generic name" autocomplete="off" />
                            <div id="suggestions-generic_name" class="icd-suggestions" style="display:none;position:absolute;z-index:2000;left:0;right:0;margin-top:6px;padding:6px;"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Brand Name</label>
                        <div style="position:relative;">
                            <input id="input-brand_name" name="brand_name" placeholder="Brand name" />
                            <select id="brand-options" style="display:none;width:100%;margin-top:6px;"></select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Price (optional)</label>
                        <input name="price" type="number" step="0.01" placeholder="0.00" />
                    </div>
                </div>

                <div class="form-group" style="margin-top:8px;">
                    <label>Quantity to add *</label>
                    <input name="quantity" type="number" min="1" required placeholder="Quantity" />
                </div>

                <div class="form-actions" style="margin-top:12px;display:flex;justify-content:flex-end;gap:8px;">
                    <button type="button" class="btn cancel-btn" onclick="closeAddStockModal()">Cancel</button>
                    <button type="submit" class="btn submit-btn">Add</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Autocomplete rewritten to match ICD-10 UX (keyboard navigation, Enter, Escape)
    (function(){
        const containerItem = document.getElementById('suggestions-item_code');
        const containerGeneric = document.getElementById('suggestions-generic_name');
        const inputItem = document.getElementById('input-item_code');
        const inputGeneric = document.getElementById('input-generic_name');
        const brandInput = document.getElementById('input-brand_name');
        const brandSelect = document.getElementById('brand-options');
        if(!((containerItem && inputItem) || (containerGeneric && inputGeneric))) return;

        let timer = null; let activeIndex = -1; let lastItems = [];
        function clearSuggestions(container){ if(!container) return; container.innerHTML=''; container.style.display='none'; activeIndex=-1; lastItems=[]; }

        function escapeHtml(s){ if(!s) return ''; return String(s).replace(/[&<>'"]/g, (m)=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

        function render(container, items){ if(!container) return; lastItems = items || []; if(!lastItems.length){ clearSuggestions(container); return; } container.innerHTML=''; lastItems.forEach((it, idx)=>{ const el = document.createElement('div'); el.className='icd-suggestion'; el.dataset.index = idx; el.innerHTML = '<strong>' + escapeHtml(it.item_code || '') + '</strong> ' + (it.generic_name ? (' - ' + escapeHtml(it.generic_name)) : ''); el.addEventListener('click', ()=> selectItem(container, idx)); container.appendChild(el); }); container.style.display='block'; activeIndex = -1; }

        function selectItem(container, idx){ const it = lastItems[idx]; if(!it) return; // fill fields
            if(inputItem) inputItem.value = it.item_code || ''; 
            if(inputGeneric) inputGeneric.value = it.generic_name || ''; 
            
            // Set price only if brand is also selected or there's only one result
            const priceInput = document.querySelector('input[name="price"]');
            if (priceInput && it.price !== null && it.price !== undefined) {
                priceInput.value = it.price;
            }
            
            // Only clear quantity - we want to preserve other fields for auto-completion
            const qtyInput = document.querySelector('input[name="quantity"]');
            if(qtyInput) qtyInput.value = '';
            
            populateBrandOptions(it);
            clearSuggestions(container);
        }

        function highlight(container){ const nodes = container.querySelectorAll('.icd-suggestion'); nodes.forEach((n,i)=> n.classList.toggle('active', i===activeIndex)); }

        function populateBrandOptions(item){ if(!brandSelect || !brandInput) return; if(!item || !item.generic_name){ brandSelect.style.display='none'; return; }
            fetch(`{{ route('inventory.stocks.search') }}?q=` + encodeURIComponent(item.generic_name || item.item_code || ''))
                .then(r=>r.json()).then(list=>{
                    // Filter matches by generic name
                    const matches = list.filter(x => x.generic_name && 
                        x.generic_name.toLowerCase() === item.generic_name.toLowerCase());
                    
                    if(!matches.length){ brandSelect.style.display='none'; return; }
                    
                    // Group by brand_name, keeping item_code and price data
                    const brandData = {};
                    matches.forEach(match => {
                        if (match.brand_name) {
                            brandData[match.brand_name] = {
                                item_code: match.item_code || '',
                                price: match.price || '',
                                brand_name: match.brand_name
                            };
                        }
                    });
                    
                    const brands = Object.keys(brandData);
                    if(!brands.length){ brandSelect.style.display='none'; return; }
                    
                    // Create brand options with data attributes
                    brandSelect.innerHTML=''; 
                    const placeholder = document.createElement('option'); 
                    placeholder.value=''; 
                    placeholder.text = 'Select existing brand to auto-fill code & price'; 
                    brandSelect.appendChild(placeholder);
                    
                    brands.forEach(brandName => {
                        const data = brandData[brandName];
                        const o = document.createElement('option'); 
                        o.value = brandName;
                        o.text = brandName;
                        o.dataset.itemCode = data.item_code;
                        o.dataset.price = data.price;
                        brandSelect.appendChild(o);
                    }); 
                    
                    brandSelect.style.display=''; 
                    
                    // When brand is selected, fill brand name, item_code, and price
                    brandSelect.onchange = function(){
                        const selected = this.options[this.selectedIndex];
                        if (!selected || !selected.value) return;
                        
                        // Set brand name
                        brandInput.value = selected.value;
                        
                        // Set item code if available
                        if (selected.dataset.itemCode && inputItem) {
                            inputItem.value = selected.dataset.itemCode;
                        }
                        
                        // Set price if available
                        const priceInput = document.querySelector('input[name="price"]');
                        if (selected.dataset.price && priceInput) {
                            priceInput.value = selected.dataset.price;
                        }
                    };
                    
                    // If only one brand, auto-select it
                    if (brands.length === 1) {
                        brandSelect.selectedIndex = 1; // First real option (after placeholder)
                        brandSelect.onchange(); // Trigger the change handler
                    }
                }).catch(e=>{ console.error('brand fetch failed', e); brandSelect.style.display='none'; });
        }

        function doSearch(q, container){ if(timer) clearTimeout(timer); if(!q || q.length < 1){ clearSuggestions(container); return; } timer = setTimeout(()=>{
            fetch(`{{ route('inventory.stocks.search') }}?q=` + encodeURIComponent(q)).then(async r=>{
                const ct = (r.headers.get('content-type')||'').toLowerCase(); const text = await r.text(); if(!ct.includes('application/json')){ clearSuggestions(container); return; }
                try{ const data = JSON.parse(text); render(container, Array.isArray(data)?data:[]); } catch(e){ console.error('parse', e); clearSuggestions(container); }
            }).catch(e=>{ console.error('search fail', e); clearSuggestions(container); });
        }, 200); }

        if(inputItem){ inputItem.addEventListener('input', function(){ doSearch(this.value, containerItem); }); inputItem.addEventListener('blur', ()=> setTimeout(()=>clearSuggestions(containerItem), 180)); inputItem.addEventListener('keydown', function(e){ const nodes = containerItem.querySelectorAll('.icd-suggestion'); if(!nodes.length) return; if(e.key==='ArrowDown'){ e.preventDefault(); activeIndex = Math.min(activeIndex+1, nodes.length-1); highlight(containerItem); nodes[activeIndex].scrollIntoView({block:'nearest'}); } else if(e.key==='ArrowUp'){ e.preventDefault(); activeIndex = Math.max(activeIndex-1, 0); highlight(containerItem); nodes[activeIndex].scrollIntoView({block:'nearest'}); } else if(e.key==='Enter'){ e.preventDefault(); if(activeIndex>=0) selectItem(containerItem, activeIndex); } else if(e.key==='Escape'){ clearSuggestions(containerItem); } }); }

        if(inputGeneric){ inputGeneric.addEventListener('input', function(){ doSearch(this.value, containerGeneric); }); inputGeneric.addEventListener('blur', ()=> setTimeout(()=>clearSuggestions(containerGeneric), 180)); inputGeneric.addEventListener('keydown', function(e){ const nodes = containerGeneric.querySelectorAll('.icd-suggestion'); if(!nodes.length) return; if(e.key==='ArrowDown'){ e.preventDefault(); activeIndex = Math.min(activeIndex+1, nodes.length-1); highlight(containerGeneric); nodes[activeIndex].scrollIntoView({block:'nearest'}); } else if(e.key==='ArrowUp'){ e.preventDefault(); activeIndex = Math.max(activeIndex-1, 0); highlight(containerGeneric); nodes[activeIndex].scrollIntoView({block:'nearest'}); } else if(e.key==='Enter'){ e.preventDefault(); if(activeIndex>=0) selectItem(containerGeneric, activeIndex); } else if(e.key==='Escape'){ clearSuggestions(containerGeneric); } }); }

        document.addEventListener('click', function(e){ if(containerItem && !containerItem.contains(e.target) && e.target !== inputItem) clearSuggestions(containerItem); if(containerGeneric && !containerGeneric.contains(e.target) && e.target !== inputGeneric) clearSuggestions(containerGeneric); });
    })();
    document.addEventListener('DOMContentLoaded', function(){
        const rows = Array.from(document.querySelectorAll('.stock-row'));
        const detailsEmpty = document.getElementById('detailsEmpty');
        const detailsContent = document.getElementById('detailsContent');

        function or(v){ return v===null||v===undefined||v==='' ? '-' : v; }

        function renderStock(stock){
            document.getElementById('md-item_code').textContent = or(stock.item_code);
            document.getElementById('md-generic_name').textContent = or(stock.generic_name);
            document.getElementById('md-brand_name').textContent = or(stock.brand_name);
            document.getElementById('md-price').textContent = stock.price !== null ? parseFloat(stock.price).toFixed(2) : '-';
            document.getElementById('md-quantity').textContent = or(stock.quantity ?? 0);
        }

        function clearActive(){ rows.forEach(r => r.classList.remove('active')); }

        rows.forEach(row => {
            const btn = row.querySelector('.js-open-stock');
            btn.addEventListener('click', function(){
                try{
                    const stock = JSON.parse(row.getAttribute('data-stock'));
                    clearActive(); row.classList.add('active');
                    detailsEmpty.style.display = 'none'; detailsContent.style.display = '';
                    renderStock(stock);
                }catch(e){ console.error('Invalid stock JSON', e); }
            });
        });

        if(rows.length && !document.querySelector('.stock-row.active')){
            rows[0].querySelector('.js-open-stock').click();
        }
    });
    </script>

    <script>
    // Add Stock modal functions (nurse-style)
    function openAddStockModal() {
        window.isModalOpen = true;
        const modal = document.getElementById('addStockModal');
        modal.classList.add('show');
        modal.classList.add('open');
        document.getElementById('addStockForm').reset();
    }

    function closeAddStockModal() {
        const modal = document.getElementById('addStockModal');
        modal.classList.remove('show');
        modal.classList.remove('open');
        document.getElementById('addStockForm').reset();
        setTimeout(() => { window.isModalOpen = false; }, 300);
    }

    // Wire Add Stock button to open modal
    document.getElementById('addStockBtn').addEventListener('click', function(){ openAddStockModal(); });

    // Submit handler for addStockForm
    document.getElementById('addStockForm').addEventListener('submit', function(e){
        e.preventDefault();
        const form = this;
        const submitBtn = form.querySelector('.submit-btn');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Submitting...'; submitBtn.disabled = true;

        const fd = new FormData(form);
        fd.set('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        const qty = parseInt(fd.get('quantity')||0, 10);
        if (!qty || qty <= 0) { alert('Please enter a valid quantity'); submitBtn.textContent = originalText; submitBtn.disabled = false; return; }

        fetch('{{ route('inventory.stocks.add') }}', {
            method: 'POST',
            credentials: 'same-origin',
            body: fd
        }).then(async r => {
            const ct = r.headers.get('content-type') || '';
            const txt = await r.text();
            if (!r.ok) {
                let msg = txt;
                if (ct.includes('application/json')) {
                    try { msg = JSON.parse(txt).message || JSON.parse(txt).errors || txt; } catch(e){}
                }
                alert('Failed: ' + JSON.stringify(msg));
                return;
            }
            if (ct.includes('application/json')) {
                const j = JSON.parse(txt);
                if (j.ok) { alert('Stock updated'); closeAddStockModal(); location.reload(); return; }
                alert('Error: ' + (j.message || 'Unknown'));
            } else { location.reload(); }
        }).catch(e => { console.error(e); alert('Add failed: ' + e.message); })
        .finally(() => { submitBtn.textContent = originalText; submitBtn.disabled = false; });
    });

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('addStockModal');
        if (event.target === modal) {
            closeAddStockModal();
        }
    }
    </script>

    <script>
    // Delete stock handler: calls DELETE /inventory/stocks/{id}
    document.addEventListener('DOMContentLoaded', function(){
        document.querySelectorAll('.delete-btn').forEach(function(btn){
            btn.addEventListener('click', function(){
                const id = this.getAttribute('data-id');
                if (!confirm('Are you sure you want to delete this stock item? This cannot be undone.')) return;

                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch('{{ url('/inventory/stocks') }}/' + encodeURIComponent(id), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                }).then(async res => {
                    const txt = await res.text();
                    let json = null;
                    try { json = JSON.parse(txt); } catch(e){}
                    if (!res.ok) {
                        const msg = (json && json.message) ? json.message : txt || 'Delete failed';
                        alert('Delete failed: ' + msg);
                        return;
                    }
                    // success: remove row from DOM and clear details if necessary
                    const row = btn.closest('tr');
                    if (row) row.remove();
                    const detailsItem = document.getElementById('md-item_code');
                    if (detailsItem && detailsItem.textContent === id) {
                        document.getElementById('detailsContent').style.display = 'none';
                        document.getElementById('detailsEmpty').style.display = '';
                    }
                    alert('Deleted');
                }).catch(e => { console.error(e); alert('Delete error: ' + e.message); });
            });
        });
    });
    </script>
</body>
</html>
