<!-- Lab Request Modal -->
<div id="labRequestModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeLabRequestModal()">&times;</span>
        <h3>Request Lab Test</h3>
        <form id="labRequestForm">
            <input type="hidden" id="requestPatientId" name="patient_id">
            
            <div class="form-group">
                <label>Patient:</label>
                <p id="requestPatientInfo" class="patient-info-display"></p>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="testCategory">Test Category: *</label>
                    <select id="testCategory" name="test_category" required onchange="updateTestOptions()">
                        <option value="" disabled selected>Select category</option>
                        <option value="laboratory">Laboratory Tests</option>
                        <option value="xray">X-Ray Procedures</option>
                        <option value="ultrasound">Ultrasound</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="specificTest">Specific Test/Procedure: *</label>
                    <select id="specificTest" name="specific_test" required disabled>
                        <option value="" disabled selected>Select test first</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <div class="checkbox-label-group">
                    <input type="checkbox" id="enableAdditionalTests" title="Enable additional tests/notes">
                    <label for="enableAdditionalTests">Additional Tests/Notes:</label>
                </div>
                <textarea id="additionalTests" name="additional_tests" rows="3" disabled
                          placeholder="e.g., Additional lab work, special instructions, or multiple tests"></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="priority">Priority: *</label>
                    <select id="priority" name="priority" required>
                        <option value="normal">Normal</option>
                        <option value="urgent">Urgent</option>
                        <option value="stat">STAT</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="scheduledDate">Preferred Date:</label>
                    <input type="date" id="scheduledDate" name="scheduled_date" min="{{ date('Y-m-d') }}">
                </div>
            </div>
            
            <div class="form-group">
                <label for="notes">Clinical Notes:</label>
                <textarea id="notes" name="notes" rows="2" 
                          placeholder="Clinical indications, symptoms, or special instructions for the technician"></textarea>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn cancel-btn" onclick="closeLabRequestModal()">Cancel</button>
                <button type="submit" class="btn submit-btn">Submit Request</button>
            </div>
        </form>
    </div>
</div>

<script>
// Lab request modal functionality
document.addEventListener('DOMContentLoaded', function() {
    // Define updateTestOptions function for this modal only
    window.updateTestOptions = function() {
        const category = document.getElementById('testCategory').value;
        const specificTest = document.getElementById('specificTest');
        
        // Clear existing options
        specificTest.innerHTML = '';
        
        // Always add a disabled placeholder as the first option
        const placeholderOption = document.createElement('option');
        placeholderOption.value = '';
        placeholderOption.disabled = true;
        placeholderOption.selected = true;
        placeholderOption.textContent = 'Select specific test';
        specificTest.appendChild(placeholderOption);
        
        if (!category) {
            specificTest.disabled = true;
            return;
        }
        
        // Show loading state
        placeholderOption.textContent = 'Loading procedures...';
        specificTest.disabled = true;
        
        // Fetch procedures from database
        fetch(`/procedures/category?category=${category}`)
            .then(response => response.json())
            .then(data => {
                // Clear and re-add placeholder
                specificTest.innerHTML = '';
                const newPlaceholder = document.createElement('option');
                newPlaceholder.value = '';
                newPlaceholder.disabled = true;
                newPlaceholder.selected = true;
                newPlaceholder.textContent = 'Select specific test';
                specificTest.appendChild(newPlaceholder);
                
                if (data.error) {
                    console.error('Error fetching procedures:', data.error);
                    newPlaceholder.textContent = 'Error loading procedures';
                    specificTest.disabled = true;
                    return;
                }
                
                // Add actual options
                if (Array.isArray(data)) {
                    data.forEach(procedure => {
                        const option = document.createElement('option');
                        option.value = procedure.name;
                        option.textContent = procedure.name;
                        specificTest.appendChild(option);
                    });
                    
                    // Enable the dropdown after successfully loading procedures
                    specificTest.disabled = false;
                } else {
                    newPlaceholder.textContent = 'No procedures available';
                    specificTest.disabled = true;
                }
            })
            .catch(error => {
                console.error('Error fetching procedures:', error);
                const errorPlaceholder = specificTest.querySelector('option');
                if (errorPlaceholder) {
                    errorPlaceholder.textContent = 'Error loading procedures';
                }
                specificTest.disabled = true;
            });
    };
    
    // Enable/disable additional tests textarea based on checkbox
    const enableAdditionalTests = document.getElementById('enableAdditionalTests');
    const additionalTests = document.getElementById('additionalTests');
    
    if (enableAdditionalTests && additionalTests) {
        enableAdditionalTests.addEventListener('change', function() {
            additionalTests.disabled = !this.checked;
            if (!this.checked) {
                additionalTests.value = '';
            }
        });
    }
});
</script>