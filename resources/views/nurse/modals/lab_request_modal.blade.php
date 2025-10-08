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
                    <select id="specificTest" name="specific_test" required disabled onchange="updatePrice()">
                        <option value="" disabled selected>Select test first</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="testPrice">Test Price:</label>
                    <div class="price-display">
                        <span class="currency">₱</span>
                        <span id="testPrice" class="price-amount">0.00</span>
                    </div>
                    <input type="hidden" id="testPriceValue" name="test_price" value="0">
                </div>
                
                <div class="form-group">
                    <label for="totalPrice">Total Price:</label>
                    <div class="price-display total-price">
                        <span class="currency">₱</span>
                        <span id="totalPrice" class="price-amount">0.00</span>
                    </div>
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

<style>
.price-display {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    padding: 8px 12px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    gap: 4px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.price-display .currency {
    color: #28a745;
    font-weight: 600;
}

.price-display .price-amount {
    color: #333;
    font-weight: 600;
    font-size: 16px;
}

.price-display.total-price {
    background: #e7f3ff;
    border-color: #2196f3;
}

.price-display.total-price .currency,
.price-display.total-price .price-amount {
    color: #1976d2;
    font-weight: 700;
}

.form-row {
    display: flex;
    gap: 15px;
}

.form-row .form-group {
    flex: 1;
}

.checkbox-label-group {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}
</style>

<script>
// Lab request modal functionality
document.addEventListener('DOMContentLoaded', function() {
    // Store procedure data with prices
    let proceduresData = [];
    
    // Define updateTestOptions function for this modal only
    window.updateTestOptions = function() {
        const category = document.getElementById('testCategory').value;
        const specificTest = document.getElementById('specificTest');
        
        // Clear existing options and reset price
        specificTest.innerHTML = '';
        resetPrice();
        
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
                
                // Store procedures data for price lookup
                proceduresData = Array.isArray(data) ? data : [];
                
                // Add actual options
                if (Array.isArray(data)) {
                    data.forEach(procedure => {
                        const option = document.createElement('option');
                        option.value = procedure.name;
                        option.textContent = `${procedure.name} - ₱${parseFloat(procedure.price || 0).toFixed(2)}`;
                        option.setAttribute('data-price', procedure.price || 0);
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
    
    // Define updatePrice function
    window.updatePrice = function() {
        const specificTest = document.getElementById('specificTest');
        const selectedOption = specificTest.options[specificTest.selectedIndex];
        
        if (selectedOption && selectedOption.value) {
            const price = parseFloat(selectedOption.getAttribute('data-price') || 0);
            setPrice(price);
        } else {
            resetPrice();
        }
    };
    
    // Helper functions for price management
    function setPrice(price) {
        document.getElementById('testPrice').textContent = price.toFixed(2);
        document.getElementById('testPriceValue').value = price;
        document.getElementById('totalPrice').textContent = price.toFixed(2);
    }
    
    function resetPrice() {
        setPrice(0);
    }
    
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