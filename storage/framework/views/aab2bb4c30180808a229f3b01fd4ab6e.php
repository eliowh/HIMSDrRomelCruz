<!-- Lab Results History Modal -->
<div id="labResultsModal" class="modal">
    <div class="modal-content lab-results-modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-flask"></i> Lab Results History</h3>
            <span class="close" onclick="closeLabResultsModal()">&times;</span>
        </div>
        
        <div class="patient-info-summary">
            <div class="patient-details">
                <strong id="lab-results-patient-name">Patient Name</strong>
                <span id="lab-results-patient-no">Patient No</span>
            </div>
            <div class="history-stats">
                <span class="stat">
                    <i class="fas fa-vial"></i>
                    <span id="total-lab-tests">0</span> Total Tests
                </span>
                <span class="stat">
                    <i class="fas fa-check-circle"></i>
                    <span id="completed-tests">0</span> Completed
                </span>
                <span class="stat">
                    <i class="fas fa-dollar-sign"></i>
                    ₱<span id="total-price">0.00</span> Total Price
                </span>
            </div>
        </div>
        
        <div class="lab-results-content">
            <!-- Lab results will be loaded here -->
            <div id="labResultsLoading" class="loading-spinner">
                <div class="spinner"></div>
                <p>Loading lab results...</p>
            </div>
            
            <div id="labResultsError" class="lab-results-error" style="display:none;">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Error loading lab results. Please try again.</p>
                <button class="btn retry-btn" onclick="retryLoadLabResults()">
                    <i class="fas fa-redo"></i> Retry
                </button>
            </div>
            
            <div id="labResultsEmpty" class="lab-results-empty" style="display:none;">
                <i class="fas fa-info-circle"></i>
                <h4>No Lab Results</h4>
                <p>No lab tests have been requested for this patient yet.</p>
            </div>
            
            <div id="labResultsList" class="lab-results-list" style="display:none;">
                <!-- Lab results will be loaded here -->
            </div>
        </div>
        
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeLabResultsModal()">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
    </div>
</div>

<style>
#labResultsModal {
    display: none;
}

#labResultsModal.show {
    display: flex;
}

.lab-results-modal-content {
    max-width: 900px;
    width: 90%;
    max-height: 85vh;
    display: flex;
    flex-direction: column;
}

.lab-results-content {
    flex: 1;
    overflow-y: auto;
    padding: 20px 0;
    min-height: 400px;
}

.lab-results-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.lab-result-card {
    background: #fff;
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: all 0.2s ease;
}

.lab-result-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    border-color: #c1c9d2;
}

.lab-result-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
    flex-wrap: wrap;
    gap: 8px;
}

.lab-result-title {
    font-size: 16px;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
    flex: 1;
    min-width: 200px;
}

.lab-result-status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    white-space: nowrap;
}

