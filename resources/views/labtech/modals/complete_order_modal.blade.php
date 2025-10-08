<!-- Complete Order Modal -->
<div id="completeModal" class="modal">
    <div class="modal-content lab-results-modal">
        <span class="close">&times;</span>
        
        <div class="modal-header">
            <h3>Laboratory Results Form</h3>
            <div class="patient-info" id="modal-patient-info">
                <!-- Patient info will be populated here -->
            </div>
        </div>
        
        <form id="completeForm">
            <!-- Hidden fields -->
            <input type="hidden" id="order-id" name="order_id" value="">
            <input type="hidden" id="test-type" name="test_type" value="">
            
            <!-- Template Selector Section -->
            <div id="template-selector-section" class="template-selector" style="display: none;">
                <h4>Select Laboratory Template</h4>
                <div class="template-grid">
                    <div class="template-option" data-template="hematology">
                        <div class="template-icon">🩸</div>
                        <div class="template-name">Hematology</div>
                        <div class="template-desc">Complete Blood Count (CBC)</div>
                    </div>
                    <div class="template-option" data-template="blood_typing">
                        <div class="template-icon">🅰️</div>
                        <div class="template-name">Blood Typing</div>
                        <div class="template-desc">ABO & Rh Factor</div>
                    </div>
                    <div class="template-option" data-template="urinalysis">
                        <div class="template-icon">🧪</div>
                        <div class="template-name">Urinalysis</div>
                        <div class="template-desc">Complete Urine Analysis</div>
                    </div>
                    <div class="template-option" data-template="clinical_chemistry">
                        <div class="template-icon">⚗️</div>
                        <div class="template-name">Clinical Chemistry</div>
                        <div class="template-desc">Blood Chemistry Panel</div>
                    </div>
                    <div class="template-option" data-template="fecal_analysis">
                        <div class="template-icon">🔬</div>
                        <div class="template-name">Fecal Analysis</div>
                        <div class="template-desc">Stool Examination</div>
                    </div>
                    <div class="template-option" data-template="serology">
                        <div class="template-icon">🦠</div>
                        <div class="template-name">Serology</div>
                        <div class="template-desc">Infectious Disease Tests</div>
                    </div>
                    <div class="template-option" data-template="coagulation_test">
                        <div class="template-icon">⏰</div>
                        <div class="template-name">Coagulation</div>
                        <div class="template-desc">Blood Clotting Tests</div>
                    </div>
                    <div class="template-option" data-template="pregnancy_test">
                        <div class="template-icon">👶</div>
                        <div class="template-name">Pregnancy Test</div>
                        <div class="template-desc">HCG Test</div>
                    </div>
                </div>
                <div class="template-actions">
                    <button type="button" id="load-template-btn" class="btn btn-primary" disabled>
                        Load Selected Template
                    </button>
                    <button type="button" id="auto-detect-btn" class="btn btn-secondary">
                        Auto-Detect Template
                    </button>
                </div>
            </div>
            
            <!-- Dynamic Template Container -->
            <div id="dynamic-template-container" class="dynamic-template" style="display: none;">
                <!-- Selected template will be loaded here -->
            </div>
            
            <!-- Loading section -->
            <div id="loading-section" class="test-section">
                <div class="loading-container">
                    <i class="fas fa-spinner fa-spin"></i>
                    <span>Loading test details...</span>
                </div>
            </div>
            
            <!-- CBC/Hematology Results Template -->
            <div id="cbc-template" class="test-template" style="display: none;">
                <h4 class="template-title">COMPLETE BLOOD COUNT (CBC)</h4>
                
                <div class="lab-form-container">
                    <!-- Patient Header Section -->
                    <div class="form-header-section">
                        <div class="hospital-header">
                            <h3>ROMEL CRUZ HOSPITAL</h3>
                            <p>Laboratory Department</p>
                        </div>
                    </div>
                    
                    <!-- Test Results Grid -->
                    <div class="lab-results-table">
                        <table class="results-table">
                            <thead>
                                <tr>
                                    <th class="test-name-col">TEST</th>
                                    <th class="result-col">RESULT</th>
                                    <th class="unit-col">UNIT</th>
                                    <th class="reference-col">REFERENCE RANGE</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Hematology Section -->
                                <tr class="section-header">
                                    <td colspan="4"><strong>HEMATOLOGY</strong></td>
                                </tr>
                                <tr>
                                    <td>Red Blood Cell Count</td>
                                    <td><input type="text" name="rbc_count" class="result-input"></td>
                                    <td>x10⁶/μL</td>
                                    <td>4.5 - 5.5</td>
                                </tr>
                                <tr>
                                    <td>Hemoglobin</td>
                                    <td><input type="text" name="hemoglobin" class="result-input"></td>
                                    <td>g/dL</td>
                                    <td>12.0 - 16.0</td>
                                </tr>
                                <tr>
                                    <td>Hematocrit</td>
                                    <td><input type="text" name="hematocrit" class="result-input"></td>
                                    <td>%</td>
                                    <td>36.0 - 46.0</td>
                                </tr>
                                <tr>
                                    <td>Platelet Count</td>
                                    <td><input type="text" name="platelet_count" class="result-input"></td>
                                    <td>x10³/μL</td>
                                    <td>150 - 400</td>
                                </tr>
                                <tr>
                                    <td>White Blood Cell Count</td>
                                    <td><input type="text" name="wbc_count" class="result-input"></td>
                                    <td>x10³/μL</td>
                                    <td>4.5 - 11.0</td>
                                </tr>
                                
                                <!-- Red Cell Indices Section -->
                                <tr class="section-header">
                                    <td colspan="4"><strong>RED CELL INDICES</strong></td>
                                </tr>
                                <tr>
                                    <td>Mean Corpuscular Volume (MCV)</td>
                                    <td><input type="text" name="mcv" class="result-input"></td>
                                    <td>fL</td>
                                    <td>80.0 - 95.0</td>
                                </tr>
                                <tr>
                                    <td>Mean Corpuscular Hemoglobin (MCH)</td>
                                    <td><input type="text" name="mch" class="result-input"></td>
                                    <td>pg</td>
                                    <td>27.0 - 33.0</td>
                                </tr>
                                <tr>
                                    <td>MCHC</td>
                                    <td><input type="text" name="mchc" class="result-input"></td>
                                    <td>g/dL</td>
                                    <td>32.0 - 36.0</td>
                                </tr>
                                
                                <!-- Differential Count Section -->
                                <tr class="section-header">
                                    <td colspan="4"><strong>DIFFERENTIAL COUNT</strong></td>
                                </tr>
                                <tr>
                                    <td>Neutrophils</td>
                                    <td><input type="text" name="neutrophils" class="result-input"></td>
                                    <td>%</td>
                                    <td>50.0 - 70.0</td>
                                </tr>
                                <tr>
                                    <td>Lymphocytes</td>
                                    <td><input type="text" name="lymphocytes" class="result-input"></td>
                                    <td>%</td>
                                    <td>20.0 - 40.0</td>
                                </tr>
                                <tr>
                                    <td>Monocytes</td>
                                    <td><input type="text" name="monocytes" class="result-input"></td>
                                    <td>%</td>
                                    <td>2.0 - 8.0</td>
                                </tr>
                                <tr>
                                    <td>Eosinophils</td>
                                    <td><input type="text" name="eosinophils" class="result-input"></td>
                                    <td>%</td>
                                    <td>1.0 - 4.0</td>
                                </tr>
                                <tr>
                                    <td>Basophils</td>
                                    <td><input type="text" name="basophils" class="result-input"></td>
                                    <td>%</td>
                                    <td>0.0 - 2.0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Blood Chemistry Template -->
            <div id="chemistry-template" class="test-template" style="display: none;">
                <h4 class="template-title">BLOOD CHEMISTRY</h4>
                
                <div class="lab-form-container">
                    <!-- Patient Header Section -->
                    <div class="form-header-section">
                        <div class="hospital-header">
                            <h3>ROMEL CRUZ HOSPITAL</h3>
                            <p>Laboratory Department</p>
                        </div>
                    </div>
                    
                    <!-- Test Results Grid -->
                    <div class="lab-results-table">
                        <table class="results-table">
                            <thead>
                                <tr>
                                    <th class="test-name-col">TEST</th>
                                    <th class="result-col">RESULT</th>
                                    <th class="unit-col">UNIT</th>
                                    <th class="reference-col">REFERENCE RANGE</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Blood Glucose Section -->
                                <tr class="section-header">
                                    <td colspan="4"><strong>BLOOD GLUCOSE</strong></td>
                                </tr>
                                <tr>
                                    <td>Fasting Blood Sugar (FBS)</td>
                                    <td><input type="text" name="fbs" class="result-input"></td>
                                    <td>mg/dL</td>
                                    <td>70 - 100</td>
                                </tr>
                                <tr>
                                    <td>Random Blood Sugar (RBS)</td>
                                    <td><input type="text" name="rbs" class="result-input"></td>
                                    <td>mg/dL</td>
                                    <td>< 200</td>
                                </tr>
                                <tr>
                                    <td>HbA1c</td>
                                    <td><input type="text" name="hba1c" class="result-input"></td>
                                    <td>%</td>
                                    <td>< 5.7</td>
                                </tr>
                                
                                <!-- Lipid Profile Section -->
                                <tr class="section-header">
                                    <td colspan="4"><strong>LIPID PROFILE</strong></td>
                                </tr>
                                <tr>
                                    <td>Total Cholesterol</td>
                                    <td><input type="text" name="total_cholesterol" class="result-input"></td>
                                    <td>mg/dL</td>
                                    <td>< 200</td>
                                </tr>
                                <tr>
                                    <td>HDL Cholesterol</td>
                                    <td><input type="text" name="hdl" class="result-input"></td>
                                    <td>mg/dL</td>
                                    <td>> 40</td>
                                </tr>
                                <tr>
                                    <td>LDL Cholesterol</td>
                                    <td><input type="text" name="ldl" class="result-input"></td>
                                    <td>mg/dL</td>
                                    <td>< 130</td>
                                </tr>
                                <tr>
                                    <td>Triglycerides</td>
                                    <td><input type="text" name="triglycerides" class="result-input"></td>
                                    <td>mg/dL</td>
                                    <td>< 150</td>
                                </tr>
                                
                                <!-- Liver Function Section -->
                                <tr class="section-header">
                                    <td colspan="4"><strong>LIVER FUNCTION TESTS</strong></td>
                                </tr>
                                <tr>
                                    <td>ALT (SGPT)</td>
                                    <td><input type="text" name="alt" class="result-input"></td>
                                    <td>U/L</td>
                                    <td>7 - 56</td>
                                </tr>
                                <tr>
                                    <td>AST (SGOT)</td>
                                    <td><input type="text" name="ast" class="result-input"></td>
                                    <td>U/L</td>
                                    <td>10 - 40</td>
                                </tr>
                                <tr>
                                    <td>Total Bilirubin</td>
                                    <td><input type="text" name="total_bilirubin" class="result-input"></td>
                                    <td>mg/dL</td>
                                    <td>0.3 - 1.2</td>
                                </tr>
                                <tr>
                                    <td>Direct Bilirubin</td>
                                    <td><input type="text" name="direct_bilirubin" class="result-input"></td>
                                    <td>mg/dL</td>
                                    <td>0.0 - 0.3</td>
                                </tr>
                                
                                <!-- Kidney Function Section -->
                                <tr class="section-header">
                                    <td colspan="4"><strong>KIDNEY FUNCTION TESTS</strong></td>
                                </tr>
                                <tr>
                                    <td>Blood Urea Nitrogen (BUN)</td>
                                    <td><input type="text" name="bun" class="result-input"></td>
                                    <td>mg/dL</td>
                                    <td>7 - 20</td>
                                </tr>
                                <tr>
                                    <td>Creatinine</td>
                                    <td><input type="text" name="creatinine" class="result-input"></td>
                                    <td>mg/dL</td>
                                    <td>0.6 - 1.2</td>
                                </tr>
                                <tr>
                                    <td>Uric Acid</td>
                                    <td><input type="text" name="uric_acid" class="result-input"></td>
                                    <td>mg/dL</td>
                                    <td>3.5 - 7.2</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Urinalysis Template -->
            <div id="urinalysis-template" class="test-template" style="display: none;">
                <h4 class="template-title">URINALYSIS</h4>
                
                <div class="lab-form-container">
                    <!-- Patient Header Section -->
                    <div class="form-header-section">
                        <div class="hospital-header">
                            <h3>ROMEL CRUZ HOSPITAL</h3>
                            <p>Laboratory Department</p>
                        </div>
                    </div>
                    
                    <!-- Test Results Grid -->
                    <div class="lab-results-table">
                        <table class="results-table">
                            <thead>
                                <tr>
                                    <th class="test-name-col">TEST</th>
                                    <th class="result-col">RESULT</th>
                                    <th class="unit-col">UNIT</th>
                                    <th class="reference-col">REFERENCE RANGE</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Physical Examination Section -->
                                <tr class="section-header">
                                    <td colspan="4"><strong>PHYSICAL EXAMINATION</strong></td>
                                </tr>
                                <tr>
                                    <td>Color</td>
                                    <td>
                                        <select name="urine_color" class="result-select">
                                            <option value="">-</option>
                                            <option value="Pale Yellow">Pale Yellow</option>
                                            <option value="Yellow">Yellow</option>
                                            <option value="Dark Yellow">Dark Yellow</option>
                                            <option value="Amber">Amber</option>
                                            <option value="Red">Red</option>
                                            <option value="Brown">Brown</option>
                                        </select>
                                    </td>
                                    <td>-</td>
                                    <td>Pale Yellow to Yellow</td>
                                </tr>
                                <tr>
                                    <td>Transparency</td>
                                    <td>
                                        <select name="urine_clarity" class="result-select">
                                            <option value="">-</option>
                                            <option value="Clear">Clear</option>
                                            <option value="Slightly Turbid">Slightly Turbid</option>
                                            <option value="Turbid">Turbid</option>
                                            <option value="Cloudy">Cloudy</option>
                                        </select>
                                    </td>
                                    <td>-</td>
                                    <td>Clear</td>
                                </tr>
                                <tr>
                                    <td>Specific Gravity</td>
                                    <td><input type="text" name="specific_gravity" class="result-input"></td>
                                    <td>-</td>
                                    <td>1.003 - 1.030</td>
                                </tr>
                                
                                <!-- Chemical Examination Section -->
                                <tr class="section-header">
                                    <td colspan="4"><strong>CHEMICAL EXAMINATION</strong></td>
                                </tr>
                                <tr>
                                    <td>pH</td>
                                    <td><input type="text" name="urine_ph" class="result-input"></td>
                                    <td>-</td>
                                    <td>5.0 - 8.0</td>
                                </tr>
                                <tr>
                                    <td>Protein</td>
                                    <td>
                                        <select name="protein" class="result-select">
                                            <option value="">-</option>
                                            <option value="Negative">Negative</option>
                                            <option value="Trace">Trace</option>
                                            <option value="1+">1+</option>
                                            <option value="2+">2+</option>
                                            <option value="3+">3+</option>
                                            <option value="4+">4+</option>
                                        </select>
                                    </td>
                                    <td>-</td>
                                    <td>Negative</td>
                                </tr>
                                <tr>
                                    <td>Glucose</td>
                                    <td>
                                        <select name="glucose" class="result-select">
                                            <option value="">-</option>
                                            <option value="Negative">Negative</option>
                                            <option value="Trace">Trace</option>
                                            <option value="1+">1+</option>
                                            <option value="2+">2+</option>
                                            <option value="3+">3+</option>
                                            <option value="4+">4+</option>
                                        </select>
                                    </td>
                                    <td>-</td>
                                    <td>Negative</td>
                                </tr>
                                <tr>
                                    <td>Ketones</td>
                                    <td>
                                        <select name="ketones" class="result-select">
                                            <option value="">-</option>
                                            <option value="Negative">Negative</option>
                                            <option value="Small">Small</option>
                                            <option value="Moderate">Moderate</option>
                                            <option value="Large">Large</option>
                                        </select>
                                    </td>
                                    <td>-</td>
                                    <td>Negative</td>
                                </tr>
                                
                                <!-- Microscopic Examination Section -->
                                <tr class="section-header">
                                    <td colspan="4"><strong>MICROSCOPIC EXAMINATION</strong></td>
                                </tr>
                                <tr>
                                    <td>RBC</td>
                                    <td><input type="text" name="urine_rbc" class="result-input"></td>
                                    <td>/hpf</td>
                                    <td>0 - 2</td>
                                </tr>
                                <tr>
                                    <td>WBC</td>
                                    <td><input type="text" name="urine_wbc" class="result-input"></td>
                                    <td>/hpf</td>
                                    <td>0 - 5</td>
                                </tr>
                                <tr>
                                    <td>Epithelial Cells</td>
                                    <td><input type="text" name="epithelial_cells" class="result-input"></td>
                                    <td>/hpf</td>
                                    <td>Few</td>
                                </tr>
                                <tr>
                                    <td>Bacteria</td>
                                    <td>
                                        <select name="bacteria" class="result-select">
                                            <option value="">-</option>
                                            <option value="Few">Few</option>
                                            <option value="Moderate">Moderate</option>
                                            <option value="Many">Many</option>
                                        </select>
                                    </td>
                                    <td>-</td>
                                    <td>Few</td>
                                </tr>
                                <tr>
                                    <td>Casts</td>
                                    <td><input type="text" name="casts" class="result-input"></td>
                                    <td>/lpf</td>
                                    <td>None</td>
                                </tr>
                                <tr>
                                    <td>Crystals</td>
                                    <td><input type="text" name="crystals" class="result-input"></td>
                                    <td>-</td>
                                    <td>Few</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- General Template (for other tests) -->
            <div id="general-template" class="test-template" style="display: none;">
                <h4 class="template-title">Laboratory Results</h4>
                <div class="test-panel">
                    <div class="test-fields">
                        <div class="test-field full-width">
                            <label>Test Results:</label>
                            <textarea name="general_results" rows="8" class="test-textarea" placeholder="Enter test results..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Original Template (for X-ray and image-based tests) -->
            <div id="original-template" class="test-template" style="display: none;">
                <h4 class="template-title">Upload Results</h4>
                <div class="test-panel">
                    <div class="test-fields">
                        <div class="test-field full-width">
                            <label for="results">Notes/Summary:</label>
                            <textarea id="results" name="results" rows="4" class="test-textarea" placeholder="Enter test results summary or notes..."></textarea>
                        </div>
                        
                        <div class="test-field full-width">
                            <label for="resultsPdf">Upload Results (PDF): *</label>
                            <div class="file-upload-container">
                                <input type="file" id="resultsPdf" name="results_pdf" accept=".pdf" class="file-input">
                                <small class="file-hint">Please upload the results as PDF (required for X-ray and imaging tests)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Common Fields (only for lab tests, not X-ray) -->
            <div class="common-fields" id="lab-common-fields">
                <div class="test-panel">
                    <h5 class="panel-title">Clinical Assessment</h5>
                    <div class="test-fields">
                        <div class="test-field full-width">
                            <label>Clinical Interpretation:</label>
                            <textarea name="interpretation" rows="3" class="test-textarea" placeholder="Enter clinical interpretation and findings..."></textarea>
                        </div>
                        
                        <div class="test-field full-width">
                            <label>Additional Notes:</label>
                            <textarea name="notes" rows="2" class="test-textarea" placeholder="Any additional observations or recommendations..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn cancel-btn" onclick="closeCompleteModal()">Cancel</button>
                <button type="submit" class="btn complete-btn">
                    <span class="btn-text">Complete Order & Generate PDF</span>
                    <span class="btn-loading" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i> Generating PDF...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.lab-results-modal {
    max-width: 1000px;
    max-height: 90vh;
    overflow-y: auto;
    padding: 0;
}

