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
                <span class="stat pending-stat">
                    <i class="fas fa-clock"></i>
                    <span id="pending-tests">0</span> Pending
                </span>
                <span class="stat progress-stat">
                    <i class="fas fa-spinner"></i>
                    <span id="progress-tests">0</span> In Progress
                </span>
                <span class="stat completed-stat">
                    <i class="fas fa-check-circle"></i>
                    <span id="completed-tests">0</span> Completed
                </span>
                <span class="stat price-stat">
                    <i class="fas fa-dollar-sign"></i>
                    ₱<span id="total-price">0.00</span> Total
                </span>
            </div>
        </div>

        <!-- Status Filter Tabs -->
        <div class="lab-status-filters">
            <button class="status-filter-btn active" data-status="all">
                <i class="fas fa-list"></i> All Tests <span class="filter-count" id="all-count">0</span>
            </button>
            <button class="status-filter-btn" data-status="pending">
                <i class="fas fa-clock"></i> Pending <span class="filter-count" id="pending-count">0</span>
            </button>
            <button class="status-filter-btn" data-status="in_progress">
                <i class="fas fa-spinner"></i> In Progress <span class="filter-count" id="progress-count">0</span>
            </button>
            <button class="status-filter-btn" data-status="completed">
                <i class="fas fa-check-circle"></i> Completed <span class="filter-count" id="completed-count">0</span>
            </button>
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

/* Status-specific card styling */
.lab-result-card[data-status="pending"] {
    border-left-color: #f57c00;
    background: linear-gradient(135deg, #fff 0%, #fff9f0 100%);
}

.lab-result-card[data-status="in_progress"] {
    border-left-color: #1976d2;
    background: linear-gradient(135deg, #fff 0%, #f0f8ff 100%);
}

.lab-result-card[data-status="completed"] {
    border-left-color: #388e3c;
    background: linear-gradient(135deg, #fff 0%, #f0fff0 100%);
}

.lab-result-card[data-status="cancelled"] {
    border-left-color: #d32f2f;
    background: linear-gradient(135deg, #fff 0%, #fff0f0 100%);
}

/* Enhanced status badge with animations */
.lab-result-status-badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    animation: statusPulse 2s ease-in-out infinite;
}

@keyframes statusPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.02); }
}

.lab-result-status-badge.completed {
    background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
    color: #2e7d32;
    border: 1px solid #a5d6a7;
}
.lab-result-status-badge.completed::before {
    content: "✅";
    font-size: 10px;
}

.lab-result-status-badge.in-progress {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    color: #1565c0;
    border: 1px solid #90caf9;
    animation: progressSpin 1.5s linear infinite;
}
.lab-result-status-badge.in-progress::before {
    content: "🔄";
    font-size: 10px;
    animation: spin 1s linear infinite;
}

.lab-result-status-badge.pending {
    background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
    color: #ef6c00;
    border: 1px solid #ffcc02;
}
.lab-result-status-badge.pending::before {
    content: "⏳";
    font-size: 10px;
}

.lab-result-status-badge.cancelled {
    background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
    color: #c62828;
    border: 1px solid #ef9a9a;
}
.lab-result-status-badge.cancelled::before {
    content: "❌";
    font-size: 10px;
}

.lab-result-status-badge.unknown {
    background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);
    color: #616161;
    border: 1px solid #bdbdbd;
}

/* Enhanced patient info and stats */
.patient-info-summary {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
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

.stat.pending-stat {
    background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
    color: #ef6c00;
    border: 1px solid #ffb74d;
}

.stat.progress-stat {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    color: #1565c0;
    border: 1px solid #42a5f5;
}

.stat.completed-stat {
    background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
    color: #2e7d32;
    border: 1px solid #66bb6a;
}

.stat.price-stat {
    background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%);
    color: #7b1fa2;
    border: 1px solid #ba68c8;
}

/* Status Filter Tabs */
.lab-status-filters {
    display: flex;
    gap: 8px;
    margin-bottom: 20px;
    padding: 8px;
    background: #f8f9fa;
    border-radius: 12px;
    border: 1px solid #dee2e6;
    flex-wrap: wrap;
}

