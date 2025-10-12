@extends('layouts.doctor')

@section('title', 'Lab Results')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{ asset('css/doctorcss/doctor_patients.css') }}">
<link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
.btn-group {
    display: flex;
    gap: 8px;
}

.btn-group .btn {
    flex: 1;
    min-width: 110px;
}

.btn.primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
}

.btn.primary:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: translateY(-1px);
}

.btn.secondary {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    color: #495057;
}

.btn.secondary:hover {
    background: #e9ecef;
    border-color: #adb5bd;
}

.btn.analysis {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
    color: white;
    font-weight: 600;
}

.btn.analysis:hover {
    background: linear-gradient(135deg, #218838 0%, #17a085 100%);
    transform: translateY(-1px);
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending-analysis {
    background: #ffeaa7;
    color: #d63031;
    border: 1px solid #fdcb6e;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.status-in-progress {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

.lab-results-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 16px;
    padding: 16px;
    border: 1px solid #e9ecef;
}

.result-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e9ecef;
}

.result-patient-info {
    flex: 1;
}

.result-patient-name {
    font-size: 16px;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 4px;
}

.result-patient-details {
    font-size: 14px;
    color: #6c757d;
}

.result-actions {
    display: flex;
    gap: 8px;
}

.result-body {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
}

.result-detail {
    display: flex;
    flex-direction: column;
}

.result-label {
    font-size: 12px;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    margin-bottom: 4px;
}

.result-value {
    font-size: 14px;
    color: #2c3e50;
    font-weight: 500;
}

.search-container {
    margin-bottom: 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.page-title {
    font-size: 24px;
    font-weight: 600;
    color: #2c3e50;
}

.results-summary {
    color: #6c757d;
    font-size: 14px;
}

.status-completed {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.status-tabs {
    display: flex;
    margin-bottom: 20px;
    border-bottom: 2px solid #e9ecef;
}

.status-tab {
    padding: 12px 24px;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-bottom: none;
    cursor: pointer;
    font-weight: 600;
    text-decoration: none;
    color: #6c757d;
    margin-right: 4px;
    border-radius: 8px 8px 0 0;
    transition: all 0.3s ease;
}

.status-tab:hover {
    background: #e9ecef;
    color: #495057;
    text-decoration: none;
}

.status-tab.active {
    background: white;
    color: #667eea;
    border-color: #667eea;
    border-bottom: 2px solid white;
    margin-bottom: -2px;
}

.tab-count {
    background: #6c757d;
    color: white;
    border-radius: 12px;
    padding: 2px 8px;
    font-size: 11px;
    margin-left: 6px;
}

.status-tab.active .tab-count {
    background: #667eea;
}

.no-results {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
}

.no-results i {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.5;
}

/* Modal Styling */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    backdrop-filter: blur(2px);
}

.modal.show {
    display: flex;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.3s ease;
}

.modal-content {
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    animation: slideIn 0.3s ease;
    position: relative;
    margin: 20px;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideIn {
    from { 
        transform: translateY(-30px) scale(0.95);
        opacity: 0;
    }
    to { 
        transform: translateY(0) scale(1);
        opacity: 1;
    }
}

.form-control:focus {
    border-color: #667eea;
    outline: none;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}
</style>

<div class="page-header">
    <div>
        <h1 class="page-title">Lab Results</h1>
        @if($results->total() > 0)
            <p class="results-summary">
                Showing {{ $results->firstItem() }}-{{ $results->lastItem() }} of {{ $results->total() }} 
                @if($status === 'pending')
                    pending lab requests
                @elseif($status === 'pending_analysis')
                    lab results pending analysis
                @else
                    completed lab results
                @endif
            </p>
        @endif
    </div>
</div>

<!-- Status Tabs -->
<div class="status-tabs">
    <a href="{{ route('doctor.results', ['status' => 'pending', 'search' => request('search')]) }}" 
       class="status-tab {{ $status === 'pending' ? 'active' : '' }}">
        <i class="fas fa-clock"></i> Pending Requests
        <span class="tab-count">{{ $statusCounts['pending'] }}</span>
    </a>
    <a href="{{ route('doctor.results', ['status' => 'pending_analysis', 'search' => request('search')]) }}" 
       class="status-tab {{ $status === 'pending_analysis' ? 'active' : '' }}">
        <i class="fas fa-microscope"></i> Pending Analysis
        <span class="tab-count">{{ $statusCounts['pending_analysis'] }}</span>
    </a>
    <a href="{{ route('doctor.results', ['status' => 'completed', 'search' => request('search')]) }}" 
       class="status-tab {{ $status === 'completed' ? 'active' : '' }}">
        <i class="fas fa-check-circle"></i> Completed
        <span class="tab-count">{{ $statusCounts['completed'] }}</span>
    </a>
</div>

<div class="search-container">
    <form method="GET" action="{{ route('doctor.results') }}" class="search-form">
        <div class="search-input-container">
            <input type="text" 
                   name="search" 
                   placeholder="Search by patient name, patient ID, or test..." 
                   value="{{ $search }}"
                   class="search-input">
            <button type="submit" class="search-btn">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </form>
</div>

@if($results->count() > 0)
    @foreach($results as $result)
        <div class="lab-results-card">
            <div class="result-header">
                <div class="result-patient-info">
                    <div class="result-patient-name">
                        {{ $result->patient->first_name }} {{ $result->patient->last_name }}
                    </div>
                    <div class="result-patient-details">
                        Patient ID: {{ $result->patient->patient_no }} • 
                        Lab Order #{{ $result->id }}
                    </div>
                </div>
                <div class="result-actions">
                    @if($status === 'pending')
                        @if($result->status === 'pending')
                            <span class="status-badge status-pending">Pending</span>
                        @elseif($result->status === 'in_progress')
                            <span class="status-badge status-in-progress">In Progress</span>
                        @endif
                    @elseif($status === 'pending_analysis')
                        <span class="status-badge status-pending-analysis">Pending Analysis</span>
                    @else
                        <span class="status-badge status-completed">Completed</span>
                    @endif
                </div>
            </div>

            <div class="result-body">
                <div class="result-detail">
                    <span class="result-label">Test Requested</span>
                    <span class="result-value">{{ $result->test_requested }}</span>
                </div>
                <div class="result-detail">
                    <span class="result-label">
                        @if($status === 'completed')
                            Completed Date
                        @elseif($status === 'pending_analysis')
                            Lab Completed Date
                        @else
                            Requested Date
                        @endif
                    </span>
                    <span class="result-value">
                        @if($status === 'completed' || $status === 'pending_analysis')
                            {{ $result->completed_at ? $result->completed_at->format('M d, Y g:i A') : 'N/A' }}
                        @else
                            {{ $result->requested_at ? $result->requested_at->format('M d, Y g:i A') : 'N/A' }}
                        @endif
                    </span>
                </div>
                <div class="result-detail">
                    <span class="result-label">
                        @if($status === 'completed' || $status === 'pending_analysis')
                            Lab Technician
                        @else
                            Requested By
                        @endif
                    </span>
                    <span class="result-value">
                        @if($status === 'completed' || $status === 'pending_analysis')
                            {{ $result->labTech ? $result->labTech->name : 'N/A' }}
                        @else
                            {{ $result->requestedBy ? $result->requestedBy->name : 'N/A' }}
                        @endif
                    </span>
                </div>
            </div>

            <div class="btn-group">
                @if($status === 'completed')
                    @if($result->results_pdf_path)
                        <a href="{{ route('doctor.lab.viewPdf', $result->id) }}" 
                           target="_blank" 
                           class="btn primary">
                            <i class="fas fa-file-pdf"></i> View Lab Report
                        </a>
                    @endif
                    
                    <a href="{{ route('doctor.results.analysis-pdf', $result->id) }}" 
                       target="_blank" 
                       class="btn analysis">
                        <i class="fas fa-file-medical"></i> View Analysis Report
                    </a>
                @elseif($status === 'pending_analysis')
                    @if($result->results_pdf_path)
                        <a href="{{ route('doctor.lab.viewPdf', $result->id) }}" 
                           target="_blank" 
                           class="btn primary">
                            <i class="fas fa-file-pdf"></i> View Lab Report
                        </a>
                    @endif
                    
                    <button class="btn analysis" onclick="openAnalysisModal({{ $result->id }}, '{{ $result->patient->first_name }} {{ $result->patient->last_name }}', '{{ $result->test_requested }}')">
                        <i class="fas fa-chart-line"></i> Complete Analysis
                    </button>
                @else
                    <button class="btn secondary" disabled>
                        <i class="fas fa-file-pdf"></i> Report Pending
                    </button>
                    
                    <button class="btn secondary" disabled>
                        <i class="fas fa-chart-line"></i> Analysis (Pending Lab)
                    </button>
                @endif
                
                <button class="btn secondary" onclick="viewPatientHistory({{ $result->patient->id }})">
                    <i class="fas fa-history"></i> Patient History
                </button>
            </div>
        </div>
    @endforeach

    <!-- Pagination -->
    <div class="pagination-container">
        {{ $results->appends(request()->query())->links() }}
    </div>
@else
    <div class="no-results">
        <i class="fas fa-flask"></i>
        <h3>No {{ $status === 'pending' ? 'Pending' : ($status === 'pending_analysis' ? 'Pending Analysis' : 'Completed') }} Lab Results Found</h3>
        @if($search)
            <p>No results match your search for "{{ $search }}"</p>
            <a href="{{ route('doctor.results', ['status' => $status]) }}" class="btn primary">Show All {{ $status === 'pending' ? 'Pending' : ($status === 'pending_analysis' ? 'Pending Analysis' : 'Completed') }} Results</a>
        @else
            @if($status === 'pending')
                <p>No pending lab requests are available at this time.</p>
                <p>Lab requests will appear here when nurses submit them for patients.</p>
            @elseif($status === 'pending_analysis')
                <p>No lab results are waiting for your analysis.</p>
                <p>Lab results will appear here when lab technicians complete tests and they need your clinical analysis.</p>
            @else
                <p>No completed lab results with analysis are available.</p>
                <p>Results will appear here after you complete clinical analysis.</p>
            @endif
        @endif
    </div>
@endif

<!-- Analysis Modal -->
<div id="analysisModal" class="modal">
    <div class="modal-content" style="max-width: 700px; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px 8px 0 0;">
            <h3 id="analysisModalTitle" style="margin: 0; font-size: 18px; font-weight: 600;">
                <i class="fas fa-file-medical"></i> Clinical Analysis Report
            </h3>
            <span class="close" onclick="closeAnalysisModal()" style="position: absolute; top: 15px; right: 20px; font-size: 24px; cursor: pointer; color: white;">&times;</span>
        </div>
        <div id="analysisModalBody" style="padding: 25px;">
            <!-- Patient Information Section -->
            <div class="analysis-section" style="margin-bottom: 25px; border: 1px solid #e9ecef; border-radius: 6px; padding: 15px; background: #f8f9fa;">
                <h4 style="margin: 0 0 10px 0; color: #2c3e50; font-size: 14px; font-weight: 600; text-transform: uppercase; border-bottom: 2px solid #667eea; padding-bottom: 5px;">
                    <i class="fas fa-user"></i> Patient Information
                </h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <strong>Name:</strong> <span id="analysisPatientInfo" style="color: #2c3e50;"></span>
                    </div>
                    <div>
                        <strong>Test Type:</strong> <span id="analysisTestInfo" style="color: #2c3e50;"></span>
                    </div>
                </div>
            </div>

            <!-- Clinical Findings Section -->
            <div class="analysis-section" style="margin-bottom: 25px;">
                <h4 style="margin: 0 0 10px 0; color: #2c3e50; font-size: 14px; font-weight: 600; text-transform: uppercase; border-bottom: 2px solid #28a745; padding-bottom: 5px;">
                    <i class="fas fa-stethoscope"></i> Clinical Findings & Interpretation
                </h4>
                <p style="font-size: 12px; color: #6c757d; margin-bottom: 10px; font-style: italic;">
                    Provide your clinical analysis and interpretation of the laboratory results
                </p>
                <textarea id="analysisNotes" 
                          placeholder="Enter your detailed clinical findings and interpretation of the laboratory results..." 
                          rows="8" 
                          class="form-control"
                          style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 6px; font-family: 'Arial', sans-serif; font-size: 13px; line-height: 1.5; resize: vertical; min-height: 120px;"></textarea>
            </div>

            <!-- Recommendations Section -->
            <div class="analysis-section" style="margin-bottom: 20px;">
                <h4 style="margin: 0 0 10px 0; color: #2c3e50; font-size: 14px; font-weight: 600; text-transform: uppercase; border-bottom: 2px solid #fd7e14; padding-bottom: 5px;">
                    <i class="fas fa-clipboard-list"></i> Clinical Recommendations
                </h4>
                <p style="font-size: 12px; color: #6c757d; margin-bottom: 10px; font-style: italic;">
                    Provide follow-up recommendations, treatment suggestions, or further diagnostic needs
                </p>
                <textarea id="analysisRecommendations" 
                          placeholder="Enter follow-up recommendations, treatment suggestions, or additional diagnostic requirements..." 
                          rows="5" 
                          class="form-control"
                          style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 6px; font-family: 'Arial', sans-serif; font-size: 13px; line-height: 1.5; resize: vertical; min-height: 80px;"></textarea>
            </div>

            <!-- Important Notice -->
            <div style="background: #e8f4fd; border: 1px solid #bee5eb; border-radius: 6px; padding: 12px; margin-bottom: 20px;">
                <p style="margin: 0; font-size: 11px; color: #0c5460;">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Note:</strong> This analysis will be permanently saved to the patient's medical record and will be included in the generated analysis report PDF.
                </p>
            </div>
        </div>
        <div class="modal-footer" style="padding: 20px 25px; background: #f8f9fa; border-top: 1px solid #e9ecef; display: flex; justify-content: flex-end; gap: 10px; border-radius: 0 0 8px 8px;">
            <button type="button" class="btn secondary" onclick="closeAnalysisModal()" style="padding: 10px 20px; border-radius: 6px;">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button type="button" class="btn primary" onclick="saveAnalysis()" style="padding: 10px 20px; border-radius: 6px; font-weight: 600;">
                <i class="fas fa-save"></i> Save Analysis & Generate Report
            </button>
        </div>
    </div>
</div>

<script>
function openAnalysisModal(resultId, patientName, testRequested) {
    document.getElementById('analysisPatientInfo').textContent = patientName;
    document.getElementById('analysisTestInfo').textContent = testRequested;
    document.getElementById('analysisModal').classList.add('show');
    
    // Store result ID for saving
    document.getElementById('analysisModal').dataset.resultId = resultId;
}

function closeAnalysisModal() {
    document.getElementById('analysisModal').classList.remove('show');
    // Clear form
    document.getElementById('analysisNotes').value = '';
    document.getElementById('analysisRecommendations').value = '';
}

function saveAnalysis() {
    const resultId = document.getElementById('analysisModal').dataset.resultId;
    const notes = document.getElementById('analysisNotes').value;
    const recommendations = document.getElementById('analysisRecommendations').value;
    
    if (!notes.trim() && !recommendations.trim()) {
        alert('Please enter at least clinical notes or recommendations.');
        return;
    }
    
    // Show loading state
    const saveBtn = document.querySelector('#analysisModal .btn.primary');
    const originalText = saveBtn.textContent;
    saveBtn.textContent = 'Saving...';
    saveBtn.disabled = true;
    
    // Send AJAX request to save analysis
    fetch('/doctor/results/save-analysis', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            lab_order_id: resultId,
            clinical_notes: notes,
            recommendations: recommendations
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Analysis saved successfully!');
            closeAnalysisModal();
            // Redirect to completed tab to show the result moved there
            window.location.href = '{{ route("doctor.results", ["status" => "completed"]) }}';
        } else {
            alert('Error saving analysis: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Network error occurred while saving analysis.');
    })
    .finally(() => {
        // Restore button state
        saveBtn.textContent = originalText;
        saveBtn.disabled = false;
    });
}

function viewPatientHistory(patientId) {
    window.location.href = `/doctor/patients/${patientId}`;
}

// Modal functionality
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('analysisModal');
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target === modal) {
            closeAnalysisModal();
        }
    }
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('show')) {
            closeAnalysisModal();
        }
    });
});
</script>

@endsection