.lab-result-status-badge.completed {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.lab-result-status-badge.in-progress {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.lab-result-status-badge.pending {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.lab-result-status-badge.unknown {
    background: #e2e3e5;
    color: #383d41;
    border: 1px solid #d6d8db;
}

.lab-result-meta {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
    margin-bottom: 12px;
}

.lab-result-meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #6c757d;
}

.lab-result-meta-item i {
    color: #007bff;
    width: 16px;
    text-align: center;
}

.lab-result-meta-item strong {
    color: #495057;
}

.lab-result-priority {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.lab-result-priority.high {
    background: #f8d7da;
    color: #721c24;
}

.lab-result-priority.medium {
    background: #fff3cd;
    color: #856404;
}

.lab-result-priority.low {
    background: #d4edda;
    color: #155724;
}

.lab-result-details {
    background: #f8f9fa;
    border-radius: 6px;
    padding: 12px;
    margin-top: 12px;
}

.lab-result-details h5 {
    margin: 0 0 8px 0;
    font-size: 14px;
    color: #495057;
    font-weight: 600;
}

.lab-result-details p {
    margin: 0;
    font-size: 13px;
    color: #6c757d;
    line-height: 1.4;
}

.lab-result-actions {
    display: flex;
    gap: 8px;
    margin-top: 12px;
    justify-content: flex-end;
}

.lab-result-actions .btn {
    padding: 6px 12px;
    font-size: 12px;
    border-radius: 4px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.lab-result-actions .btn-pdf {
    background: #dc3545;
    color: white;
    border: none;
}

.lab-result-actions .btn-pdf:hover {
    background: #c82333;
}



.lab-results-error,
.lab-results-empty {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
}

.lab-results-error i,
.lab-results-empty i {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.5;
}

.lab-results-error h4,
.lab-results-empty h4 {
    margin: 0 0 8px 0;
    color: #495057;
}

.lab-results-error p,
.lab-results-empty p {
    margin: 0 0 16px 0;
}

.retry-btn {
    background: #007bff;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
}

.retry-btn:hover {
    background: #0056b3;
}

/* Loading spinner */
.loading-spinner {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px;
    color: #6c757d;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 16px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive design */
@media (max-width: 768px) {
    .lab-results-modal-content {
        width: 95%;
        max-height: 90vh;
    }
    
    .lab-result-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .lab-result-meta {
        grid-template-columns: 1fr;
    }
    
    .lab-result-actions {
        justify-content: flex-start;
    }
}
</style>

<script>
let currentLabResultsPatientId = null;
let currentLabResultsAdmissionId = null;

function openLabResultsModal(patientId, admissionId = null) {
    currentLabResultsPatientId = patientId;
    currentLabResultsAdmissionId = admissionId;
    
    // Show modal with proper class
    const modal = document.getElementById('labResultsModal');
    modal.classList.add('show');
    
    // Load lab results with admission filter
    loadLabResultsHistory(patientId, admissionId);
}

function closeLabResultsModal() {
    const modal = document.getElementById('labResultsModal');
    modal.classList.remove('show');
    currentLabResultsPatientId = null;
    currentLabResultsAdmissionId = null;
}

function loadLabResultsHistory(patientId, admissionId = null) {
    // Show loading state
    document.getElementById('labResultsLoading').style.display = 'block';
    document.getElementById('labResultsError').style.display = 'none';
    document.getElementById('labResultsEmpty').style.display = 'none';
    document.getElementById('labResultsList').style.display = 'none';
    
    // Build API URL with optional admission filter
    let apiUrl = `/doctor/api/patients/${patientId}/lab-results`;
    if (admissionId) {
        apiUrl += `?admission_id=${admissionId}`;
    }
    
    // Fetch lab results from API
    fetch(apiUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Lab results data:', data);
            
            // Hide loading
            document.getElementById('labResultsLoading').style.display = 'none';
            
            if (data.success && data.tests && data.tests.length > 0) {
                displayLabResultsHistory(data.tests, data.patient);
            } else {
                // Show empty state
                document.getElementById('labResultsEmpty').style.display = 'block';
                updateLabResultsStats(0, 0, 0.00);
            }
        })
        .catch(error => {
            console.error('Error loading lab results:', error);
            
            // Hide loading and show error
            document.getElementById('labResultsLoading').style.display = 'none';
            document.getElementById('labResultsError').style.display = 'block';
        });
}

function displayLabResultsHistory(tests, patient) {
    // Update patient info
    if (patient) {
        document.getElementById('lab-results-patient-name').textContent = 
            `${patient.first_name || ''} ${patient.last_name || ''}`.trim();
        document.getElementById('lab-results-patient-no').textContent = 
            `Patient No: ${patient.patient_no || 'N/A'}`;
    }
    
    // Calculate stats
    const totalTests = tests.length;
    const completedTests = tests.filter(test => test.status === 'completed').length;
    const totalPrice = tests.reduce((sum, test) => {
        // Get price from test object (assuming price is stored in test.price or test.procedure_price)
        const price = parseFloat(test.price || test.procedure_price || test.cost || 0);
        return sum + price;
    }, 0);
    
    updateLabResultsStats(totalTests, completedTests, totalPrice);
    
    // Generate HTML for lab results
    const labResultsHtml = tests.map(test => {
        const testName = test.test_requested || 'Unknown Test';
        const status = test.status || 'unknown';
        const priority = test.priority || 'medium';
        const requestedBy = test.requestedBy ? `${test.requestedBy.name}` : 'Unknown';
        const requestedAt = test.requested_at ? formatDateTime(test.requested_at) : 'Unknown date';
        const completedAt = test.completed_at ? formatDateTime(test.completed_at) : null;
        const labTech = test.labTech ? `${test.labTech.name}` : null;
        const results = test.results || '';
        const hasPdf = test.results_pdf_path ? true : false;
        
        const statusClass = status.toLowerCase().replace('_', '-');
        const priorityClass = priority.toLowerCase();
        
        let actionsHtml = '';
        if (hasPdf && status === 'completed') {
            actionsHtml = `
                <button type="button" class="btn btn-pdf" onclick="viewLabResultPdf(${test.id})">
                    <i class="fas fa-file-pdf"></i> View PDF
                </button>
            `;
        }
        
        return `
            <div class="lab-result-card">
                <div class="lab-result-header">
                    <h4 class="lab-result-title">${testName}</h4>
                    <span class="lab-result-status-badge ${statusClass}">${status.replace('_', ' ')}</span>
                </div>
                
                <div class="lab-result-meta">
                    <div class="lab-result-meta-item">
                        <i class="fas fa-user-md"></i>
                        <span><strong>Requested by:</strong> ${requestedBy}</span>
                    </div>
                    <div class="lab-result-meta-item">
                        <i class="fas fa-calendar"></i>
                        <span><strong>Requested:</strong> ${requestedAt}</span>
                    </div>
                    ${completedAt ? `
                        <div class="lab-result-meta-item">
                            <i class="fas fa-check"></i>
                            <span><strong>Completed:</strong> ${completedAt}</span>
                        </div>
                    ` : ''}
                    ${labTech ? `
                        <div class="lab-result-meta-item">
                            <i class="fas fa-user-cog"></i>
                            <span><strong>Lab Tech:</strong> ${labTech}</span>
                        </div>
                    ` : ''}
                    <div class="lab-result-meta-item">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><strong>Priority:</strong> <span class="lab-result-priority ${priorityClass}">${priority.toUpperCase()}</span></span>
                    </div>
                </div>
                
                ${results ? `
                    <div class="lab-result-details">
                        <h5><i class="fas fa-clipboard-list"></i> Results:</h5>
                        <p>${results}</p>
                    </div>
                ` : ''}
                
                ${actionsHtml ? `
                    <div class="lab-result-actions">
                        ${actionsHtml}
                    </div>
                ` : ''}
            </div>
        `;
    }).join('');
    
    // Display the results
    document.getElementById('labResultsList').innerHTML = labResultsHtml;
    document.getElementById('labResultsList').style.display = 'block';
}

function updateLabResultsStats(total, completed, totalPrice) {
    document.getElementById('total-lab-tests').textContent = total;
    document.getElementById('completed-tests').textContent = completed;
    document.getElementById('total-price').textContent = totalPrice.toFixed(2);
}

function retryLoadLabResults() {
    if (currentLabResultsPatientId) {
        loadLabResultsHistory(currentLabResultsPatientId, currentLabResultsAdmissionId);
    }
}

// Helper function to format datetime (reuse from main script)
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
        return dateStr.split('T')[0]; // fallback
    }
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('labResultsModal');
    if (event.target === modal) {
        closeLabResultsModal();
    }
});
</script><?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views/doctor/modals/lab_results_modal.blade.php ENDPATH**/ ?>