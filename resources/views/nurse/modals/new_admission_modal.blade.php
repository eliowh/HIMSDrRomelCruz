<!-- New Admission Modal -->
<div class="modal" id="newAdmissionModal">
    <div class="modal-content">
        <span class="close modal-close">&times;</span>
        <h3>New Admission</h3>
        <form id="newAdmissionForm">
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
            <input type="hidden" id="admission_patient_id" name="patient_id">
            
            <!-- Patient Info Display -->
            <div class="patient-info-display" id="admissionPatientInfo">
                <!-- Patient info will be populated here -->
            </div>
            
            <div class="two-column-form">
                <div class="form-group">
                    <label for="new_admission_room_no">Room</label>
                    <div class="input-validation-container">
                        <div class="suggestion-container">
                            <input id="new_admission_room_no" name="room_no" placeholder="Type room name or price" autocomplete="off" required />
                            <div id="new_admission_room_suggestions" class="suggestion-list"></div>
                        </div>
                        <div id="new_admission_room_validation_error" class="validation-error"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="admission_admission_type">Admission Type</label>
                    <select id="admission_admission_type" name="admission_type" required>
                        <option value="" disabled selected>-- Select Service --</option>
                        <option value="Inpatient">Inpatient</option>
                        <option value="Outpatient">Outpatient</option>
                        <option value="Emergency">Emergency</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="new_admission_doctor_name">Doctor</label>
                    <div class="input-validation-container">
                        <div class="suggestion-container">
                            <input id="new_admission_doctor_input" type="text" autocomplete="off" placeholder="Type doctor name or select" required />
                            <div id="new_admission_doctor_suggestions" class="suggestion-list"></div>
                        </div>
                        <div id="new_admission_doctor_validation_error" class="validation-error"></div>
                        <input type="hidden" id="new_admission_doctor_name" name="doctor_name" />
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="admission_doctor_type">Specialization</label>
                    <select id="admission_doctor_type" name="doctor_type">
                        <option value="" disabled selected>-- Select --</option>
                        <option value="PHYSICIAN">PHYSICIAN</option>
                        <option value="SURGEON / ROD">SURGEON / ROD</option>
                        <option value="PHYSICIAN / ROD">PHYSICIAN / ROD</option>
                        <option value="ANESTHESIOLOGIST">ANESTHESIOLOGIST</option>
                        <option value="GASTROENTEROLOGIST">GASTROENTEROLOGIST</option>
                        <option value="NEUROLOGIST">NEUROLOGIST</option>
                        <option value="ONCOLOGIST">ONCOLOGIST</option>
                        <option value="OPHTHALMOLOGIST">OPHTHALMOLOGIST</option>
                        <option value="ORTHOPAEDIC">ORTHOPAEDIC</option>
                        <option value="OB-GYN / SURGEON">OB-GYN / SURGEON</option>
                        <option value="PEDIATRICIAN">PEDIATRICIAN</option>
                        <option value="INFECTIOUS MED.">INFECTIOUS MED.</option>
                        <option value="UROLOGIST">UROLOGIST</option>
                        <option value="ENT">ENT</option>
                        <option value="NEPHROLOGIST">NEPHROLOGIST</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="new_admission_admission_diagnosis">Admission Diagnosis (ICD-10)</label>
                    <div class="input-validation-container">
                        <div class="suggestion-container">
                            <input id="new_admission_admission_diagnosis" name="admission_diagnosis" placeholder="Type ICD-10 code or description" autocomplete="off" />
                            <div id="new_admission_icd10_suggestions" class="suggestion-list"></div>
                        </div>
                        <div id="new_admission_icd_validation_error" class="validation-error"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="new_admission_admission_diagnosis_description">Diagnosis Description</label>
                    <input id="new_admission_admission_diagnosis_description" name="admission_diagnosis_description" type="text" placeholder="Description will appear here" readonly />
                </div>
                
                <div class="form-actions full-width">
                    <button type="button" class="btn cancel-btn modal-close">Cancel</button>
                    <button id="saveAdmissionBtn" type="button" class="btn submit-btn">Create Admission</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.new-admission-section {
    margin: 12px 0;
    text-align: center;
}