.modal-header {
    background: #f8f9fa;
    padding: 20px;
    border-bottom: 1px solid #dee2e6;
    margin: -20px -20px 20px -20px;
}

.modal-header h3 {
    margin: 0 0 10px 0;
    color: #2c3e50;
    font-size: 1.5em;
}

.patient-info {
    background: #e3f2fd;
    padding: 12px;
    border-radius: 4px;
    border-left: 4px solid #2196f3;
    font-size: 0.95em;
}

.loading-container {
    text-align: center;
    padding: 40px;
    color: #6c757d;
}

.loading-container i {
    font-size: 24px;
    margin-right: 10px;
}

.test-template {
    margin-bottom: 20px;
}

.template-title {
    color: #2c3e50;
    margin: 0 0 20px 0;
    padding-bottom: 8px;
    border-bottom: 2px solid #3498db;
    font-size: 1.3em;
    text-align: center;
    font-weight: bold;
}

.lab-form-container {
    background: white;
    border: 2px solid #333;
    border-radius: 8px;
    overflow: hidden;
}

.form-header-section {
    background: #f8f9fa;
    padding: 15px;
    border-bottom: 2px solid #333;
    text-align: center;
}

.hospital-header h3 {
    margin: 0;
    font-size: 1.4em;
    font-weight: bold;
    color: #2c3e50;
}

