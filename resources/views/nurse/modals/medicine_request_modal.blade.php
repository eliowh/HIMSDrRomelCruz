<!-- Medicine Request Modal (Nurse -> Pharmacy) -->
<div id="medicineRequestModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeMedicineRequestModal()">&times;</span>
        <h3>Request Medicine (Send to Pharmacy)</h3>
        <form id="medicineRequestForm">
            <input type="hidden" id="medRequestPatientId" name="patient_id">
            <div class="form-group">
                <label>Patient:</label>
                <p id="medRequestPatientInfo" class="patient-info-display"></p>
            </div>

            <div class="form-group">
                <label for="med_search">Search Medicine (Generic or Brand): *</label>
                <div class="pharmacy-search-container">
                    <input id="med_search" class="pharmacy-search-input" type="text" placeholder="Type generic or brand name" autocomplete="off">
                    <div class="pharmacy-suggestions"></div>
                </div>
                <small class="helper">Select an item to auto-fill item code and price.</small>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="med_item_code">Item Code</label>
                    <input id="med_item_code" name="item_code" type="text" readonly>
                </div>
                <div class="form-group">
                    <label for="med_unit_price">Unit Price</label>
                    <input id="med_unit_price" name="unit_price" type="number" step="0.01" readonly>
                </div>
                <div class="form-group">
                    <label for="med_quantity">Quantity *</label>
                    <input id="med_quantity" name="quantity" type="number" min="1" value="1" required>
                </div>
                <div class="form-group">
                    <label for="med_total_price">Total Price</label>
                    <input id="med_total_price" name="total_price" type="text" readonly>
                </div>
            </div>

            <div class="form-group">
                <label for="med_notes">Notes / Clinical Indication</label>
                <textarea id="med_notes" name="notes" rows="3" placeholder="e.g., For outpatient, dosing instructions, patient allergies"></textarea>
            </div>

            <div class="form-actions">
                <button type="button" class="btn cancel-btn" onclick="closeMedicineRequestModal()">Cancel</button>
                <button type="submit" class="btn submit-btn">Submit Request to Pharmacy</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('medicineRequestModal');
    const medSearch = document.getElementById('med_search');
    const suggestions = modal.querySelector('.pharmacy-suggestions');
    const itemCode = document.getElementById('med_item_code');
    const unitPrice = document.getElementById('med_unit_price');
    const quantity = document.getElementById('med_quantity');
    const totalPriceEl = document.getElementById('med_total_price');
    const notes = document.getElementById('med_notes');
    let timer = null;
    let itemsCache = [];

    function clearSuggestions() { suggestions.innerHTML=''; suggestions.style.display='none'; }

    function renderSuggestions(items){
        suggestions.innerHTML='';
        if(!items || !items.length) { suggestions.innerHTML = '<div class="pharmacy-suggestion-no-results">No results</div>'; suggestions.style.display='block'; return; }
        items.forEach(it=>{
            const div = document.createElement('div');
            div.className = 'pharmacy-suggestion-item';
            div.style.padding='8px'; div.style.borderBottom='1px solid #eee'; div.style.cursor='pointer';
            div.textContent = `${it.generic_name || ''} ${it.brand_name ? '— '+it.brand_name : ''} (₱${parseFloat(it.price).toFixed(2)})`;
            div.addEventListener('click', ()=>{
                medSearch.value = (it.generic_name || it.brand_name || it.item_code);
                itemCode.value = it.item_code || '';
                unitPrice.value = (typeof it.price !== 'undefined' && it.price !== null) ? parseFloat(it.price).toFixed(2) : '';
                // Update total price based on current quantity
                updateTotalPrice();
                suggestions.innerHTML=''; suggestions.style.display='none';
                itemsCache = [it];
            });
            suggestions.appendChild(div);
        });
        suggestions.style.display='block';
    }

    function updateTotalPrice(){
        const up = parseFloat(unitPrice.value) || 0;
        const q = parseInt(quantity.value) || 0;
        const total = up * q;
        // Show formatted currency with two decimals
        totalPriceEl.value = total.toFixed(2);
    }

        medSearch.addEventListener('input', ()=>{
        clearTimeout(timer);
        const q = medSearch.value.trim();
        if(!q) { clearSuggestions(); return; }
        timer = setTimeout(()=>{
            fetch('/nurse/pharmacy/stocks-reference?search='+encodeURIComponent(q))
                .then(r=>r.json())
                .then(j=>{
                    // Accept either {success:true,data:[]} or direct array
                    if (j && j.success && Array.isArray(j.data)) { itemsCache = j.data; renderSuggestions(j.data); }
                    else if (Array.isArray(j)) { itemsCache = j; renderSuggestions(j); }
                })
                .catch(e=>{ console.error('Medicine lookup error', e); });
        }, 250);
    });

    // Update total when quantity changes
    quantity.addEventListener('input', function(){ updateTotalPrice(); });

    document.addEventListener('click', function(e){ if(!modal.contains(e.target) || e.target === modal) { /* allow outside click to close handled by nurse page */ } if(!e.target.closest('.pharmacy-search-container')) clearSuggestions(); });

    // Form submit
    document.getElementById('medicineRequestForm').addEventListener('submit', function(e){
        e.preventDefault();
        const pid = document.getElementById('medRequestPatientId').value;
        if(!pid){ alert('Patient missing'); return; }
        const payload = new FormData();
        payload.append('patient_id', pid);
        payload.append('item_code', itemCode.value || '');
        payload.append('generic_name', medSearch.value);
        payload.append('brand_name', '');
        payload.append('quantity', quantity.value);
        payload.append('unit_price', unitPrice.value || 0);
        payload.append('notes', notes.value || '');

        const btn = this.querySelector('.submit-btn');
        const txt = btn.textContent; btn.textContent = 'Submitting...'; btn.disabled = true;

        fetch('/nurse/pharmacy-orders', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: payload
        })
        .then(r=>r.json())
        .then(j=>{
            if(j.success){
                nurseSuccess('Medicine Requested', 'Request sent to Pharmacy');
                closeMedicineRequestModal();
            } else {
                nurseError('Request Failed', j.message || 'Unable to submit request');
            }
        })
        .catch(e=>{ console.error('Submit error', e); nurseError('Request Failed', 'Network error'); })
        .finally(()=>{ btn.textContent = txt; btn.disabled = false; });
    });
});

function openMedicineRequestModal(patientId, patientName, patientNo){
    window.isModalOpen = true;
    document.getElementById('medRequestPatientId').value = patientId;
    document.getElementById('medRequestPatientInfo').textContent = `${patientName} (ID: ${patientNo})`;
    document.getElementById('medicineRequestForm').reset();
    document.getElementById('medicineRequestModal').classList.add('show');
}

function closeMedicineRequestModal(){
    document.getElementById('medicineRequestModal').classList.remove('show');
    setTimeout(()=>{ window.isModalOpen = false; }, 300);
}
</script>