.new-admission-btn {
    background: #28a745;
    border: none;
    color: white;
    padding: 8px 16px;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    transition: background 0.2s;
}

.new-admission-btn:hover {
    background: #218838;
}

.new-admission-btn i {
    margin-right: 6px;
}

.patient-info-display {
    background: #f0f8ff;
    padding: 12px;
    border-radius: 4px;
    border: 1px solid #007bff;
    margin-bottom: 20px;
    font-size: 14px;
}

.patient-info-display .info-item {
    display: inline-block;
    margin-right: 20px;
    margin-bottom: 6px;
}

.patient-info-display .info-label {
    font-weight: bold;
    color: #0056b3;
}

/* Room and ICD suggestions styling */
.room-suggestion, .icd-suggestion {
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
    transition: background-color 0.2s;
}
.room-suggestion:hover, .icd-suggestion:hover,
.room-suggestion.active, .icd-suggestion.active {
    background-color: #f0f8ff;
}
.room-suggestion .code, .icd-suggestion .code {
    font-weight: bold;
    color: #2c5f2d;
}
.room-suggestion .desc, .icd-suggestion .desc {
    color: #666;
    font-size: 0.9em;
    margin-left: 10px;
}

/* ICD suggestions styling */
.icd-suggestion {
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
    transition: background-color 0.2s;
}
.icd-suggestion:hover,
.icd-suggestion.active {
    background-color: #f0f8ff;
}
.icd-suggestion .code {
    font-weight: bold;
    color: #0066cc;
}
.icd-suggestion .desc {
    color: #666;
    font-size: 0.9em;
    margin-left: 10px;
}

/* Doctor suggestions styling */
.doctor-suggestion {
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
    transition: background-color 0.2s;
}
.doctor-suggestion:hover,
.doctor-suggestion.active {
    background-color: #f0f8ff;
}
.doctor-suggestion .name {
    font-weight: bold;
    color: #9c27b0;
}
.doctor-suggestion .type {
    color: #666;
    font-size: 0.9em;
    margin-left: 10px;
    font-style: italic;
}

/* Suggestion container styling */
.suggestion-container {
    position: relative;
}

.suggestion-list {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    z-index: 9999;
    max-height: 200px;
    overflow-y: auto;
    display: none; /* Hidden by default */
}

/* Ensure suggestion dropdowns don't affect layout */
.icd-suggestions,
#edit_room_suggestions,
#edit_icd10_suggestions,
#edit-doctor-suggestions,
#new_admission_room_suggestions,
#new_admission_icd10_suggestions,
#new_admission_doctor_suggestions {
    position: absolute !important;
    z-index: 9999 !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    border-radius: 4px !important;
    contain: layout style !important;
}

/* Prevent suggestion containers from triggering layout recalculations */
.icd-suggestions *,
#edit_room_suggestions *,
#edit_icd10_suggestions *,
#edit-doctor-suggestions *,
#new_admission_room_suggestions *,
#new_admission_icd10_suggestions *,
#new_admission_doctor_suggestions * {
    contain: layout style !important;
}

/* Validation error styling */
.validation-error {
    color: #dc3545;
    font-size: 12px;
    margin-top: 4px;
    display: none;
}

.validation-error.visible {
    display: block;
}

.input-validation-container {
    position: relative;
}

.modal-content{
    max-width: 40%;
}
</style>

