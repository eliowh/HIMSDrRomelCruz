<!-- Health History Modal -->
<div id="healthHistoryModal" class="modal">
    <div class="modal-content health-history-modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-notes-medical"></i> General Health History</h3>
            <span class="close" onclick="closeHealthHistoryModal()">&times;</span>
        </div>
        
        <div class="patient-info-summary">
            <div class="patient-details">
                <strong id="health-history-patient-name">Patient Name</strong>
                <span id="health-history-patient-no">Patient No</span>
            </div>
        </div>
        
        <div class="health-history-content">
            <!-- Health history will be loaded here -->
            <div id="healthHistoryLoading" class="loading-spinner">
                <div class="spinner"></div>
                <p>Loading health history...</p>
            </div>
            
            <div id="healthHistoryDetails" style="display: none;">
                <!-- Medical Conditions -->
                <div class="health-category">
                    <h4><i class="fas fa-heartbeat"></i> Medical Conditions</h4>
                    <div class="health-items">
                        <div class="health-item">
                            <strong>Chronic Illnesses:</strong>
                            <span id="chronic-illnesses">No chronic illnesses reported</span>
                        </div>
                        <div class="health-item">
                            <strong>Hospitalization History:</strong>
                            <span id="hospitalization-history">No hospitalization history reported</span>
                        </div>
                        <div class="health-item">
                            <strong>Surgery History:</strong>
                            <span id="surgery-history">No surgery history reported</span>
                        </div>
                        <div class="health-item">
                            <strong>Accident/Injury History:</strong>
                            <span id="accident-history">No accident/injury history reported</span>
                        </div>
                    </div>
                </div>

                <!-- Medications -->
                <div class="health-category">
                    <h4><i class="fas fa-pills"></i> Medications</h4>
                    <div class="health-items">
                        <div class="health-item">
                            <strong>Current Medications:</strong>
                            <span id="current-medications">No current medications reported</span>
                        </div>
                        <div class="health-item">
                            <strong>Long-term Medications:</strong>
                            <span id="longterm-medications">No long-term medications reported</span>
                        </div>
                    </div>
                </div>

                <!-- Allergies -->
                <div class="health-category">
                    <h4><i class="fas fa-exclamation-triangle"></i> Allergies</h4>
                    <div class="health-items">
                        <div class="health-item">
                            <strong>Known Allergies:</strong>
                            <span id="known-allergies">No known allergies reported</span>
                        </div>
                    </div>
                </div>

                <!-- Family History -->
                <div class="health-category">
                    <h4><i class="fas fa-users"></i> Family History</h4>
                    <div class="health-items">
                        <div class="health-item">
                            <strong>Family History of Chronic Diseases:</strong>
                            <span id="family-history">No family history of chronic diseases reported</span>
                        </div>
                    </div>
                </div>

                <!-- Social History -->
                <div class="health-category">
                    <h4><i class="fas fa-user-friends"></i> Social History</h4>
                    <div class="health-items">
                        <div class="health-item">
                            <strong>Smoking History:</strong>
                            <span id="smoking-history">No smoking history reported</span>
                        </div>
                        <div class="health-item">
                            <strong>Alcohol Consumption:</strong>
                            <span id="alcohol-consumption">No alcohol consumption reported</span>
                        </div>
                        <div class="health-item">
                            <strong>Recreational Drugs:</strong>
                            <span id="recreational-drugs">No recreational drug use reported</span>
                        </div>
                        <div class="health-item">
                            <strong>Exercise & Physical Activity:</strong>
                            <span id="exercise-activity">No exercise activity reported</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="healthHistoryEmpty" style="display: none;" class="empty-state">
                <i class="fas fa-notes-medical"></i>
                <p>No health history information available for this patient.</p>
            </div>
        </div>
    </div>
</div>

<style>
.health-history-modal-content {
    max-width: 800px;
    margin: 2% auto;
    max-height: 90vh;
    overflow-y: auto;
}

.health-category {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.health-category h4 {
    color: #2c5f2d;
    margin: 0 0 15px 0;
    font-size: 18px;
    border-bottom: 2px solid #2c5f2d;
    padding-bottom: 8px;
}

.health-category h4 i {
    margin-right: 8px;
    color: #367F2B;
}

.health-items {
    display: grid;
    gap: 12px;
}

.health-item {
    background: white;
    padding: 12px 15px;
    border-radius: 6px;
    border-left: 4px solid #367F2B;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.health-item strong {
    color: #2c5f2d;
    display: block;
    margin-bottom: 5px;
    font-size: 14px;
}

.health-item span {
    color: #495057;
    font-size: 14px;
    line-height: 1.4;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 15px;
    color: #adb5bd;
}

.loading-spinner {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #367F2B;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 20px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>