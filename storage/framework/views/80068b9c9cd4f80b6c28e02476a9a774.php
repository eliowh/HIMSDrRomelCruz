<form id="patientDetailsForm" class="patient-details-form">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="patient_id" value="<?php echo e($patient->id); ?>">
    
    <div class="form-sections">
        <!-- Personal Information Section -->
        <div class="form-section">
            <h4><i class="fas fa-user"></i> Personal Information</h4>
            <div class="form-row">
                <div class="form-group">
                    <label for="patient_no">Patient Number</label>
                    <input type="text" id="patient_no" name="patient_no" value="<?php echo e($patient->patient_no ?? ''); ?>" readonly class="form-input readonly">
                </div>
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo e($patient->first_name ?? ''); ?>" class="form-input" readonly>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="middle_name">Middle Name</label>
                    <input type="text" id="middle_name" name="middle_name" value="<?php echo e($patient->middle_name ?? ''); ?>" class="form-input" readonly>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo e($patient->last_name ?? ''); ?>" class="form-input" readonly>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="date_of_birth">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo e($patient->date_of_birth ?? ''); ?>" class="form-input" readonly>
                </div>
                <div class="form-group">
                    <label for="age_display">Age</label>
                    <?php
                        $ageYears = $patient->date_of_birth ? intval(\Carbon\Carbon::parse($patient->date_of_birth)->diffInYears(now())) : null;
                    ?>
                    <input type="text" id="age_display" name="age_display" value="<?php echo e($ageYears !== null ? $ageYears.' years' : 'N/A'); ?>" class="form-input readonly" readonly>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" class="form-input" disabled>
                        <option value="">Select Gender</option>
                        <option value="male" <?php echo e(strtolower($patient->gender ?? '') == 'male' ? 'selected' : ''); ?>>Male</option>
                        <option value="female" <?php echo e(strtolower($patient->gender ?? '') == 'female' ? 'selected' : ''); ?>>Female</option>
                        <option value="other" <?php echo e(strtolower($patient->gender ?? '') == 'other' ? 'selected' : ''); ?>>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="marital_status">Marital Status</label>
                    <select id="marital_status" name="marital_status" class="form-input" disabled>
                        <option value="">Select Status</option>
                        <option value="single" <?php echo e(strtolower($patient->marital_status ?? '') == 'single' ? 'selected' : ''); ?>>Single</option>
                        <option value="married" <?php echo e(strtolower($patient->marital_status ?? '') == 'married' ? 'selected' : ''); ?>>Married</option>
                        <option value="divorced" <?php echo e(strtolower($patient->marital_status ?? '') == 'divorced' ? 'selected' : ''); ?>>Divorced</option>
                        <option value="widowed" <?php echo e(strtolower($patient->marital_status ?? '') == 'widowed' ? 'selected' : ''); ?>>Widowed</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Contact Information Section -->
        <div class="form-section">
            <h4><i class="fas fa-address-card"></i> Contact Information</h4>
            <div class="form-row">
                <div class="form-group">
                    <label for="edit_contact_number">Contact Number</label>
                    <input id="edit_contact_number" type="number" name="contact_number" placeholder="Enter contact number" min="1000000000" max="99999999999" maxlength="11" oninput="if(this.value.length > 11) this.value = this.value.slice(0, 11);" value="<?php echo e($patient->contact_number ?? ''); ?>" class="form-input" readonly>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo e($patient->email ?? ''); ?>" class="form-input" readonly>
                </div>
            </div>
            
            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" class="form-input" rows="3" readonly><?php echo e($patient->address ?? ''); ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="emergency_contact_name">Emergency Contact Name</label>
                    <input type="text" id="emergency_contact_name" name="emergency_contact_name" value="<?php echo e($patient->emergency_contact_name ?? ''); ?>" class="form-input" readonly>
                </div>
                <div class="form-group">
                    <label for="emergency_contact_phone">Emergency Contact Phone</label>
                    <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" value="<?php echo e($patient->emergency_contact_phone ?? ''); ?>" class="form-input" readonly>
                </div>
            </div>
        </div>

        <!-- Medical Information Section -->
        <div class="form-section">
            <h4><i class="fas fa-hospital"></i> Medical Information</h4>
            <div class="form-row">
                <div class="form-group">
                    <label for="blood_type">Blood Type</label>
                    <select id="blood_type" name="blood_type" class="form-input" disabled>
                        <option value="">Select Blood Type</option>
                        <option value="A+" <?php echo e(($patient->blood_type ?? '') == 'A+' ? 'selected' : ''); ?>>A+</option>
                        <option value="A-" <?php echo e(($patient->blood_type ?? '') == 'A-' ? 'selected' : ''); ?>>A-</option>
                        <option value="B+" <?php echo e(($patient->blood_type ?? '') == 'B+' ? 'selected' : ''); ?>>B+</option>
                        <option value="B-" <?php echo e(($patient->blood_type ?? '') == 'B-' ? 'selected' : ''); ?>>B-</option>
                        <option value="AB+" <?php echo e(($patient->blood_type ?? '') == 'AB+' ? 'selected' : ''); ?>>AB+</option>
                        <option value="AB-" <?php echo e(($patient->blood_type ?? '') == 'AB-' ? 'selected' : ''); ?>>AB-</option>
                        <option value="O+" <?php echo e(($patient->blood_type ?? '') == 'O+' ? 'selected' : ''); ?>>O+</option>
                        <option value="O-" <?php echo e(($patient->blood_type ?? '') == 'O-' ? 'selected' : ''); ?>>O-</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="patient_status">Status</label>
                    <select id="patient_status" name="status" class="form-input" disabled>
                        <option value="active" <?php echo e(strtolower($patient->status ?? 'active') == 'active' ? 'selected' : ''); ?>>Active</option>
                        <option value="discharged" <?php echo e(strtolower($patient->status ?? '') == 'discharged' ? 'selected' : ''); ?>>Discharged</option>
                        <option value="deceased" <?php echo e(strtolower($patient->status ?? '') == 'deceased' ? 'selected' : ''); ?>>Deceased</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="allergies">Allergies</label>
                <textarea id="allergies" name="allergies" class="form-input" rows="2" readonly><?php echo e($patient->allergies ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="medical_history">Medical History</label>
                <textarea id="medical_history" name="medical_history" class="form-input" rows="3" readonly><?php echo e($patient->medical_history ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="current_medications">Current Medications</label>
                <textarea id="current_medications" name="current_medications" class="form-input" rows="3" readonly><?php echo e($patient->current_medications ?? ''); ?></textarea>
            </div>
        </div>

        <!-- Admission Information Section -->
        <div class="form-section">
            <h4><i class="fas fa-bed"></i> Admission Information</h4>
            <div class="form-row">
                <div class="form-group">
                    <label for="room_no">Room Number</label>
                    <input type="text" id="room_no" name="room_no" value="<?php echo e($patient->room_no ?? ''); ?>" class="form-input" readonly>
                </div>
                <div class="form-group">
                    <label for="admission_date">Admission Date</label>
                    <input type="datetime-local" id="admission_date" name="admission_date" 
                           value="<?php echo e($patient->admission_date ? date('Y-m-d\TH:i', strtotime($patient->admission_date)) : ''); ?>" 
                           class="form-input" readonly>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="discharge_date">Discharge Date</label>
                    <input type="datetime-local" id="discharge_date" name="discharge_date" 
                           value="<?php echo e($patient->discharge_date ? date('Y-m-d\TH:i', strtotime($patient->discharge_date)) : ''); ?>" 
                           class="form-input" readonly>
                </div>
                <div class="form-group">
                    <label for="admitting_doctor">Admitting Doctor</label>
                    <input type="text" id="admitting_doctor" name="admitting_doctor" value="<?php echo e($patient->admitting_doctor ?? ''); ?>" class="form-input" readonly>
                </div>
            </div>
            
            <div class="form-group">
                <label for="diagnosis">Diagnosis</label>
                <textarea id="diagnosis" name="diagnosis" class="form-input" rows="3" readonly><?php echo e($patient->diagnosis ?? ''); ?></textarea>
            </div>
        </div>

        <!-- Additional Notes Section -->
        <div class="form-section">
            <h4><i class="fas fa-sticky-note"></i> Additional Notes</h4>
            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" class="form-input" rows="4" readonly><?php echo e($patient->notes ?? ''); ?></textarea>
            </div>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="form-actions">
        <button type="button" id="editPatientBtn" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit Patient
        </button>
        <button type="button" id="savePatientBtn" class="btn btn-success" style="display: none;">
            <i class="fas fa-save"></i> Save Changes
        </button>
        <button type="button" id="cancelEditBtn" class="btn btn-secondary" style="display: none;">
            <i class="fas fa-times"></i> Cancel
        </button>
    </div>
</form>

<style>
.patient-details-form {
    max-height: 70vh;
    overflow-y: auto;
    padding: 0;
}

.form-sections {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.form-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    border-left: 4px solid #667eea;
}

.form-section h4 {
    color: #333;
    margin: 0 0 20px 0;
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-section h4 i {
    color: #667eea;
    font-size: 18px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 15px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 5px;
    font-size: 14px;
}

.form-input {
    padding: 8px 12px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.form-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.form-input.readonly {
    background-color: #e9ecef;
    cursor: not-allowed;
}

.form-input:disabled {
    background-color: #e9ecef;
    cursor: not-allowed;
}

.form-actions {
    margin-top: 25px;
    padding: 20px;
    background: #fff;
    border-top: 1px solid #e9ecef;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.btn-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
}

.btn-success:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-1px);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        justify-content: center;
    }
    
    .patient-details-form {
        max-height: 60vh;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editBtn = document.getElementById('editPatientBtn');
    const saveBtn = document.getElementById('savePatientBtn');
    const cancelBtn = document.getElementById('cancelEditBtn');
    const form = document.getElementById('patientDetailsForm');
    
    let originalValues = {};
    
    // Store original values when entering edit mode
    function storeOriginalValues() {
        const inputs = form.querySelectorAll('input, select, textarea');
        originalValues = {};
        inputs.forEach(input => {
            originalValues[input.name] = input.value;
        });
    }
    
    // Restore original values when canceling
    function restoreOriginalValues() {
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            if (originalValues[input.name] !== undefined) {
                input.value = originalValues[input.name];
            }
        });
    }
    
    // Calculate age from date of birth
    function calculateAge(dateOfBirth) {
        if (!dateOfBirth) return '';
        
        const today = new Date();
        const birthDate = new Date(dateOfBirth);
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        return age >= 0 ? age + ' years' : '';
    }
    
    // Update age when date of birth changes
    function updateAgeField() {
        const dobInput = document.getElementById('date_of_birth');
        const ageInput = document.getElementById('age_display');
        
        if (dobInput && ageInput) {
            dobInput.addEventListener('change', function() {
                const age = calculateAge(this.value);
                ageInput.value = age || 'N/A';
            });
        }
    }
    
    // Toggle edit mode
    function toggleEditMode(editMode) {
        const inputs = form.querySelectorAll('input:not([name="patient_id"]):not(#patient_no):not(#age_display), select, textarea');
        
        inputs.forEach(input => {
            if (editMode) {
                input.removeAttribute('readonly');
                input.removeAttribute('disabled');
                input.classList.remove('readonly');
            } else {
                if (input.tagName === 'SELECT') {
                    input.setAttribute('disabled', 'disabled');
                } else {
                    input.setAttribute('readonly', 'readonly');
                    input.classList.add('readonly');
                }
            }
        });
        
        // Show/hide buttons
        editBtn.style.display = editMode ? 'none' : 'flex';
        saveBtn.style.display = editMode ? 'flex' : 'none';
        cancelBtn.style.display = editMode ? 'flex' : 'none';
        
        // Initialize age calculation when entering edit mode
        if (editMode) {
            updateAgeField();
        }
    }
    
    // Edit button click
    editBtn.addEventListener('click', function() {
        storeOriginalValues();
        toggleEditMode(true);
    });
    
    // Cancel button click
    cancelBtn.addEventListener('click', function() {
        restoreOriginalValues();
        toggleEditMode(false);
    });
    
    // Initialize age calculation on page load
    updateAgeField();
    
    // Save button click
    saveBtn.addEventListener('click', async function() {
        const formData = new FormData(form);
        const submitBtn = this;
        const originalText = submitBtn.innerHTML;
        
        // Update button state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        submitBtn.disabled = true;
        
        try {
            const response = await fetch('/admin/patients/' + formData.get('patient_id') + '/update', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                adminSuccess('Patient information updated successfully!');
                toggleEditMode(false);
                // Optionally refresh the main table
                setTimeout(() => {
                    closePatientDetailsModal();
                    location.reload();
                }, 1500);
            } else {
                adminError('Error updating patient: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            adminError('An error occurred while updating patient information.');
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });
});
</script><?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\admin\partials\patient_details_form.blade.php ENDPATH**/ ?>