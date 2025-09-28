<!-- Test History Modal -->
<div id="testHistoryModal" class="modal">
    <div class="modal-content test-history-modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-history"></i> Lab Test History</h3>
            <span class="close" onclick="closeTestHistoryModal()">&times;</span>
        </div>
        
        <div class="patient-info-summary">
            <div class="patient-details">
                <strong id="history-patient-name">Patient Name</strong>
                <span id="history-patient-no">Patient No</span>
            </div>
            <div class="history-stats">
                <span class="stat">
                    <i class="fas fa-flask"></i>
                    <span id="total-tests">0</span> Total Tests
                </span>
                <span class="stat">
                    <i class="fas fa-check-circle"></i>
                    <span id="completed-tests">0</span> Completed
                </span>
            </div>
        </div>
        
        <div class="test-history-content">
            <!-- Test history will be loaded here -->
            <div id="testHistoryLoading" class="loading-spinner">
                <div class="spinner"></div>
                <p>Loading test history...</p>
            </div>
            
            <div id="testHistoryError" class="test-history-error" style="display:none;">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Error loading test history. Please try again.</p>
                <button class="btn retry-btn" onclick="retryLoadHistory()">
                    <i class="fas fa-retry"></i> Retry
                </button>
            </div>
            
            <div id="testHistoryEmpty" class="test-history-empty" style="display:none;">
                <i class="fas fa-info-circle"></i>
                <h4>No Test History</h4>
                <p>No previous lab tests found for this patient.</p>
            </div>
            
            <div id="testHistoryList" class="test-history-list" style="display:none;">
                <!-- Lab test records will be loaded here -->
            </div>
        </div>
        
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeTestHistoryModal()">
                <i class="fas fa-times"></i> Close
            </button>
            <button class="btn btn-primary" onclick="printTestHistory()" id="printHistoryBtn" style="display:none;">
                <i class="fas fa-print"></i> Print History
            </button>
        </div>
    </div>
</div>

<style>
.test-history-modal-content {
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
    gap: 20px;
}

.history-stats .stat {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #7f8c8d;
    font-size: 14px;
}

.history-stats .stat i {
    color: #3498db;
}

.test-history-content {
    max-height: 500px;
    overflow-y: auto;
    padding: 20px 25px;
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

.test-history-error, .test-history-empty {
    text-align: center;
    padding: 40px;
    color: #7f8c8d;
}

.test-history-error i, .test-history-empty i {
    font-size: 48px;
    margin-bottom: 15px;
    display: block;
}

.test-history-error i {
    color: #e74c3c;
}

.test-history-empty i {
    color: #95a5a6;
}

.test-history-error h4, .test-history-empty h4 {
    margin: 0 0 10px 0;
    color: #2c3e50;
}

.test-history-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.test-record {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    background: #fafafa;
    transition: all 0.2s;
}

.test-record:hover {
    border-color: #3498db;
    box-shadow: 0 2px 8px rgba(52, 152, 219, 0.1);
}

.test-record-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.test-record-info h4 {
    margin: 0 0 5px 0;
    color: #2c3e50;
    font-size: 16px;
}

.test-record-meta {
    font-size: 13px;
    color: #7f8c8d;
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.test-record-status {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 8px;
}

.test-record-actions {
    display: flex;
    gap: 8px;
    margin-top: 15px;
}

.test-record-actions .btn {
    padding: 6px 12px;
    font-size: 12px;
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
    background: #3498db;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
}

.retry-btn:hover {
    background: #2980b9;
}
</style>