<!-- Copy suggestion scripts from edit modal -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Room autocomplete for new admission (adapted from edit modal)
    (function(){
        const input = document.getElementById('new_admission_room_no');
        const container = document.getElementById('new_admission_room_suggestions');
        const errorDiv = document.getElementById('new_admission_room_validation_error');
        if (!input || !container) return;
        
        let timer = null;
        let activeIndex = -1;
        let lastItems = [];
        let newAdmissionRoomIsValid = false;

        function escapeHtml(s){ if(!s) return ''; return String(s).replace(/[&<>"']/g, (m)=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
        
        function clearSuggestions(){ 
            container.innerHTML=''; 
            container.style.display='none'; 
            activeIndex=-1; 
            lastItems=[];
        }
        
        function renderSuggestions(items){ 
            lastItems = items || []; 
            if(!lastItems.length){ clearSuggestions(); return; } 
            container.innerHTML = ''; 
            
            const itemsToShow = lastItems.slice(0, 10);

            itemsToShow.forEach((it,idx)=>{ 
                const el=document.createElement('div'); 
                el.className='room-suggestion'; 
                el.dataset.index=idx; 
                el.innerHTML = '<span class="code">'+escapeHtml(it.name)+'</span>' + (it.price ? ' <span class="desc">₱'+escapeHtml(it.price)+'</span>' : '');
                el.addEventListener('click',()=>selectItem(idx, itemsToShow)); 
                container.appendChild(el); 
            }); 
            container.style.display='block'; 
            activeIndex=-1; 
        }
        
        function selectItem(idx, items = lastItems){ 
            const item=items[idx]; 
            if(!item) return; 
            input.value = item.name || ''; 
            newAdmissionRoomIsValid = true;
            hideError();
            clearSuggestions(); 
        }

        function showError(message) {
            if (errorDiv) {
                errorDiv.textContent = message;
                errorDiv.classList.add('visible');
            }
        }
        
        function hideError() {
            if (errorDiv) {
                errorDiv.classList.remove('visible');
            }
        }

        input.addEventListener('input', ()=>{
            newAdmissionRoomIsValid = false;
            clearTimeout(timer);
            const val = input.value.trim();
            if(!val){ clearSuggestions(); hideError(); return; }
            
            timer = setTimeout(()=>{
                fetch('/rooms/search?q='+encodeURIComponent(val))
                    .then(async r=>{
                        const ct=(r.headers.get('content-type')||'').toLowerCase();
                        const text=await r.text();
                        if(ct.includes('application/json')){
                            try{ 
                                const rooms = JSON.parse(text);
                                renderSuggestions(rooms);
                            }catch(e){ console.error('Room parse error',e); }
                        }
                    })
                    .catch(e=>console.error('Room fetch error',e));
            }, 300);
        });

        input.addEventListener('blur', ()=>{
            setTimeout(()=>{
                if(input.value.trim() && !newAdmissionRoomIsValid){
                    // Validate on blur
                    fetch('/rooms/validate', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ name: input.value.trim() })
                    })
                    .then(async r => {
                        const result = await r.json();
                        newAdmissionRoomIsValid = result.valid || false;
                        if (!newAdmissionRoomIsValid) {
                            showError('Please select a valid room from the list.');
                        } else {
                            hideError();
                        }
                    })
                    .catch(e => {
                        console.error('Room validation error', e);
                        newAdmissionRoomIsValid = false;
                        showError('Unable to validate room. Please try again.');
                    });
                }
                clearSuggestions();
            }, 200);
        });
        
        document.addEventListener('click',(e)=>{ 
            if(!container.contains(e.target) && e.target !== input) {
                clearSuggestions();
            }
        });

        // Expose validation function
        window.validateNewAdmissionRoom = () => newAdmissionRoomIsValid;
    })();

    // ICD-10 autocomplete for new admission (adapted from edit modal)
    (function(){
        const input = document.getElementById('new_admission_admission_diagnosis');
        const descInput = document.getElementById('new_admission_admission_diagnosis_description');
        const container = document.getElementById('new_admission_icd10_suggestions');
        const errorDiv = document.getElementById('new_admission_icd_validation_error');
        if (!input || !container) return;
        
        let timer = null;
        let activeIndex = -1;
        let lastItems = [];
        let newAdmissionIcdIsValid = true; // ICD is optional

        function escapeHtml(s){ if(!s) return ''; return String(s).replace(/[&<>"']/g, (m)=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
        
        function clearSuggestions(){ 
            container.innerHTML=''; 
            container.style.display='none'; 
            activeIndex=-1; 
            lastItems=[];
        }
        
        function renderSuggestions(items){ 
            lastItems = items || []; 
            if(!lastItems.length){ clearSuggestions(); return; } 
            container.innerHTML = ''; 
            
            const itemsToShow = lastItems.slice(0, 10);

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
        
        function selectItem(idx, items = lastItems){ 
            const item=items[idx]; 
            if(!item) return; 
            input.value = item.code || ''; 
            if(descInput) descInput.value = item.description || '';
            newAdmissionIcdIsValid = true;
            hideError();
            clearSuggestions(); 
        }

        function showError(message) {
            if (errorDiv) {
                errorDiv.textContent = message;
                errorDiv.classList.add('visible');
            }
        }
        
        function hideError() {
            if (errorDiv) {
                errorDiv.classList.remove('visible');
            }
        }

        input.addEventListener('input', ()=>{
            clearTimeout(timer);
            const val = input.value.trim();
            if(!val){ 
                clearSuggestions(); 
                if(descInput) descInput.value = '';
                newAdmissionIcdIsValid = true; // Empty is valid
                hideError();
                return; 
            }
            
            timer = setTimeout(()=>{
                fetch('/icd10/search?q='+encodeURIComponent(val))
                    .then(async r=>{
                        const ct=(r.headers.get('content-type')||'').toLowerCase();
                        const text=await r.text();
                        if(ct.includes('application/json')){
                            try{ 
                                const codes = JSON.parse(text);
                                renderSuggestions(codes);
                            }catch(e){ console.error('ICD-10 parse error',e); }
                        }
                    })
                    .catch(e=>console.error('ICD-10 fetch error',e));
            }, 300);
        });

        input.addEventListener('blur', ()=>{
            setTimeout(()=>{
                if(input.value.trim()){
                    // Validate on blur
                    fetch('/icd10/validate', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ code: input.value.trim() })
                    })
                    .then(async r => {
                        const result = await r.json();
                        newAdmissionIcdIsValid = result.valid || false;
                        if (!newAdmissionIcdIsValid) {
                            showError('Please select a valid ICD-10 code from the list.');
                        } else {
                            hideError();
                        }
                    })
                    .catch(e => {
                        console.error('ICD-10 validation error', e);
                        newAdmissionIcdIsValid = false;
                        showError('Unable to validate ICD-10 code. Please try again.');
                    });
                } else {
                    newAdmissionIcdIsValid = true; // Empty is valid
                }
                clearSuggestions();
            }, 200);
        });
        
        document.addEventListener('click',(e)=>{ 
            if(!container.contains(e.target) && e.target !== input) {
                clearSuggestions();
            }
        });

        // Expose validation function
        window.validateNewAdmissionIcd = () => newAdmissionIcdIsValid;
    })();

    // Doctor autocomplete for new admission (adapted from edit modal)
    (function(){
        const visibleInput = document.getElementById('new_admission_doctor_input');
        const hiddenInput = document.getElementById('new_admission_doctor_name');
        const container = document.getElementById('new_admission_doctor_suggestions');
        const errorDiv = document.getElementById('new_admission_doctor_validation_error');
        const typeSelect = document.getElementById('admission_doctor_type');
        if (!visibleInput || !hiddenInput || !container) return;
        
        let timer = null;
        let activeIndex = -1;
        let lastItems = [];

        function escapeHtml(s){ if(!s) return ''; return String(s).replace(/[&<>"']/g, (m)=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
        
        function clearSuggestions(){ 
            container.innerHTML=''; 
            container.style.display='none'; 
            activeIndex=-1; 
            lastItems=[];
        }
        
        function renderSuggestions(items){ 
            lastItems = items || []; 
            if(!lastItems.length){ clearSuggestions(); return; } 
            container.innerHTML = ''; 

            // If a specialization is selected, filter client-side as well
            const selectedType = typeSelect ? (typeSelect.value || '').toString().trim() : '';
            let filtered = lastItems;
            if (selectedType) {
                filtered = lastItems.filter(d => (d.type || '').toString().trim() === selectedType);
            }
            const itemsToShow = filtered.slice(0, 10);

            itemsToShow.forEach((it,idx)=>{ 
                const el=document.createElement('div'); 
                el.className='doctor-suggestion'; 
                el.dataset.index=idx; 
                el.innerHTML = '<span class="name">'+escapeHtml(it.name)+'</span>' + (it.type ? ' <span class="type">'+escapeHtml(it.type)+'</span>' : '');
                el.addEventListener('click',()=>selectItem(idx, itemsToShow)); 
                container.appendChild(el); 
            }); 
            container.style.display='block'; 
            activeIndex=-1; 
        }
        
        function selectItem(idx, items = lastItems){ 
            const item=items[idx]; 
            if(!item) return; 
            visibleInput.value = item.name || ''; 
            hiddenInput.value = item.name || '';
            
            // Auto-populate doctor specialization if available
            const doctorTypeSelect = document.getElementById('admission_doctor_type');
            if (doctorTypeSelect && item.type) {
                for (let i = 0; i < doctorTypeSelect.options.length; i++) {
                    if (doctorTypeSelect.options[i].value === item.type) {
                        doctorTypeSelect.selectedIndex = i;
                        break;
                    }
                }
            }
            
            clearSuggestions(); 
        }

        visibleInput.addEventListener('input', ()=>{
            hiddenInput.value = '';
            
            // Clear doctor specialization when input is cleared
            const doctorTypeSelect = document.getElementById('admission_doctor_type');
            if (doctorTypeSelect && !visibleInput.value.trim()) {
                doctorTypeSelect.selectedIndex = 0; // Reset to default "-- Select --"
            }
            
            clearTimeout(timer);
            const val = visibleInput.value.trim();
            if(!val){ clearSuggestions(); return; }
            
            timer = setTimeout(()=>{
                // Include selected specialization in the search if present
                const typeParam = (typeSelect && typeSelect.value) ? '&type=' + encodeURIComponent(typeSelect.value) : '';
                fetch('/doctors/search?q='+encodeURIComponent(val) + typeParam)
                    .then(async r=>{
                        const ct=(r.headers.get('content-type')||'').toLowerCase();
                        const text=await r.text();
                        if(ct.includes('application/json')){
                            try{ 
                                const doctors = JSON.parse(text);
                                renderSuggestions(doctors);
                            }catch(e){ console.error('Doctor parse error',e); }
                        }
                    })
                    .catch(e=>console.error('Doctor fetch error',e));
            }, 300);
        });

        // Show suggestions on focus as well (respect selected specialization)
        visibleInput.addEventListener('focus', ()=>{
            window.isModalOpen = true;
            window.isDropdownOpen = true;
            const q = visibleInput.value.trim();
            const typeParam = (typeSelect && typeSelect.value) ? '&type=' + encodeURIComponent(typeSelect.value) : '';
            // If input is empty, fetch a broad list (server may return a master list)
            fetch('/doctors/search?q='+encodeURIComponent(q) + typeParam)
                .then(async r=>{
                    const ct=(r.headers.get('content-type')||'').toLowerCase();
                    const text=await r.text();
                    if(ct.includes('application/json')){
                        try{
                            const doctors = JSON.parse(text);
                            renderSuggestions(doctors);
                        }catch(e){ console.error('Doctor parse error', e); }
                    }
                })
                .catch(e=>console.error('Doctor fetch error', e));
        });

        visibleInput.addEventListener('blur', ()=>{
            setTimeout(()=>{
                hiddenInput.value = visibleInput.value;
                clearSuggestions();
            }, 200);
        });
        
        document.addEventListener('click',(e)=>{ 
            if(!container.contains(e.target) && e.target !== visibleInput) {
                clearSuggestions();
            }
        });
    })();

    // Initialize new admission suggestions function
    window.initializeAdmissionSuggestions = function() {
        // Suggestions are already initialized above
        console.log('New admission suggestions initialized');
    };
});
</script>