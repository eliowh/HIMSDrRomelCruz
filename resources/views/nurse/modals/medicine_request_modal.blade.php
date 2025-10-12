<!-- Medicine Request Modal (Nurse -> Pharmacy) -->
<div id="medicineRequestModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeMedicineRequestModal()">&times;</span>
        <h3>Request Medicine (Send to Pharmacy)</h3>
        <form id="medicineRequestForm">
            <input type="hidden" id="medRequestPatientId" name="patient_id">
            <input type="hidden" id="medRequestAdmissionId" name="admission_id">
            <div class="form-group">
                <label>Patient:</label>
                <p id="medRequestPatientInfo" class="patient-info-display"></p>
            </div>
            
            <div class="form-group" id="medAdmissionInfoGroup" style="display: none;">
                <label>Current Admission:</label>
                <p id="medRequestAdmissionInfo" class="admission-info-display"></p>
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

    // Helper function to parse price values that may contain commas
    function parsePrice(value) {
        if (typeof value === 'number') return value;
        if (typeof value !== 'string') return 0;
        // Remove commas, currency symbols, and extra spaces, then parse as float
        const cleaned = value.replace(/[,₱$\s]/g, '');
        const parsed = parseFloat(cleaned);
        return isNaN(parsed) ? 0 : parsed;
    }

    function clearSuggestions() { suggestions.innerHTML=''; suggestions.style.display='none'; }

    function renderSuggestions(items){
        suggestions.innerHTML='';
        if(!items || !items.length) { suggestions.innerHTML = '<div class="pharmacy-suggestion-no-results">No results</div>'; suggestions.style.display='block'; return; }
        items.forEach(it=>{
            const div = document.createElement('div');
            div.className = 'pharmacy-suggestion-item';
            div.style.padding='8px'; div.style.borderBottom='1px solid #eee'; div.style.cursor='pointer';
            div.textContent = `${it.generic_name || ''} ${it.brand_name ? '— '+it.brand_name : ''} (₱${parsePrice(it.price).toFixed(2)})`;
            div.addEventListener('click', ()=>{
                medSearch.value = (it.generic_name || it.brand_name || it.item_code);
                itemCode.value = it.item_code || '';
                unitPrice.value = (typeof it.price !== 'undefined' && it.price !== null) ? parsePrice(it.price).toFixed(2) : '';
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
        const up = parsePrice(unitPrice.value);
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
    // include active admission id (required by server validator)
    const admissionId = document.getElementById('medRequestAdmissionId').value;
    payload.append('admission_id', admissionId || '');
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

// Block submission if admission_id not set (safety)
document.getElementById('medicineRequestForm').addEventListener('submit', function(e){
    const admissionId = document.getElementById('medRequestAdmissionId').value;
    if (!admissionId) {
        e.preventDefault();
        nurseError('No Active Admission', 'This patient has no active admission. Please verify before requesting medicine.');
        return false;
    }
});

function openMedicineRequestModal(patientId, patientName, patientNo){
    window.isModalOpen = true;
    // Reset form first to ensure a clean state
    document.getElementById('medicineRequestForm').reset();

    document.getElementById('medRequestPatientId').value = patientId;
    document.getElementById('medRequestPatientInfo').textContent = `${patientName} (ID: ${patientNo})`;

    // Get the active admission ID for this patient
    fetch(`/api/patients/${patientId}/active-admission`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.admission) {
                document.getElementById('medRequestAdmissionId').value = data.admission.id;
                
                // Show admission info
                const admissionInfo = `Admission #${data.admission.admission_number} - Room ${data.admission.room_no || 'N/A'} - Dr. ${data.admission.doctor_name || 'N/A'}`;
                document.getElementById('medRequestAdmissionInfo').textContent = admissionInfo;
                document.getElementById('medAdmissionInfoGroup').style.display = 'block';
            } else {
                // No active admission - show error
                nurseError('No Active Admission', 'This patient has no active admission. Please create an admission first before requesting medicine.');
                closeMedicineRequestModal();
                return;
            }
        })
        .catch(error => {
            console.error('Error fetching active admission:', error);
            nurseError('Error', 'Unable to verify patient admission status. Please try again.');
            closeMedicineRequestModal();
            return;
        });
    
    document.getElementById('medicineRequestModal').classList.add('show');
}

function closeMedicineRequestModal(){
    document.getElementById('medicineRequestModal').classList.remove('show');
    setTimeout(()=>{ window.isModalOpen = false; }, 300);
}
</script>

<style>
.admission-info-display {
    background: #e8f5e8;
    border: 1px solid #28a745;
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 500;
    color: #155724;
    margin: 0;
}
</style>