.status-filter-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 10px 16px;
    border: 2px solid transparent;
    background: white;
    color: #6c757d;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.3s ease;
    flex: 1;
    justify-content: center;
    min-width: 110px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.status-filter-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.status-filter-btn.active {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.status-filter-btn[data-status="all"].active {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    color: white;
    border-color: #495057;
}

.status-filter-btn[data-status="pending"] {
    border-color: #f57c00;
    color: #f57c00;
}
.status-filter-btn[data-status="pending"].active {
    background: linear-gradient(135deg, #f57c00 0%, #ff9800 100%);
    color: white;
}

.status-filter-btn[data-status="in_progress"] {
    border-color: #1976d2;
    color: #1976d2;
}
.status-filter-btn[data-status="in_progress"].active {
    background: linear-gradient(135deg, #1976d2 0%, #2196f3 100%);
    color: white;
}

.status-filter-btn[data-status="completed"] {
    border-color: #388e3c;
    color: #388e3c;
}
.status-filter-btn[data-status="completed"].active {
    background: linear-gradient(135deg, #388e3c 0%, #4caf50 100%);
    color: white;
}

.filter-count {
    background: rgba(255,255,255,0.9);
    color: inherit;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
    margin-left: 4px;
    border: 1px solid currentColor;
}

.status-filter-btn.active .filter-count {
    background: rgba(255,255,255,0.2);
    color: white;
    border-color: rgba(255,255,255,0.5);
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
    
    // Fetch lab results from API
    fetch(`/api/patients/${patientId}/lab-results`)
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
    
    updateLabResultsStats(totalTests, completedTests, totalPrice, pendingTests, inProgressTests);
    
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
        
        return `
            <div class="lab-order-card" data-status="${status}">
                <div class="lab-result-header">
                    <h4 class="lab-result-title">${testName}</h4>
                    <span class="lab-result-status-badge ${statusClass}">
                        ${statusIcon} ${statusText}
                    </span>
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
    
    // Initialize filter functionality after content loads
    initializeLabResultsFilter();
}

function updateLabResultsStats(total, completed, totalPrice, pending = 0, inProgress = 0) {
    // Update enhanced stats with proper element checking
    const totalElement = document.getElementById('total-lab-tests');
    const pendingElement = document.getElementById('pending-tests');
    const progressElement = document.getElementById('in-progress-tests');
    const completedElement = document.getElementById('completed-tests');
    const priceElement = document.getElementById('total-price');
    
    if (totalElement) totalElement.textContent = total;
    if (pendingElement) pendingElement.textContent = pending;
    if (progressElement) progressElement.textContent = inProgress;
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

// Enhanced filter functionality for lab results
function initializeLabResultsFilter() {
    const filterButtons = document.querySelectorAll('.status-filter-btn');
    const labOrderCards = document.querySelectorAll('.lab-order-card');
    
    // Update filter counts
    updateFilterCounts();
    
    // Set up filter button click handlers
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetStatus = this.getAttribute('data-status');
            
            // Update active state
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Filter cards
            filterLabResults(targetStatus);
        });
    });
    
    // Initialize with "all" filter active
    const allButton = document.querySelector('.status-filter-btn[data-status="all"]');
    if (allButton) {
        allButton.classList.add('active');
    }
}

function updateFilterCounts() {
    const labOrderCards = document.querySelectorAll('.lab-order-card');
    const filterButtons = document.querySelectorAll('.status-filter-btn');
    
    const counts = {
        all: labOrderCards.length,
        pending: 0,
        in_progress: 0,
        completed: 0
    };
    
    labOrderCards.forEach(card => {
        const status = card.getAttribute('data-status');
        if (counts.hasOwnProperty(status)) {
            counts[status]++;
        }
    });
    
    filterButtons.forEach(button => {
        const status = button.getAttribute('data-status');
        const countElement = button.querySelector('.filter-count');
        if (countElement && counts.hasOwnProperty(status)) {
            countElement.textContent = counts[status];
        }
    });
}

function filterLabResults(status) {
    const labOrderCards = document.querySelectorAll('.lab-order-card');
    
    labOrderCards.forEach(card => {
        const cardStatus = card.getAttribute('data-status');
        
        if (status === 'all' || cardStatus === status) {
            card.style.display = 'block';
            // Add smooth fade in
            card.style.opacity = '0';
            card.style.transition = 'opacity 0.3s ease';
            setTimeout(() => {
                card.style.opacity = '1';
            }, 50);
        } else {
            card.style.display = 'none';
        }
    });
    
    // Show "no results" message if needed
    const visibleCards = Array.from(labOrderCards).filter(card => 
        card.style.display !== 'none'
    );
    
    let noResultsMsg = document.querySelector('.no-results-message');
    if (visibleCards.length === 0) {
        if (!noResultsMsg) {
            noResultsMsg = document.createElement('div');
            noResultsMsg.className = 'no-results-message';
            noResultsMsg.innerHTML = `
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <i class="fas fa-search" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                    <h5>No lab results found</h5>
                    <p>No lab results match the current filter.</p>
                </div>
            `;
            document.getElementById('labResultsList').appendChild(noResultsMsg);
        }
        noResultsMsg.style.display = 'block';
    } else if (noResultsMsg) {
        noResultsMsg.style.display = 'none';
    }
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('labResultsModal');
    if (event.target === modal) {
        closeLabResultsModal();
    }
});
</script>