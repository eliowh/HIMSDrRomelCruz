<!-- Add Stock Modal -->
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
                    <input id="add-price" name="price" type="number" step="1.00" min="0" placeholder="0.00" onchange="formatDecimal(this, 2)" />
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
// Add Stock modal functions
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
document.addEventListener('DOMContentLoaded', function() {
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
});

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('addStockModal');
    if (event.target === modal) {
        closeAddStockModal();
    }
});
</script>
