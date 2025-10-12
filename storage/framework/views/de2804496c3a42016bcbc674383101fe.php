<!-- Medicine History Modal -->
<div id="medicineHistoryModal" class="modal">
    <div class="modal-content medicine-history-modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-pills"></i> Medicine History</h3>
            <span class="close" onclick="closeMedicineHistoryModal()">&times;</span>
        </div>
        
        <div class="patient-info-summary">
            <div class="patient-details">
                <strong id="medicine-history-patient-name">Patient Name</strong>
                <span id="medicine-history-patient-no">Patient No</span>
            </div>
            <div class="history-stats">
                <span class="stat">
                    <i class="fas fa-capsules"></i>
                    <span id="total-medicines">0</span> Total Medicines
                </span>
                <span class="stat">
                    <i class="fas fa-dollar-sign"></i>
                    ₱<span id="total-cost">0.00</span> Total Cost
                </span>
            </div>
        </div>
        
        <div class="medicine-history-content">
            <!-- Medicine history will be loaded here -->
            <div id="medicineHistoryLoading" class="loading-spinner">
                <div class="spinner"></div>
                <p>Loading medicine history...</p>
            </div>
            
            <div id="medicineHistoryError" class="medicine-history-error" style="display:none;">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Error loading medicine history. Please try again.</p>
                <button class="btn retry-btn" onclick="retryLoadMedicineHistory()">
                    <i class="fas fa-redo"></i> Retry
                </button>
            </div>
            
            <div id="medicineHistoryEmpty" class="medicine-history-empty" style="display:none;">
                <i class="fas fa-info-circle"></i>
                <h4>No Medicine History</h4>
                <p>No medicines have been dispensed to this patient yet.</p>
            </div>
            
            <div id="medicineHistoryList" class="medicine-history-list" style="display:none;">
                <!-- Medicine records will be loaded here -->
            </div>
        </div>
        
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeMedicineHistoryModal()">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
    </div>
</div>

<style>
.medicine-history-modal-content {
    max-width: 900px;
    width: 95%;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    border-bottom: 1px solid #eee;
    background: #f8f9fa;
}

.modal-header h3 {
    margin: 0;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 10px;
}

.patient-info-summary {
    padding: 20px 25px;
    background: #fff;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.patient-details strong {
    font-size: 18px;
    color: #2c3e50;
    margin-right: 15px;
}

.patient-details span {
    color: #7f8c8d;
    font-size: 14px;
}

.history-stats {
    display: flex;
    gap: 25px;
}

.stat {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #34495e;
    font-size: 14px;
    font-weight: 500;
}

.stat i {
    color: #3498db;
    font-size: 16px;
}

.medicine-history-content {
    max-height: 500px;
    overflow-y: auto;
    padding: 25px;
}

.loading-spinner {
    text-align: center;
    padding: 40px;
}

.spinner {
    border: 3px solid #f3f3f3;
    border-top: 3px solid #3498db;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin: 0 auto 15px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.medicine-history-error,
.medicine-history-empty {
    text-align: center;
    padding: 40px;
    color: #7f8c8d;
}

.medicine-history-error i,
.medicine-history-empty i {
    font-size: 48px;
    color: #e74c3c;
    margin-bottom: 15px;
    display: block;
}

.medicine-history-empty i {
    color: #95a5a6;
}

.medicine-history-error h4,
.medicine-history-empty h4 {
    margin: 15px 0 10px;
    color: #2c3e50;
}

.medicine-history-list {
    display: grid;
    gap: 15px;
}

.medicine-history-item {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    transition: all 0.2s ease;
    border-left: 4px solid #28a745;
}

.medicine-history-item:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.medicine-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.medicine-name {
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
}

.medicine-quantity {
    background: #28a745;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 5px;
}

.medicine-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 15px;
}

.medicine-detail {
    display: flex;
    flex-direction: column;
}