.hospital-header p {
    margin: 5px 0 0 0;
    font-size: 1.1em;
    color: #666;
}

.lab-results-table {
    padding: 0;
}

.results-table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
    font-size: 0.95em;
}

.results-table th {
    background: #2c3e50;
    color: white;
    padding: 12px 8px;
    text-align: left;
    font-weight: bold;
    border: 1px solid #333;
    font-size: 0.9em;
}

.test-name-col {
    width: 40%;
}

.result-col {
    width: 20%;
}

.unit-col {
    width: 15%;
}

.reference-col {
    width: 25%;
}

.results-table td {
    padding: 8px;
    border: 1px solid #ddd;
    vertical-align: middle;
}

.section-header td {
    background: #e9ecef;
    font-weight: bold;
    color: #2c3e50;
    border: 1px solid #333;
    padding: 10px 8px;
}

.result-input {
    width: 100%;
    padding: 6px 8px;
    border: 1px solid #ced4da;
    border-radius: 3px;
    font-size: 0.9em;
    text-align: center;
    background: #fff;
}

.result-input:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    outline: 0;
}

.result-select {
    width: 100%;
    padding: 6px 8px;
    border: 1px solid #ced4da;
    border-radius: 3px;
    font-size: 0.9em;
    background: #fff;
}

