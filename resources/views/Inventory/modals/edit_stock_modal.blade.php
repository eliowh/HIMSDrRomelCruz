<!-- Edit Stock Modal -->
<div id="editStockModal" class="modal mini">
    <div class="modal-content">
        <span class="close" onclick="closeEditStockModal()">&times;</span>
        <h3>Edit Stock</h3>
        <form id="editStockForm">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="id" id="edit-id">

            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                <div class="form-group">
                    <label>Item Code</label>
                    <input id="edit-item_code" name="item_code" />
                </div>
                <div class="form-group">
                    <label>Generic Name</label>
                    <input id="edit-generic_name" name="generic_name" />
                </div>
                <div class="form-group">
                    <label>Brand Name</label>
                    <input id="edit-brand_name" name="brand_name" />
                </div>
                <div class="form-group">
                    <label>Price</label>
                    <input id="edit-price" name="price" type="number" step="0.01" min="0" placeholder="0.00" onchange="formatDecimal(this, 2)" />
                </div>
            </div>

            <div class="form-group" style="margin-top:8px;">
                <label>Quantity</label>
                <input id="edit-quantity" name="quantity" type="number" />
            </div>

            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:8px;">
                <div class="form-group">
                    <label>Reorder Level</label>
                    <input id="edit-reorder_level" name="reorder_level" type="number" value="10" />
                </div>
                <div class="form-group">
                    <label>Expiry Date</label>
                    <input id="edit-expiry_date" name="expiry_date" type="date" />
                </div>
            </div>

            <div class="form-group" style="margin-top: 8px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" id="edit-non-perishable" name="non_perishable" style="width: auto; height: auto; margin-right: 5px;">
                    <span>Non-perishable</span>
                </label>
            </div>

            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:8px;">
                <div class="form-group">
                    <label>Supplier</label>
                    <input id="edit-supplier" name="supplier" />
                </div>
                <div class="form-group">
                    <label>Batch Number</label>
                    <input id="edit-batch_number" name="batch_number" />
                </div>
            </div>
            
            <div class="form-group" style="margin-top:8px;">
                <label>Date Received</label>
                <input id="edit-date_received" name="date_received" type="date" />
            </div>

            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:12px;">
                <button type="button" class="btn cancel-btn" onclick="closeEditStockModal()">Cancel</button>
                <button type="submit" class="btn submit-btn">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
// Edit Stock modal functions
function openEditStockModal(){
    const modal = document.getElementById('editStockModal');
    modal.classList.add('show'); modal.classList.add('open');
}
function closeEditStockModal(){
    const modal = document.getElementById('editStockModal');
    modal.classList.remove('show'); modal.classList.remove('open');
    document.getElementById('editStockForm').reset();
}

document.getElementById('editStockForm').addEventListener('submit', function(e){
    e.preventDefault();
    const id = document.getElementById('edit-id').value;
    if (!id) {
        showError('Missing stock ID', 'Validation Error');
        return;
    }

    const payload = {
        item_code: document.getElementById('edit-item_code').value,
        generic_name: document.getElementById('edit-generic_name').value,
        brand_name: document.getElementById('edit-brand_name').value,
        price: document.getElementById('edit-price').value,
        quantity: document.getElementById('edit-quantity').value,
        reorder_level: document.getElementById('edit-reorder_level').value,
        expiry_date: document.getElementById('edit-expiry_date').value,
        supplier: document.getElementById('edit-supplier').value,
        batch_number: document.getElementById('edit-batch_number').value,
        date_received: document.getElementById('edit-date_received').value,
        _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    };

    const submitBtn = this.querySelector('.submit-btn');
    const orig = submitBtn.textContent; submitBtn.textContent = 'Saving...'; submitBtn.disabled = true;

    fetch('{{ url('/inventory/stocks') }}/' + encodeURIComponent(id), {
        method: 'PATCH',
        credentials: 'same-origin',
        body: JSON.stringify(payload),
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Content-Type': 'application/json; charset=utf-8' }
    }).then(async res => {
        const txt = await res.text();
        const ct = (res.headers.get('content-type')||'').toLowerCase();
        if (!res.ok) {
            let msg = txt; if (ct.includes('application/json')) { try{ msg = JSON.parse(txt).message || txt; }catch(e){} }
            showError('Update failed: ' + JSON.stringify(msg), 'Update Error');
            return;
        }
        if (ct.includes('application/json')) {
            let j = null;
            try { j = JSON.parse(txt); } catch(e){ console.error('Invalid JSON', txt); showError('Invalid server response', 'Server Error'); return; }

            // If validation errors or not ok, show details
            if (!j.ok) {
                // If Laravel validation errors present, show them
                if (j.errors) {
                    const msgs = [];
                    for (const k in j.errors) { if (Array.isArray(j.errors[k])) msgs.push(j.errors[k].join(', ')); }
                    showError('Update failed: ' + msgs.join(' / '), 'Validation Error');
                    console.warn('Validation errors', j.errors);
                    return;
                }
                showError(j.message || 'Update failed', 'Update Error');
                console.warn('Update failed payload', j);
                return;
            }

            // Success: update UI
            console.log('Update response', j);
            window.__currentStock = j.stock;
            renderStock(j.stock);

            // Try to update matching row; prefer matching by numeric id, then by original matchKey, then by previous currentStock
            const rows = document.querySelectorAll('.stock-row');
            const matchKey = id; // original id/item_code submitted
            let updated = false;

            rows.forEach(r => {
                if (updated) return;
                try{
                    const s = JSON.parse(r.getAttribute('data-stock'));
                    const sameById = s && s.id && j.stock.id && String(s.id) === String(j.stock.id);
                    const sameByOldCode = s && s.item_code && String(s.item_code) === String(matchKey);
                    const sameBySelected = window.__currentStock && s && ((s.id && window.__currentStock.id && String(s.id) === String(window.__currentStock.id)) || (s.item_code && window.__currentStock.item_code && String(s.item_code) === String(window.__currentStock.item_code)));

                    if (sameById || sameByOldCode || sameBySelected) {
                        r.setAttribute('data-stock', JSON.stringify(j.stock));
                        const tds = r.querySelectorAll('td');
                        if (tds && tds.length >= 5) {
                            tds[0].textContent = j.stock.item_code || '';
                            tds[1].textContent = j.stock.generic_name || '';
                            tds[2].textContent = j.stock.brand_name || '';
                            tds[3].textContent = (j.stock.price !== null && j.stock.price !== undefined && j.stock.price !== '') ? parseFloat(j.stock.price).toFixed(2) : '-';
                            tds[4].textContent = j.stock.quantity ?? 0;
                        }
                        updated = true;
                    }
                } catch(e) { console.error('row update parse error', e); }
            });

            if (!updated) {
                console.warn('No matching row found to update, reloading to reflect changes.');
                // fallback: reload so UI reflects server state
                location.reload();
                return;
            }

            closeEditStockModal();
            showSuccess('Stock updated successfully!', 'Stock Updated');
            return;
        } else {
            // Non-JSON response: reload to reflect changes
            console.warn('Non-JSON response, reloading');
            location.reload();
        }
    }).catch(e => { console.error(e); showError('Update failed: ' + e.message, 'Network Error'); })
    .finally(()=>{ submitBtn.textContent = orig; submitBtn.disabled = false; });
});

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('editStockModal');
    if (event.target === modal) {
        closeEditStockModal();
    }
});
</script>
