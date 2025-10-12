<div id="finalizeModal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h4>Finalize Diagnosis</h4>
            <button class="modal-close">×</button>
        </div>
        <div class="modal-body">
            <form id="finalizeForm">
                <input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>">
                <input type="hidden" id="finalize_admission_id" name="admission_id" />

                <div class="form-group">
                    <label for="final_diagnosis">Final Diagnosis (ICD-10)</label>
                    <div class="input-validation-container">
                        <div class="suggestion-container">
                            <input id="final_diagnosis" name="final_diagnosis" type="text" autocomplete="off" placeholder="Type ICD-10 code or disease name" />
                            <div id="final-diagnosis-suggestions" class="suggestion-list"></div>
                        </div>
                        <div id="final-diagnosis-validation-error" class="validation-error"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="final_diagnosis_description">Diagnosis Description</label>
                    <input id="final_diagnosis_description" name="final_diagnosis_description" type="text" placeholder="Description will appear here" readonly />
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="modal-close btn secondary">Cancel</button>
            <button id="saveFinalizeBtn" class="btn primary">Save Final Diagnosis</button>
        </div>
    </div>
</div>

<style>
/* Modal styling to match existing app design */
#finalizeModal.modal { 
    position: fixed; 
    left: 0; 
    top: 0; 
    right: 0; 
    bottom: 0; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    background: rgba(0,0,0,0.5); 
    z-index: 3000; 
    backdrop-filter: blur(2px);
}

#finalizeModal .modal-content { 
    background: #fff; 
    padding: 24px; 
    width: 580px; 
    max-width: 90vw;
    border-radius: 12px; 
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    position: relative;
}

#finalizeModal .modal-header { 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e9ecef;
}

#finalizeModal .modal-header h4 {
    margin: 0;
    color: #2c3e50;
    font-size: 1.25rem;
    font-weight: 600;
}

#finalizeModal .modal-close {
    background: none;
    border: none;
    font-size: 24px;
    color: #6c757d;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s ease;
}

#finalizeModal .modal-close:hover {
    background: #f8f9fa;
    color: #495057;
}

#finalizeModal .modal-body { 
    margin: 0;
}

#finalizeModal .form-group { 
    margin-bottom: 20px; 
}

#finalizeModal .form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #495057;
    font-size: 14px;
}

#finalizeModal input[type="text"] { 
    width: 100%; 
    padding: 12px 16px; 
    border: 1px solid #ced4da; 
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    background: #fff;
}

#finalizeModal input[type="text"]:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
}

#finalizeModal input[readonly] {
    background-color: #f8f9fa;
    color: #6c757d;
}

#finalizeModal .modal-footer { 
    display: flex; 
    gap: 12px; 
    justify-content: flex-end; 
    margin-top: 24px;
    padding-top: 16px;
    border-top: 1px solid #e9ecef;
}

#finalizeModal .btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    min-width: 120px;
}

#finalizeModal .btn.primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
}

#finalizeModal .btn.primary:hover {
    background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
    transform: translateY(-1px);
}

#finalizeModal .btn.secondary {
    background: #f8f9fa;
    border: 1px solid #ced4da;
    color: #495057;
}

#finalizeModal .btn.secondary:hover {
    background: #e9ecef;
    border-color: #adb5bd;
}

/* Suggestion dropdown styling */
#finalizeModal .suggestion-container {
    position: relative;
}

#finalizeModal .suggestion-list {
    position: absolute !important;
    left: 0;
    right: 0;
    z-index: 9999 !important;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
    max-height: 320px;
    overflow-y: auto;
    padding: 4px 0;
    display: none;
}

#finalizeModal .icd-suggestion {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    cursor: pointer;
    border-bottom: 1px solid #f1f5f9;
    transition: background-color 0.2s ease;
}

#finalizeModal .icd-suggestion:hover,
#finalizeModal .icd-suggestion.active {
    background-color: #f0f8ff;
}

#finalizeModal .icd-suggestion .code {
    font-weight: 700;
    color: #0b5ed7;
    margin-right: 12px;
}

#finalizeModal .icd-suggestion .desc {
    color: #374151;
    font-size: 0.95em;
    margin-left: 12px;
    flex: 1;
    text-align: left;
}

#finalizeModal .icd-suggestion:last-child {
    border-bottom: none;
}

