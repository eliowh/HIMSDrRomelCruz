<!-- Common inventory scripts -->
<script>
// Autocomplete functionality for inventory search
(function(){
    // Support multiple id naming conventions used across Inventory and Pharmacy blades
    const containerItem = document.getElementById('suggestions-item_code') || document.getElementById('item_code_suggestions') || null;
    const containerGeneric = document.getElementById('suggestions-generic_name') || document.getElementById('generic_name_suggestions') || null;
    const inputItem = document.getElementById('input-item_code') || document.getElementById('item_code_input') || document.getElementById('f_item_code');
    const inputGeneric = document.getElementById('input-generic_name') || document.getElementById('generic_name_input') || document.getElementById('f_generic_name');
    const brandInput = document.getElementById('input-brand_name') || document.getElementById('brand_name_input') || document.getElementById('f_brand_name');
    const brandSelect = document.getElementById('brand-options') || document.getElementById('brand_name_suggestions') || null;
    // If there are no relevant inputs on this page, bail out
    if(!((inputItem) || (inputGeneric))) return;

    let timer = null; let activeIndex = -1; let lastItems = [];
    function escapeHtml(s){ if(!s) return ''; return String(s).replace(/[&<>'"]/g, (m)=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

    // Server-driven exact-code lookup: when user enters a code (presses Enter or blurs the field),
    // call the server endpoint to get the full record and populate the form.
    async function lookupByCode(code){
        if(!code) return;
        try{
            // Query the masterlist endpoint for an exact item_code match (server-side normalization returns objects)
            const url = '/pharmacy/stocks-reference?search=' + encodeURIComponent(code) + '&type=item_code_exact';
            const res = await fetch(url);
            const txt = await res.text();
            let parsed = null; try{ parsed = JSON.parse(txt); }catch(e){ parsed = null; }
            if(!res.ok || !parsed){
                // If a suggestion was recently selected with this exact code, suppress this error (false positive)
                try{
                    const recent = window.__lastSelectedSuggestion;
                    if (recent && String(recent.value||'') === String(code||'') && (Date.now() - recent.ts) < 1500) {
                        return; // ignore false-negative server response shortly after selection
                    }
                }catch(e){}
                if(typeof showError === 'function') showError('Item not found in masterlist', 'Lookup');
                return;
            }
            const list = Array.isArray(parsed) ? parsed : (parsed.data && Array.isArray(parsed.data) ? parsed.data : []);
            const item = list && list.length ? list[0] : null;
            if(!item){
                // If a suggestion was just selected with this code, suppress the false-negative
                try{
                    const recent = window.__lastSelectedSuggestion;
                    if (recent && String(recent.value||'') === String(code||'') && (Date.now() - recent.ts) < 1500) {
                        return; // ignore false-negative shortly after selection
                    }
                }catch(e){}
                // Also check the input dataset guard if present
                try{
                    const ic = document.getElementById('item_code_input') || document.getElementById('input-item_code');
                    if (ic && ic.dataset && ic.dataset.skipLookup) {
                        // skip showing error; the blur handler will clear the flag
                        return;
                    }
                }catch(e){}
                if(typeof showError === 'function') showError('Item not found in masterlist', 'Lookup');
                return;
            }
            // populate fields
            if(inputItem) inputItem.value = item.item_code || item.id || '';
            if(inputGeneric) inputGeneric.value = item.generic_name || '';
            const priceInput = document.querySelector('input[name="price"]') || document.getElementById('price_input');
            if(priceInput && item.price !== null && item.price !== undefined && item.price !== ''){
                const clean = String(item.price).replace(/,/g,''); const parsedPrice = parseFloat(clean);
                priceInput.value = isNaN(parsedPrice) ? '' : parsedPrice.toFixed(2);
            }
            populateBrandOptions(item);
        }catch(e){ console.warn('lookupByCode failed', e); if(typeof showError === 'function') showError('Lookup failed: ' + e.message, 'Network'); }
    }

    function populateBrandOptions(item){ if(!brandSelect || !brandInput) return; if(!item || !item.generic_name){ if(brandSelect) brandSelect.style.display='none'; return; }
        // Use pharmacy-accessible reference/search endpoint so pharmacy users can get brand options
        fetch(`/pharmacy/stocks-reference?search=` + encodeURIComponent(item.generic_name || item.item_code || ''))
            .then(async r => {
                const ct = (r.headers.get('content-type')||'').toLowerCase();
                const text = await r.text();
                if (!ct.includes('application/json')) return [];
                try { const parsed = JSON.parse(text); return Array.isArray(parsed) ? parsed : (parsed.data && Array.isArray(parsed.data) ? parsed.data : []); } catch(e) { return []; }
            }).then(list=>{
                // Filter matches by generic name
                const matches = Array.isArray(list) ? list.filter(x => x.generic_name && (x.generic_name || '').toLowerCase() === (item.generic_name||'').toLowerCase()) : [];
                
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
                    try {
                        if (!this || !this.options) return;
                        const selIdx = this.selectedIndex;
                        if (typeof selIdx !== 'number' || selIdx < 0) return;
                        const selected = this.options[selIdx];
                        if (!selected || !selected.value) return;

                        // Set brand name
                        if (brandInput) brandInput.value = selected.value;

                        // Set item code if available
                        if (selected.dataset && selected.dataset.itemCode && inputItem) {
                            inputItem.value = selected.dataset.itemCode;
                        }

                        // Set price if available
                        const priceInput = document.querySelector('input[name="price"]');
                        if (selected.dataset && selected.dataset.price && priceInput) {
                            const cleanPrice = String(selected.dataset.price).replace(/,/g, '');
                            const parsedPrice = parseFloat(cleanPrice);
                            priceInput.value = isNaN(parsedPrice) ? '' : parsedPrice.toFixed(2);
                        }
                    } catch(e) {
                        console.warn('brandSelect onchange handler error', e);
                    }
                };
                
                // If only one brand, auto-select it
                if (brands.length === 1) {
                    brandSelect.selectedIndex = 1; // First real option (after placeholder)
                    brandSelect.onchange(); // Trigger the change handler
                }
        }).catch(e=>{ console.error('brand fetch failed', e); if(brandSelect) brandSelect.style.display='none'; });
    }

    // We no longer render a client-side suggestion dropdown in Pharmacy modals.
    // Instead: on Enter or blur of the item code field we perform an exact server lookup,
    // and on blur of the generic field we fetch brand options for that generic name.
    function fetchBrandsByGenericName(q){ if(!q || q.length < 2){ if(brandSelect) brandSelect.style.display='none'; return; } const temp = { generic_name: q }; populateBrandOptions(temp); }

    if(inputItem){
        // reference to custom checkbox if present
        const customCheckbox = document.getElementById('add-custom-medicine') || null;
        // When user presses Enter inside item_code, do an exact lookup (unless custom checkbox checked)
        inputItem.addEventListener('keydown', function(e){
            if(e.key === 'Enter'){
                try{ if (customCheckbox && customCheckbox.checked) return; }catch(err){}
                e.preventDefault(); lookupByCode(this.value);
            }
        });
        // Also trigger lookup when the field blurs. If a suggestion was just clicked, a short-lived
        // dataset flag will be present to skip this automatic lookup to avoid duplicate/error notifications.
        inputItem.addEventListener('blur', function(){
            try{
                if (this.dataset && this.dataset.skipLookup) { try{ delete this.dataset.skipLookup; }catch(e){}; return; }
            } catch(e) {}
            try{ if (customCheckbox && customCheckbox.checked) return; }catch(err){}
            if(this.value) setTimeout(()=> lookupByCode(this.value), 50);
        });
    }

    if(inputGeneric){
        // When generic input blurs, fetch brand options from server for the typed generic name
        inputGeneric.addEventListener('blur', function(){ if(this.value) setTimeout(()=> fetchBrandsByGenericName(this.value), 60); });
    }
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
        // Handle price display with comma parsing
        if (stock.price !== null && stock.price !== undefined && stock.price !== '') {
            const cleanPrice = String(stock.price).replace(/,/g, '');
            const parsedPrice = parseFloat(cleanPrice);
            document.getElementById('md-price').textContent = isNaN(parsedPrice) ? '-' : '₱' + parsedPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        } else {
            document.getElementById('md-price').textContent = '-';
        }
        document.getElementById('md-quantity').textContent = or(stock.quantity ?? 0);
        document.getElementById('md-reorder_level').textContent = or(stock.reorder_level);
        document.getElementById('md-expiry_date').textContent = or(stock.expiry_date);
        document.getElementById('md-supplier').textContent = or(stock.supplier);
        document.getElementById('md-batch_number').textContent = or(stock.batch_number);
        document.getElementById('md-date_received').textContent = or(stock.date_received);
        // store current stock for editing
    window.__currentStock = stock;
    // also set the older global name used elsewhere so both callers work
    try{ window.currentPharmacyStock = stock; }catch(e){}
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
            if (!s) {
                showError('No stock selected', 'Selection Error');
                return;
            }
            document.getElementById('edit-id').value = s.id || s.item_code;
            document.getElementById('edit-item_code').value = s.item_code || '';
            document.getElementById('edit-generic_name').value = s.generic_name || '';
            document.getElementById('edit-brand_name').value = s.brand_name || '';
            // Handle price with comma parsing for edit form
            if (s.price !== null && s.price !== undefined && s.price !== '') {
                const cleanPrice = String(s.price).replace(/,/g, '');
                const parsedPrice = parseFloat(cleanPrice);
                document.getElementById('edit-price').value = isNaN(parsedPrice) ? '' : parsedPrice.toFixed(2);
            } else {
                document.getElementById('edit-price').value = '';
            }
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
    
    // Also add event listeners for when the modals are opened (guarded)
    const editStockBtnEl = document.getElementById('editStockBtn');
    if (editStockBtnEl) {
        editStockBtnEl.addEventListener('click', function() {
            setTimeout(() => {
                const editPrice = document.getElementById('edit-price');
                if (editPrice && editPrice.value !== '') {
                    formatDecimal(editPrice, 2);
                }
            }, 100); // Short delay to ensure the field is populated
        });
    }

    // For input events to format as user types (guarded)
    const editPriceField = document.getElementById('edit-price');
    if (editPriceField) {
        editPriceField.addEventListener('input', function() {
            // We don't format on every input to avoid cursor position issues
            // The onchange handler will take care of final formatting
        });
    }
    const addPriceField = document.getElementById('add-price') || document.getElementById('price_input');
    if (addPriceField) {
        addPriceField.addEventListener('input', function() {
            // Same as above
        });
    }
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

// Delete stock handler (pharmacy CRUD)
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.delete-btn').forEach(function(btn){
        btn.addEventListener('click', function(){
            const id = this.getAttribute('data-id');
            showConfirm('Are you sure you want to delete this stock item? This cannot be undone.', 'Confirm Deletion', function(confirmed) {
                if (!confirmed) return;

                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch('/pharmacy/stocks/' + encodeURIComponent(id), {
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
                        showError('Delete failed: ' + msg, 'Deletion Error');
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
                    showSuccess('Stock item deleted successfully!', 'Deleted');
                }).catch(e => { console.error(e); showError('Delete error: ' + e.message, 'Network Error'); });
            });
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