.result-select:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    outline: 0;
}

.results-table tr:nth-child(even):not(.section-header) {
    background: #f8f9fa;
}

.results-table tr:hover:not(.section-header) {
    background-color: #f1f8ff;
}

/* Template Selector Styles */
.template-selector {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #dee2e6;
}

.template-selector h4 {
    color: #2c3e50;
    margin: 0 0 20px 0;
    text-align: center;
    font-size: 1.3em;
}

.template-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.template-option {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.template-option:hover {
    border-color: #3498db;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.template-option.selected {
    border-color: #2196f3;
    background: #e3f2fd;
    box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.2);
}

.template-icon {
    font-size: 2em;
    margin-bottom: 8px;
}

.template-name {
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 5px;
    font-size: 1em;
}

.template-desc {
    color: #6c757d;
    font-size: 0.85em;
    line-height: 1.3;
}

.template-actions {
    text-align: center;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    display: inline-block;
    margin: 0 5px;
    transition: background-color 0.3s ease;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #2980b9;
}

.btn-primary:disabled {
    background: #bdc3c7;
    cursor: not-allowed;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background: #7f8c8d;
}

.dynamic-template {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    min-height: 400px;
}

.dynamic-template iframe {
    width: 100%;
    border: none;
    min-height: 600px;
}

