<!-- Test History Modal -->
<div id="testHistoryModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Lab Test History</h3>
        <div class="patient-info-summary">
            <strong id="history-patient-name">Patient Name</strong>
            <span id="history-patient-no">Patient No</span>
        </div>
        
        <div class="test-history-content">
            <!-- Test history will be loaded here -->
            <div id="testHistoryLoading" class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i> Loading test history...
            </div>
            
            <div id="testHistoryEmpty" class="test-history-empty" style="display:none;">
                <i class="fas fa-info-circle"></i>
                <p>No lab test history found for this patient.</p>
            </div>
            
            <div id="testHistoryList" class="test-history-list" style="display:none;">
                <!-- Lab test records will be loaded here -->
            </div>
        </div>
    </div>
</div>