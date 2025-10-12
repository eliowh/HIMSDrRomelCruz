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
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    position: relative;
    border-left: 4px solid transparent;
}

.lab-result-card:hover {
    box-shadow: 0 6px 16px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

/* Simplified card styling */
.lab-result-card[data-status="pending"] {
    border-left-color: #f57c00;
}

.lab-result-card[data-status="in_progress"] {
    border-left-color: #1976d2;
}

.lab-result-card[data-status="completed"] {
    border-left-color: #388e3c;
}

.lab-result-card[data-status="cancelled"] {
    border-left-color: #d32f2f;
}

/* Simple status badge */
.lab-result-status-badge {
    padding: 4px 8px;
    border-radius: 12px;
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
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

.lab-result-status-badge.pending {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.lab-result-status-badge.cancelled {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.lab-result-status-badge.unknown {
    background: #e2e3e5;
    color: #383d41;
    border: 1px solid #d6d8db;
}

/* Simple patient info styling */
.patient-info-summary {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #dee2e6;
}

.history-stats {
    display: flex;
    gap: 16px;
    margin-top: 12px;
    flex-wrap: wrap;
}

.history-stats .stat {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    flex: 1;
    min-width: 120px;
    justify-content: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Simple stat styling */
.history-stats .stat {
    color: #666;
    font-size: 0.9em;
}

/* Simplified styling */

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
    display: flex;
    align-items: center;
    gap: 8px;
}

.lab-result-title::before {
    content: "🧪";
    font-size: 14px;
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

function openLabResultsModal(patientId) {
    currentLabResultsPatientId = patientId;
    
    // Show modal with proper class
    const modal = document.getElementById('labResultsModal');
    modal.classList.add('show');
    
    // Load lab results
    loadLabResultsHistory(patientId);
}

function closeLabResultsModal() {
    const modal = document.getElementById('labResultsModal');
    modal.classList.remove('show');
    currentLabResultsPatientId = null;
}

function loadLabResultsHistory(patientId) {
    // Show loading state
    document.getElementById('labResultsLoading').style.display = 'block';
    document.getElementById('labResultsError').style.display = 'none';
    document.getElementById('labResultsEmpty').style.display = 'none';
    document.getElementById('labResultsList').style.display = 'none';
    
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
        `/api/patients/${patientId}/lab-results?admission_id=${admissionId}` : 
        `/api/patients/${patientId}/lab-results`;
    
    console.log('Loading lab results with URL:', apiUrl, 'Admission ID:', admissionId);
    
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
    
    // Calculate stats with enhanced breakdown
    const totalTests = tests.length;
    const pendingTests = tests.filter(test => test.status === 'pending').length;
    const inProgressTests = tests.filter(test => test.status === 'in_progress').length;
    const completedTests = tests.filter(test => test.status === 'completed').length;
    const totalPrice = tests.reduce((sum, test) => {
        const price = parseFloat(test.price || test.procedure_price || test.cost || 0);
        return sum + price;
    }, 0);
    
    updateLabResultsStats(totalTests, completedTests, totalPrice);
    
    // Generate HTML for lab results with enhanced status attributes
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
        
        // Doctor's analysis data
        const hasAnalysis = test.has_analysis || false;
        const analysis = test.latest_analysis || null;
        const hasAnalysisPdf = hasAnalysis && analysis && analysis.has_analysis_pdf;
        
        const statusClass = status.toLowerCase().replace('_', '-');
        const priorityClass = priority.toLowerCase();
        
        // Enhanced status badge with icons
        let statusIcon = '';
        let statusText = status.replace('_', ' ');
        switch(status) {
            case 'pending':
                statusIcon = '<i class="fas fa-clock"></i>';
                break;
            case 'in_progress':
                statusIcon = '<i class="fas fa-spinner fa-spin"></i>';
                statusText = 'In Progress';
                break;
            case 'completed':
                statusIcon = '<i class="fas fa-check-circle"></i>';
                break;
            default:
                statusIcon = '<i class="fas fa-question-circle"></i>';
        }
        
        let actionsHtml = '';
        if (hasPdf && status === 'completed') {
            actionsHtml = `
                <button type="button" class="btn btn-pdf" onclick="viewLabResultPdf(${test.id})">
                    <i class="fas fa-file-pdf"></i> View PDF
                </button>
            `;
        }
        
        // Add analysis PDF button if available
        if (hasAnalysisPdf && status === 'completed') {
            actionsHtml += `
                <button type="button" class="btn btn-analysis-pdf" onclick="viewAnalysisPdf(${test.id})" style="background: #28a745; color: white; margin-left: 8px;">
                    <i class="fas fa-stethoscope"></i> View Analysis PDF
                </button>
            `;
        }
        
        return `
            <div class="lab-result-item status-${statusClass} priority-${priorityClass}">
                <div class="lab-result-header">
                    <div class="lab-info">
                        <h4 class="lab-test-name">LABORATORY: ${testName}</h4>
                        <div class="lab-meta">
                            <span class="status-badge status-${statusClass}">
                                ${statusIcon}
                                <span class="status-text">${statusText.toUpperCase()}</span>
                            </span>
                            <span class="priority-badge priority-${priorityClass}">
                                <i class="fas fa-flag"></i>
                                <span class="priority-text">Priority: ${priority.toUpperCase()}</span>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="lab-result-details">
                    <div class="detail-row">
                        <span class="detail-label"><i class="fas fa-user-md"></i> Requested by:</span>
                        <span class="detail-value">${requestedBy}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label"><i class="fas fa-calendar-plus"></i> Requested:</span>
                        <span class="detail-value">${requestedAt}</span>
                    </div>
                    ${completedAt ? `
                        <div class="detail-row">
                            <span class="detail-label"><i class="fas fa-calendar-check"></i> Completed:</span>
                            <span class="detail-value">${completedAt}</span>
                        </div>
                    ` : ''}
                    ${labTech ? `
                        <div class="detail-row">
                            <span class="detail-label"><i class="fas fa-user-nurse"></i> Analyzed by:</span>
                            <span class="detail-value">${labTech}</span>
                        </div>
                    ` : ''}
                </div>
                
                ${results && status === 'completed' ? `
                    <div class="lab-result-details">
                        <h5><i class="fas fa-clipboard-list"></i> Lab Results:</h5>
                        <p>${results}</p>
                    </div>
                ` : ''}
                
                ${hasAnalysis && analysis ? `
                    <div class="lab-result-details" style="border-left: 4px solid #28a745;">
                        <h5><i class="fas fa-stethoscope"></i> Doctor's Analysis:</h5>
                        ${analysis.clinical_notes ? `
                            <div style="margin-bottom: 10px;">
                                <strong>Clinical Findings:</strong><br>
                                <p style="margin: 4px 0;">${analysis.clinical_notes}</p>
                            </div>
                        ` : ''}
                        ${analysis.recommendations ? `
                            <div>
                                <strong>Recommendations:</strong><br>
                                <p style="margin: 4px 0;">${analysis.recommendations}</p>
                            </div>
                        ` : ''}
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
    // Update simple stats to match doctor's modal
    const totalElement = document.getElementById('total-lab-tests');
    const completedElement = document.getElementById('completed-tests');
    const priceElement = document.getElementById('total-price');
    
    if (totalElement) totalElement.textContent = total;
    if (completedElement) completedElement.textContent = completed;
    if (priceElement) priceElement.textContent = totalPrice.toFixed(2);
}

function retryLoadLabResults() {
    if (currentLabResultsPatientId) {
        loadLabResultsHistory(currentLabResultsPatientId);
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

// Simplified modal functions

function viewLabResultPdf(labOrderId) {
    // Open the lab result PDF in a new window/tab
    window.open(`/nurse/results/${labOrderId}/pdf`, '_blank');
}

// Function to view analysis PDF (nurse route)
function viewAnalysisPdf(labOrderId) {
    // Open the analysis PDF in a new window/tab using nurse route
    window.open(`/nurse/results/${labOrderId}/analysis-pdf`, '_blank');
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('labResultsModal');
    if (event.target === modal) {
        closeLabResultsModal();
    }
});
</script>