.medicine-detail label {
    font-size: 12px;
    font-weight: 600;
    color: #7f8c8d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}

.medicine-detail span {
    font-size: 14px;
    color: #2c3e50;
    font-weight: 500;
}

.medicine-meta {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 6px;
    font-size: 13px;
    color: #5a6c7d;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.medicine-notes {
    margin-top: 12px;
    padding: 12px;
    background: #e3f2fd;
    border-left: 3px solid #2196f3;
    border-radius: 4px;
}

.medicine-notes strong {
    color: #1976d2;
    font-size: 13px;
}

.medicine-notes p {
    margin: 5px 0 0 0;
    font-size: 13px;
    color: #424242;
    line-height: 1.4;
}

.modal-footer {
    padding: 20px 25px;
    border-top: 1px solid #eee;
    background: #f8f9fa;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.retry-btn {
    background: #e74c3c;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.2s;
}

.retry-btn:hover {
    background: #c0392b;
}

/* Responsive design */
@media (max-width: 768px) {
    .medicine-history-modal-content {
        width: 98%;
        margin: 1% auto;
    }
    
    .patient-info-summary {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
    
    .history-stats {
        justify-content: space-between;
        width: 100%;
    }
    
    .medicine-details {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    
    .medicine-meta {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
let currentMedicinePatientId = null;
let currentMedicinePatientName = '';
let currentMedicinePatientNo = '';

function openMedicineHistoryModal(patientId, patientName, patientNo) {
    console.log('Opening medicine history modal for patient:', patientId, patientName, patientNo); // Debug log
    
    currentMedicinePatientId = patientId;
    currentMedicinePatientName = patientName;
    currentMedicinePatientNo = patientNo;
    
    // Set patient info
    document.getElementById('medicine-history-patient-name').textContent = patientName;
    document.getElementById('medicine-history-patient-no').textContent = `Patient No: ${patientNo}`;
    
    // Show modal using the same approach as other modals
    const modal = document.getElementById('medicineHistoryModal');
    console.log('Modal element found:', modal); // Debug log
    if (modal) {
        modal.classList.add('show');
        console.log('Modal show class added, modal should be visible now'); // Debug log
    } else {
        console.error('Medicine history modal element not found!');
    }
    
    // Load medicine history
    loadMedicineHistory(patientId);
}

// Make the function globally accessible
window.openMedicineHistoryModal = openMedicineHistoryModal;

function closeMedicineHistoryModal() {
    console.log('Closing medicine history modal'); // Debug log
    const modal = document.getElementById('medicineHistoryModal');
    if (modal) {
        modal.classList.remove('show');
        console.log('Modal show class removed'); // Debug log
    }
    
    // Reset state
    currentMedicinePatientId = null;
    currentMedicinePatientName = '';
    currentMedicinePatientNo = '';
}

// Make the function globally accessible
window.closeMedicineHistoryModal = closeMedicineHistoryModal;

function loadMedicineHistory(patientId) {
    // Show loading state
    showMedicineHistoryLoading();
    
    // Get the currently selected admission ID from the main interface
    let admissionId = null;
    try {
        // Look for the selected admission in the patient details area
        const selectedAdmission = document.querySelector('.admission-item.selected-admission');
        if (selectedAdmission) {
            admissionId = selectedAdmission.getAttribute('data-admission-id');
            console.log('Found selected admission ID:', admissionId);
        } else {
            // Fallback: look for the active admission if no specific selection
            const activeAdmission = document.querySelector('.admission-item.active-admission');
            if (activeAdmission) {
                admissionId = activeAdmission.getAttribute('data-admission-id');
                console.log('Using active admission ID:', admissionId);
            }
        }
    } catch (e) {
        console.log('Could not determine current admission ID:', e.message);
    }
    
    // Build API URL with admission filter if we have an active admission
    const apiUrl = admissionId ? 
        `/api/patients/${patientId}/medicines?admission_id=${admissionId}` : 
        `/api/patients/${patientId}/medicines`;
    
    console.log('Loading medicine history with URL:', apiUrl, 'Admission ID:', admissionId);
    
    // Fetch medicine history (admission-specific if admission ID available)
    fetch(apiUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.medicines && data.medicines.length > 0) {
                displayMedicineHistory(data.medicines);
            } else {
                showMedicineHistoryEmpty();
            }
        })
        .catch(error => {
            console.error('Error loading medicine history:', error);
            showMedicineHistoryError();
        });
}

// Make the function globally accessible
window.loadMedicineHistory = loadMedicineHistory;

function showMedicineHistoryLoading() {
    document.getElementById('medicineHistoryLoading').style.display = 'block';
    document.getElementById('medicineHistoryError').style.display = 'none';
    document.getElementById('medicineHistoryEmpty').style.display = 'none';
    document.getElementById('medicineHistoryList').style.display = 'none';
}

function showMedicineHistoryError() {
    document.getElementById('medicineHistoryLoading').style.display = 'none';
    document.getElementById('medicineHistoryError').style.display = 'block';
    document.getElementById('medicineHistoryEmpty').style.display = 'none';
    document.getElementById('medicineHistoryList').style.display = 'none';
}

function showMedicineHistoryEmpty() {
    document.getElementById('medicineHistoryLoading').style.display = 'none';
    document.getElementById('medicineHistoryError').style.display = 'none';
    document.getElementById('medicineHistoryEmpty').style.display = 'block';
    document.getElementById('medicineHistoryList').style.display = 'none';
    
    // Update stats
    document.getElementById('total-medicines').textContent = '0';
    document.getElementById('total-cost').textContent = '0.00';
}

function displayMedicineHistory(medicines) {
    const listContainer = document.getElementById('medicineHistoryList');
    
    // Calculate totals
    const totalMedicines = medicines.length;
    const totalCost = medicines.reduce((sum, med) => sum + (parseFloat(med.total_price) || 0), 0);
    
    // Update stats
    document.getElementById('total-medicines').textContent = totalMedicines;
    document.getElementById('total-cost').textContent = totalCost.toFixed(2);
    
    // Generate medicine history HTML
    listContainer.innerHTML = medicines.map(medicine => {
        const medicineName = medicine.medicine_name || 'Unknown Medicine';
        const quantity = medicine.quantity || 0;
        const unitPrice = medicine.unit_price ? parseFloat(medicine.unit_price).toFixed(2) : '0.00';
        const totalPrice = medicine.total_price ? parseFloat(medicine.total_price).toFixed(2) : '0.00';
        const dispensedAt = medicine.dispensed_at ? formatDateTime(medicine.dispensed_at) : 'Unknown date';
        const dispensedBy = medicine.dispensed_by ? formatName(medicine.dispensed_by) : 'Unknown';
        const notes = medicine.notes || '';
        
        return `
            <div class="medicine-history-item">
                <div class="medicine-header">
                    <h4 class="medicine-name">${medicineName}</h4>
                    <div class="medicine-quantity">
                        <i class="fas fa-pills"></i>
                        ${quantity} units
                    </div>
                </div>
                
                <div class="medicine-details">
                    <div class="medicine-detail">
                        <label>Unit Price</label>
                        <span>₱${unitPrice}</span>
                    </div>
                    <div class="medicine-detail">
                        <label>Total Cost</label>
                        <span>₱${totalPrice}</span>
                    </div>
                </div>
                
                <div class="medicine-meta">
                    <div>
                        <strong>Dispensed:</strong> ${dispensedAt}
                    </div>
                    <div>
                        <strong>By:</strong> ${dispensedBy}
                    </div>
                </div>
                
                ${notes ? `
                    <div class="medicine-notes">
                        <strong>Notes:</strong>
                        <p>${notes}</p>
                    </div>
                ` : ''}
            </div>
        `;
    }).join('');
    
    // Show results
    document.getElementById('medicineHistoryLoading').style.display = 'none';
    document.getElementById('medicineHistoryError').style.display = 'none';
    document.getElementById('medicineHistoryEmpty').style.display = 'none';
    document.getElementById('medicineHistoryList').style.display = 'block';
}

// Make display functions globally accessible
window.showMedicineHistoryLoading = showMedicineHistoryLoading;
window.showMedicineHistoryError = showMedicineHistoryError;
window.showMedicineHistoryEmpty = showMedicineHistoryEmpty;
window.displayMedicineHistory = displayMedicineHistory;

function retryLoadMedicineHistory() {
    if (currentMedicinePatientId) {
        loadMedicineHistory(currentMedicinePatientId);
    }
}

// Make the function globally accessible
window.retryLoadMedicineHistory = retryLoadMedicineHistory;

// Helper functions (use existing ones from main page)
function formatDateTime(dateStr) {
    if (!dateStr) return '-';
    try {
        const date = new Date(dateStr);
        if (isNaN(date.getTime())) return '-';
        return date.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (e) {
        return dateStr.split('T')[0];
    }
}

function formatName(name) {
    if (!name) return '-';
    return name.toLowerCase().replace(/\b\w/g, l => l.toUpperCase());
}

// Make helper functions globally accessible
window.formatDateTime = formatDateTime;
window.formatName = formatName;

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('medicineHistoryModal');
    if (event.target === modal) {
        closeMedicineHistoryModal();
    }
});
</script>
<?php /**PATH D:\xamppLatest\htdocs\HIMSDrRomelCruz\resources\views/nurse/modals/medicine_history_modal.blade.php ENDPATH**/ ?>