/* Validation error styling */
#finalizeModal .validation-error {
    color: #dc3545;
    font-size: 0.875em;
    margin-top: 4px;
    padding: 4px 8px;
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    border-radius: 4px;
    display: none;
}

#finalizeModal .validation-error.visible {
    display: block;
}

/* Input validation container */
#finalizeModal .input-validation-container {
    position: relative;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const modal = document.getElementById('finalizeModal');
    const closeBtns = modal.querySelectorAll('.modal-close');
    closeBtns.forEach(b => b.addEventListener('click', () => { modal.style.display = 'none'; }));

    // Enhanced ICD-10 autocomplete (same as admission diagnosis)
    (function(){
        const input = document.getElementById('final_diagnosis');
        const descField = document.getElementById('final_diagnosis_description');
        const container = document.getElementById('final-diagnosis-suggestions');
        const errorDiv = document.getElementById('final-diagnosis-validation-error');
        if (!input || !container) return;
        
        let timer = null; 
        let activeIndex = -1; 
        let lastItems = [];
        let allCodes = [];
        
        function clearSuggestions(){ 
            container.innerHTML=''; 
            container.style.display='none'; 
            activeIndex=-1; 
            lastItems=[];
        }
        
        function renderSuggestions(items, showAll = false){ 
            lastItems=items; 
            if(!items||!items.length){ 
                clearSuggestions(); 
                return;
            } 
            container.innerHTML=''; 
            
            const itemsToShow = showAll ? lastItems : lastItems.slice(0, 10);
            
            itemsToShow.forEach((it,idx)=>{ 
                const el=document.createElement('div'); 
                el.className='icd-suggestion'; 
                el.dataset.index=idx; 
                el.innerHTML = '<span class="code">'+escapeHtml(it.code)+'</span> <span class="desc">'+escapeHtml(it.description)+'</span>'; 
                el.addEventListener('click',()=>selectItem(idx, itemsToShow)); 
                container.appendChild(el); 
            }); 
            container.style.display='block'; 
            activeIndex=-1; 
        }
        
        function escapeHtml(s){ if(!s) return ''; return s.replace(/[&<>"']/g, (m)=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[m])); }
        
        function selectItem(idx, items = lastItems){ 
            const item=items[idx]; 
            if(!item) return; 
            input.value = item.code || ''; 
            if(descField) descField.value = item.description || ''; 
            hideError();
            clearSuggestions(); 
        }
        
        function highlightActive(){ 
            const nodes = container.querySelectorAll('.icd-suggestion'); 
            nodes.forEach((n,i)=> n.classList.toggle('active', i===activeIndex)); 
        }
        
        function showError(message) {
            if (errorDiv) {
                errorDiv.textContent = message;
                errorDiv.style.display = 'block';
                errorDiv.classList.add('visible');
            }
        }

        function hideError() {
            if (errorDiv) {
                errorDiv.textContent = '';
                errorDiv.style.display = 'none';
                errorDiv.classList.remove('visible');
            }
        }

        function loadAllCodes(showSuggestions = false) {
            // Avoid re-fetching if we already have the master list
            if (allCodes.length > 0) {
                if (showSuggestions) {
                    renderSuggestions(allCodes, true);
                }
                return;
            }
            fetch('/icd10/search?q=')
                .then(async r => {
                    const ct = (r.headers.get('content-type') || '').toLowerCase();
                    const text = await r.text();
                    if (ct.includes('application/json')) {
                        try {
                            allCodes = JSON.parse(text); // Store the definitive list
                            if (showSuggestions) {
                                renderSuggestions(allCodes, true);
                            }
                        } catch(e) { console.error('ICD parse error', e); }
                    }
                })
                .catch(e => console.error('ICD fetch error', e));
        }
        
        // Add dynamic search functionality on input
        input.addEventListener('input', () => {
            clearTimeout(timer);
            const val = input.value.trim();
            if (!val) { 
                clearSuggestions(); 
                hideError(); 
                if (descField) descField.value = '';
                return; 
            }
            
            timer = setTimeout(() => {
                fetch('/icd10/search?q=' + encodeURIComponent(val))
                    .then(async r => {
                        const ct = (r.headers.get('content-type') || '').toLowerCase();
                        const text = await r.text();
                        if (ct.includes('application/json')) {
                            try {
                                const items = JSON.parse(text);
                                renderSuggestions(items); // Show filtered search results
                            } catch(e) { 
                                console.error('ICD parse error', e); 
                            }
                        }
                    })
                    .catch(e => console.error('ICD fetch error', e));
            }, 300); // 300ms debounce delay
        });
        
        input.addEventListener('focus', () => {
            if (!input.value.trim()) {
                if (allCodes.length > 0) {
                    renderSuggestions(allCodes.slice(0, 50), true);
                } else {
                    loadAllCodes(true);
                }
            }
        });
        
        input.addEventListener('keydown', (e)=>{
            if(!lastItems.length) return;
            if(e.key==='ArrowDown'){ e.preventDefault(); activeIndex=(activeIndex+1)%lastItems.length; highlightActive(); }
            else if(e.key==='ArrowUp'){ e.preventDefault(); activeIndex=activeIndex<=0?(lastItems.length-1):(activeIndex-1); highlightActive(); }
            else if(e.key==='Enter'){ e.preventDefault(); if(activeIndex>=0) selectItem(activeIndex); }
            else if(e.key==='Escape'){ clearSuggestions(); }
        });
        
        input.addEventListener('blur', () => {
            setTimeout(() => {
                clearSuggestions();
            }, 200);
        });
        
        document.addEventListener('click',(e)=>{ 
            if(!container.contains(e.target) && e.target !== input) {
                clearSuggestions();
            }
        });
        
        loadAllCodes(false);
    })();

    // Save final diagnosis
    const saveBtn = document.getElementById('saveFinalizeBtn');
    saveBtn.addEventListener('click', function(e){
        e.preventDefault();
        const admissionId = document.getElementById('finalize_admission_id').value;
        const finalDiagnosis = document.getElementById('final_diagnosis').value.trim();
        const finalDesc = document.getElementById('final_diagnosis_description').value.trim();
        
        if (!admissionId) { alert('No admission selected'); return; }
        if (!finalDiagnosis) { alert('Please enter final diagnosis'); document.getElementById('final_diagnosis').focus(); return; }

        // Disable button during save
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';

        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        fetch(`/doctor/admissions/${encodeURIComponent(admissionId)}/finalize`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
            body: JSON.stringify({ final_diagnosis: finalDiagnosis, final_diagnosis_description: finalDesc })
        }).then(async r=>{
            const text = await r.text();
            const ct = r.headers.get('content-type') || '';
            if(!r.ok){
                throw new Error(`Failed to save final diagnosis (${r.status})`);
            }
            if(ct.includes('application/json')){
                const j = JSON.parse(text);
                if(j.success){
                    if (typeof doctorSuccess === 'function') {
                        doctorSuccess('Final Diagnosis Saved', 'Final diagnosis has been successfully saved.');
                    } else {
                        alert('Final diagnosis saved successfully!');
                    }
                    modal.style.display = 'none';
                    // Refresh the current patient details to show the updated final diagnosis
                    refreshCurrentPatientDetails();
                    return;
                }
            }
            if (typeof doctorSuccess === 'function') {
                doctorSuccess('Final Diagnosis Saved', 'Final diagnosis has been successfully saved.');
            } else {
                alert('Final diagnosis saved successfully!');
            }
            modal.style.display = 'none';
            refreshCurrentPatientDetails();
        }).catch(e=>{ 
            console.error(e); 
            if (typeof doctorError === 'function') {
                doctorError('Save Failed', e.message);
            } else {
                alert('Failed to save final diagnosis: ' + e.message);
            }
        }).finally(() => {
            // Re-enable button
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Final Diagnosis';
        });
    });

    // Function to refresh current patient details
    function refreshCurrentPatientDetails() {
        const patientNo = document.getElementById('md-patient_no').textContent;
        if (patientNo && patientNo !== '-') {
            const row = Array.from(document.querySelectorAll('.patient-row')).find(r => 
                r.querySelector('.col-no')?.textContent.trim() === patientNo.toString()
            );
            if (row) { 
                row.querySelector('.js-open-patient').click(); 
            }
        }
    }

    // Expose helper to open modal with admission id
    window.openFinalizeModal = function(admissionId) {
        document.getElementById('finalize_admission_id').value = admissionId;
        document.getElementById('final_diagnosis').value = '';
        document.getElementById('final_diagnosis_description').value = '';
        modal.style.display = 'flex';
        // Don't auto-focus the input field to prevent automatic suggestions
    }

    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
});
</script>
<?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\doctor\modals\finalize_diagnosis_modal.blade.php ENDPATH**/ ?>