.results-grid {

.test-panel {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 16px;
}

.panel-title {
    margin: 0 0 15px 0;
    color: #495057;
    font-size: 1.1em;
    padding-bottom: 8px;
    border-bottom: 1px solid #dee2e6;
}

.test-fields {
    display: grid;
    gap: 12px;
}

.test-field {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.test-field.full-width {
    flex-direction: column;
    gap: 6px;
}

.test-field label {
    flex: 0 0 200px;
    font-weight: 500;
    color: #495057;
    font-size: 0.95em;
    padding-top: 6px;
}

.test-field.full-width label {
    flex: none;
    padding-top: 0;
}

.input-group {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1;
}

.test-input, .test-select {
    padding: 6px 10px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 0.95em;
    min-width: 80px;
}

.test-input:focus, .test-select:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    outline: 0;
}

.test-textarea {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 0.95em;
    resize: vertical;
    font-family: inherit;
}

.test-textarea:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    outline: 0;
}

.unit {
    color: #6c757d;
    font-weight: 500;
    font-size: 0.9em;
    min-width: 50px;
}

.reference-range {
    color: #28a745;
    font-size: 0.85em;
    background: #d4edda;
    padding: 2px 6px;
    border-radius: 3px;
    border: 1px solid #c3e6cb;
    white-space: nowrap;
}

