<!-- Common inventory scripts -->
<script>
// Autocomplete functionality for inventory search
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

// Stock details rendering and event binding
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
        document.getElementById('md-reorder_level').textContent = or(stock.reorder_level);
        document.getElementById('md-expiry_date').textContent = or(stock.expiry_date);
        document.getElementById('md-supplier').textContent = or(stock.supplier);
        document.getElementById('md-batch_number').textContent = or(stock.batch_number);
        document.getElementById('md-date_received').textContent = or(stock.date_received);
        // store current stock for editing
        window.__currentStock = stock;
        const editBtn = document.getElementById('editStockBtn');
        if (editBtn) { editBtn.style.display = 'inline-block'; }
    }
    // expose to global so other scripts can call it (e.g. edit form handler)
    window.renderStock = renderStock;

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
    // wire up edit button
    const editBtn = document.getElementById('editStockBtn');
    if (editBtn) {
        editBtn.addEventListener('click', function(){
            const s = window.__currentStock;
            if (!s) return alert('No stock selected');
            document.getElementById('edit-id').value = s.id || s.item_code;
            document.getElementById('edit-item_code').value = s.item_code || '';
            document.getElementById('edit-generic_name').value = s.generic_name || '';
            document.getElementById('edit-brand_name').value = s.brand_name || '';
            document.getElementById('edit-price').value = s.price || '';
            document.getElementById('edit-quantity').value = s.quantity || 0;
            document.getElementById('edit-reorder_level').value = s.reorder_level || '';
            document.getElementById('edit-expiry_date').value = s.expiry_date || '';
            document.getElementById('edit-supplier').value = s.supplier || '';
            document.getElementById('edit-batch_number').value = s.batch_number || '';
            document.getElementById('edit-date_received').value = s.date_received || '';
            openEditStockModal();
        });
    }
});

// Function to ensure decimal places are always displayed
function formatDecimal(input, decimals) {
    if (input.value === '' || isNaN(parseFloat(input.value))) return;
    
    // Parse the input value and format it with the specified number of decimal places
    const value = parseFloat(input.value);
    input.value = value.toFixed(decimals);
}

// Initialize price fields with proper decimal formatting on page load
document.addEventListener('DOMContentLoaded', function() {
    const priceFields = ['edit-price', 'add-price'];
    priceFields.forEach(id => {
        const field = document.getElementById(id);
        if (field && field.value !== '') {
            formatDecimal(field, 2);
        }
    });
    
    // Also add event listeners for when the modals are opened
    document.getElementById('editStockBtn').addEventListener('click', function() {
        setTimeout(() => {
            const editPrice = document.getElementById('edit-price');
            if (editPrice && editPrice.value !== '') {
                formatDecimal(editPrice, 2);
            }
        }, 100); // Short delay to ensure the field is populated
    });
    
    // For input events to format as user types
    document.getElementById('edit-price').addEventListener('input', function() {
        // We don't format on every input to avoid cursor position issues
        // The onchange handler will take care of final formatting
    });
    
    document.getElementById('add-price').addEventListener('input', function() {
        // Same as above
    });
});

// Table Sorting Functionality
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('stocksTable');
    if (!table) return;

    const headers = table.querySelectorAll('th.sortable');
    if (!headers.length) return;
    
    headers.forEach(header => {
        header.addEventListener('click', function() {
            const colIndex = parseInt(this.getAttribute('data-sort'));
            const isAsc = !this.classList.contains('asc');
            
            // Reset all headers
            headers.forEach(h => {
                h.classList.remove('asc', 'desc');
                const icon = h.querySelector('.sort-icon i');
                if (icon) {
                    icon.className = 'fas fa-sort';
                }
            });
            
            // Set the current header
            this.classList.add(isAsc ? 'asc' : 'desc');
            const icon = this.querySelector('.sort-icon i');
            if (icon) {
                icon.className = isAsc ? 'fas fa-sort-up' : 'fas fa-sort-down';
            }
            
            sortTable(colIndex, isAsc);
        });
    });
    
    function sortTable(colIndex, ascending) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        // Store the active row to maintain selection
        const activeRow = tbody.querySelector('.active');
        let activeStock = null;
        if (activeRow) {
            try {
                activeStock = JSON.parse(activeRow.getAttribute('data-stock'));
            } catch (e) {
                console.error('Error parsing active row data:', e);
            }
        }
        
        // Sort the rows
        rows.sort((a, b) => {
            let x, y;
            
            // For Price and Quantity columns (numeric)
            if (colIndex === 3 || colIndex === 4) {
                x = parseFloat(a.cells[colIndex].textContent.replace(/[^0-9.-]+/g, '')) || 0;
                y = parseFloat(b.cells[colIndex].textContent.replace(/[^0-9.-]+/g, '')) || 0;
            } else {
                // For text columns
                x = a.cells[colIndex].textContent.trim().toLowerCase();
                y = b.cells[colIndex].textContent.trim().toLowerCase();
            }
            
            if (ascending) {
                return x > y ? 1 : -1;
            } else {
                return x < y ? 1 : -1;
            }
        });
        
        // Clear the table body and append sorted rows
        while (tbody.firstChild) {
            tbody.removeChild(tbody.firstChild);
        }
        
        rows.forEach(row => {
            tbody.appendChild(row);
            
            // If this was the previously selected row, highlight it
            if (activeStock) {
                try {
                    const rowStock = JSON.parse(row.getAttribute('data-stock'));
                    if (rowStock.id === activeStock.id || rowStock.item_code === activeStock.item_code) {
                        row.classList.add('active');
                    }
                } catch (e) {
                    console.error('Error parsing row data:', e);
                }
            }
        });
    }
});

// Delete stock handler
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

// Non-perishable checkbox logic
document.addEventListener('DOMContentLoaded', function() {
    function setupCheckboxLogic(checkboxId, expiryId) {
        const checkbox = document.getElementById(checkboxId);
        const expiryInput = document.getElementById(expiryId);

        if (checkbox && expiryInput) {
            checkbox.addEventListener('change', function() {
                expiryInput.disabled = this.checked;
                if (this.checked) {
                    expiryInput.value = '';
                }
            });
        }
    }

    setupCheckboxLogic('add-non-perishable', 'add-expiry-date');
    setupCheckboxLogic('edit-non-perishable', 'edit-expiry_date');

    // When opening the edit modal, set the checkbox state
    const editBtn = document.getElementById('editStockBtn');
    if(editBtn) {
        editBtn.addEventListener('click', function() {
            const stock = window.__currentStock;
            const editNonPerishable = document.getElementById('edit-non-perishable');
            const editExpiryDate = document.getElementById('edit-expiry_date');

            if (stock && editNonPerishable && editExpiryDate) {
                // A non-perishable item might have a null, empty, or far-future expiry date.
                // We'll consider it non-perishable if the expiry_date is not set.
                const isNonPerishable = !stock.expiry_date;
                editNonPerishable.checked = isNonPerishable;
                editExpiryDate.disabled = isNonPerishable;
            }
        });
    }
});
</script>