.common-fields {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px solid #dee2e6;
}

.form-actions {
    background: #f8f9fa;
    margin: 20px -20px -20px -20px;
    padding: 15px 20px;
    border-top: 1px solid #dee2e6;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.complete-btn {
    background: #28a745;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
}

.complete-btn:hover {
    background: #218838;
}

.complete-btn:disabled {
    background: #6c757d;
    cursor: not-allowed;
}

.cancel-btn {
    background: #6c757d;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
}

.cancel-btn:hover {
    background: #5a6268;
}

.btn-loading {
    display: flex;
    align-items: center;
    gap: 5px;
}

.file-upload-container {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.file-input {
    padding: 8px;
    border: 2px dashed #dee2e6;
    border-radius: 4px;
    background: #f8f9fa;
    cursor: pointer;
}

.file-input:hover {
    border-color: #80bdff;
    background: #e3f2fd;
}

.file-hint {
    color: #6c757d;
    font-size: 0.85em;
    font-style: italic;
}

@media (max-width: 768px) {
    .lab-results-modal {
        max-width: 95%;
        margin: 10px;
    }
    
    .test-field {
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
    }
    
    .test-field label {
        flex: none;
        padding-top: 0;
    }
    
    .results-grid {
        gap: 15px;
    }
    
    .modal-header {
        margin: -20px -10px 15px -10px;
        padding: 15px 10px;
    }
    
    .form-actions {
        margin: 15px -10px -10px -10px;
        padding: 10px;
    }
}
</style>

<script>
// Template selector functionality
let selectedTemplate = null;

// Handle template selection
function selectTemplate(templateId, templateName) {
    // Remove previous selection
    document.querySelectorAll('.template-option').forEach(option => {
        option.classList.remove('selected');
    });
    
    // Add selection to clicked template
    const selectedOption = document.querySelector(`[data-template="${templateId}"]`);
    if (selectedOption) {
        selectedOption.classList.add('selected');
    }
    
    selectedTemplate = templateId;
    
    // Enable load template button
    const loadBtn = document.getElementById('load-template-btn');
    if (loadBtn) {
        loadBtn.disabled = false;
    }
}

// Load the selected template
function loadTemplate() {
    if (!selectedTemplate) {
        alert('Please select a template first.');
        return;
    }
    
    const container = document.getElementById('dynamic-template-container');
    if (!container) return;
    
    // Show loading
    container.innerHTML = '<div style="text-align: center; padding: 40px;"><i class="fas fa-spinner fa-spin fa-2x" style="color: #3498db;"></i><br><br>Loading template...</div>';
    container.style.display = 'block';
    
    // Create iframe to load template
    const iframe = document.createElement('iframe');
    iframe.src = `/labtech/templates/${selectedTemplate}`;
    iframe.style.width = '100%';
    iframe.style.border = 'none';
    iframe.style.minHeight = '600px';
    
    iframe.onload = function() {
        // Template loaded successfully
        console.log('Template loaded:', selectedTemplate);
    };
    
    iframe.onerror = function() {
        container.innerHTML = '<div style="text-align: center; padding: 40px; color: #e74c3c;"><i class="fas fa-exclamation-circle fa-2x"></i><br><br>Error loading template. Please try again.</div>';
    };
    
    container.innerHTML = '';
    container.appendChild(iframe);
}

// Auto-detect template based on procedure type
function autoDetectTemplate() {
    const procedureElement = document.getElementById('orderProcedure');
    if (!procedureElement) return;
    
    const procedure = procedureElement.textContent.toLowerCase();
    let suggestedTemplate = null;
    
    // Template detection logic
    if (procedure.includes('hematology') || procedure.includes('cbc') || procedure.includes('hemoglobin')) {
        suggestedTemplate = 'hematology';
    } else if (procedure.includes('blood typing') || procedure.includes('abo') || procedure.includes('rh')) {
        suggestedTemplate = 'blood_typing';
    } else if (procedure.includes('urinalysis') || procedure.includes('urine')) {
        suggestedTemplate = 'urinalysis';
    } else if (procedure.includes('chemistry') || procedure.includes('glucose') || procedure.includes('cholesterol')) {
        suggestedTemplate = 'clinical_chemistry';
    } else if (procedure.includes('fecal') || procedure.includes('stool')) {
        suggestedTemplate = 'fecal_analysis';
    } else if (procedure.includes('serology') || procedure.includes('hepatitis') || procedure.includes('hiv')) {
        suggestedTemplate = 'serology';
    } else if (procedure.includes('coagulation') || procedure.includes('pt') || procedure.includes('ptt')) {
        suggestedTemplate = 'coagulation_test';
    } else if (procedure.includes('pregnancy') || procedure.includes('hcg')) {
        suggestedTemplate = 'pregnancy_test';
    }
    
    if (suggestedTemplate) {
        selectTemplate(suggestedTemplate, suggestedTemplate.replace('_', ' '));
        alert(`Auto-detected template: ${suggestedTemplate.replace('_', ' ')}`);
    } else {
        alert('Could not auto-detect template. Please select manually.');
    }
}

// Add event listeners when document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Template option click handlers
    document.querySelectorAll('.template-option').forEach(option => {
        option.addEventListener('click', function() {
            const templateId = this.getAttribute('data-template');
            const templateName = this.querySelector('.template-name').textContent;
            selectTemplate(templateId, templateName);
        });
    });
    
    // Load template button
    const loadBtn = document.getElementById('load-template-btn');
    if (loadBtn) {
        loadBtn.addEventListener('click', loadTemplate);
    }
    
    // Auto-detect button
    const autoBtn = document.getElementById('auto-detect-btn');
    if (autoBtn) {
        autoBtn.addEventListener('click', autoDetectTemplate);
    }
});

// Original modal functions (updated)
function openCompleteModal(orderId, procedure, patient) {
    document.getElementById('completeOrderId').value = orderId;
    document.getElementById('orderProcedure').textContent = procedure;
    document.getElementById('orderPatient').textContent = patient;
    
    // Reset template selection
    selectedTemplate = null;
    document.querySelectorAll('.template-option').forEach(option => {
        option.classList.remove('selected');
    });
    const loadBtn = document.getElementById('load-template-btn');
    if (loadBtn) loadBtn.disabled = true;
    
    // Clear template container
    const container = document.getElementById('dynamic-template-container');
    if (container) {
        container.innerHTML = '<div style="text-align: center; padding: 40px; color: #7f8c8d;"><i class="fas fa-file-medical fa-3x"></i><br><br>Select a template above to begin</div>';
        container.style.display = 'none';
    }
    
    // Show template selector
    const templateSelector = document.getElementById('template-selector-section');
    if (templateSelector) {
        templateSelector.style.display = 'block';
    }
    
    document.getElementById('completeOrderModal').style.display = 'block';
}

function closeCompleteModal() {
    document.getElementById('completeOrderModal').style.display = 'none';
}
</script>