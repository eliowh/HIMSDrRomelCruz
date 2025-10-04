<!-- Edit Patient Modal -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <span class="close modal-close">&times;</span>
        <h3>Edit Patient</h3>
        <form id="editPatientForm">
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                <div class="two-column-form">
                    <div class="form-group">
                        <label for="edit_first_name">First Name</label>
                        <input id="edit_first_name" name="first_name" placeholder="First name" required />
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_last_name">Last Name</label>
                        <input id="edit_last_name" name="last_name" placeholder="Last name" required />
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_middle_name">Middle Name</label>
                        <input id="edit_middle_name" name="middle_name" placeholder="Middle name" />
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_date_of_birth">Date of Birth</label>
                        <input id="edit_date_of_birth" name="date_of_birth" type="date" required />
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_province">Province</label>
                        <input id="edit_province" name="province" placeholder="Enter province" />
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_city">City</label>
                        <input id="edit_city" name="city" placeholder="Enter city" />
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_barangay">Barangay</label>
                        <input id="edit_barangay" name="barangay" placeholder="Enter barangay" />
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_nationality">Nationality</label>
                        <input id="edit_nationality" name="nationality" placeholder="Enter nationality" />
                    </div>

                    <div class="form-divider full-width"></div>

                    <div class="form-group">
                        <label for="edit_room_no">Room</label>
                        <div class="input-validation-container">
                            <div class="suggestion-container">
                                <input id="edit_room_no" name="room_no" placeholder="Type room name or price" autocomplete="off" />
                                <div id="edit_room_suggestions" class="suggestion-list"></div>
                            </div>
                            <div id="edit_room_validation_error" class="validation-error"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_admission_diagnosis">Admission Diagnosis (ICD-10)</label>
                        <div class="input-validation-container">
                            <div class="suggestion-container">
                                <input id="edit_admission_diagnosis" name="admission_diagnosis" type="text" autocomplete="off" placeholder="Type ICD-10 code or disease name" />
                                <div id="edit_icd10_suggestions" class="suggestion-list"></div>
                            </div>
                            <div id="edit_icd10_validation_error" class="validation-error"></div>
                        </div>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="edit_admission_diagnosis_description">Admission Diagnosis Description</label>
                        <input id="edit_admission_diagnosis_description" name="admission_diagnosis_description" type="text" placeholder="Description will appear here" readonly />
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_admission_type">Admission Type</label>
                        <select id="edit_admission_type" name="admission_type" required>
                            <option value="" disabled selected>-- Select --</option>
                            <option value="Emergency">Emergency</option>
                            <option value="Elective">Elective</option>
                            <option value="Transfer">Transfer</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_service">Service</label>
                        <select id="edit_service" name="service" required>
                            <option value="" disabled selected>-- Select Service --</option>
                            <option value="Inpatient">Inpatient</option>
                            <option value="Outpatient">Outpatient</option>
                            <option value="Surgery">Surgery</option>
                            <option value="Emergency">Emergency</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_doctor_name">Doctor</label>
                        <input id="edit_doctor_name" name="doctor_name" placeholder="Enter doctor's name" />
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_doctor_type">Doctor Type</label>
                        <select id="edit_doctor_type" name="doctor_type" required>
                            <option value="" disabled selected>-- Select --</option>
                            <option value="Consultant">Consultant</option>
                            <option value="Resident">Resident</option>
                            <option value="Intern">Intern</option>
                        </select>
                    </div>
                    
                    <div class="form-actions full-width">
                        <button type="button" class="btn cancel-btn modal-close">Cancel</button>
                        <button id="savePatientBtn" type="button" class="btn submit-btn">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add ICD-10 and Room suggestion scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize validation state variables
    let editIcdIsValid = false;
    let editRoomIsValid = false;
    
    // Dynamic height adjustment for modal
    function adjustModalHeight() {
        const modal = document.getElementById('editModal');
        const modalContent = modal.querySelector('.modal-content');
        const modalForm = modalContent.querySelector('form');
        const modalHeader = modalContent.querySelector('h3');
        
        // Reset any previously set heights
        modalForm.style.maxHeight = '';
        
        // Set a slight delay to ensure DOM is fully rendered
        setTimeout(() => {
            const viewportHeight = window.innerHeight;
            const headerHeight = modalHeader.offsetHeight + 40; // Add padding
            const maxFormHeight = viewportHeight * 0.9 - headerHeight; // 90% of viewport minus header
            
            modalForm.style.maxHeight = maxFormHeight + 'px';
        }, 50);
    }
    
    // Adjust modal height when shown
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.attributeName === 'class') {
                const modal = document.getElementById('editModal');
                if (modal.classList.contains('open') || modal.classList.contains('show')) {
                    adjustModalHeight();
                }
            }
        });
    });
    
    const modal = document.getElementById('editModal');
    observer.observe(modal, { attributes: true });
    
    // Adjust on window resize
    window.addEventListener('resize', () => {
        if (modal.classList.contains('open') || modal.classList.contains('show')) {
            adjustModalHeight();
        }
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.classList.remove('open');
            modal.classList.remove('show');
        }
    });
    
    // Enhanced ICD-10 autocomplete with validation
    (function(){
        const input = document.getElementById('edit_admission_diagnosis');
        const descField = document.getElementById('edit_admission_diagnosis_description');
        const container = document.getElementById('edit_icd10_suggestions');
        const errorDiv = document.getElementById('edit_icd10_validation_error');
        if (!input || !container) return;
        
        let timer = null; 
        let activeIndex = -1; 
        let lastItems = [];
        
        function clearSuggestions(){ 
            container.innerHTML=''; 
            container.style.display='none'; 
            activeIndex=-1; 
            lastItems=[];
            // Reset dropdown state when clearing suggestions
            window.isDropdownOpen = false;
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
            editIcdIsValid = true;
            hideError();
            clearSuggestions(); 
            window.isModalOpen = false;
            window.isDropdownOpen = false;
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
                errorDiv.style.display = 'none';
                errorDiv.classList.remove('visible');
            }
        }
        
        function validateIcdInput() {
            const currentValue = input.value.trim();
            if (!currentValue) {
                editIcdIsValid = true; // Allow empty
                hideError();
                return;
            }
            
            // Validate against full database via server
            fetch('{{ route("icd10.validate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ code: currentValue })
            })
            .then(async r => {
                const result = await r.json();
                editIcdIsValid = result.valid || false;
                if (!editIcdIsValid) {
                    showError('Please select a valid ICD-10 code from the list.');
                } else {
                    hideError();
                }
            })
            .catch(e => {
                console.error('ICD validation error', e);
                editIcdIsValid = false;
                showError('Unable to validate ICD-10 code. Please try again.');
            });
        }
        
        input.addEventListener('input', ()=>{
            editIcdIsValid = false;
            if(descField) descField.value = '';
            clearTimeout(timer);
            const val = input.value.trim();
            if(!val){ 
                clearSuggestions(); 
                hideError();
                return; 
            }
            
            timer = setTimeout(()=>{
                fetch('{{ route("icd10.search") }}?q='+encodeURIComponent(val))
                    .then(async r=>{
                        const ct=(r.headers.get('content-type')||'').toLowerCase();
                        const text=await r.text();
                        if(ct.includes('application/json')){
                            try{ 
                                const codes = JSON.parse(text);
                                renderSuggestions(codes);
                            }catch(e){ console.error('ICD parse error',e); }
                        }
                    })
                    .catch(e=>console.error('ICD fetch error',e));
            }, 300);
        });
        
        input.addEventListener('focus', () => {
            window.isModalOpen = true;
            window.isDropdownOpen = true;
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
                validateIcdInput();
                clearSuggestions();
                window.isModalOpen = false;
                window.isDropdownOpen = false;
            }, 200);
        });
        
        document.addEventListener('click',(e)=>{ 
            if(!container.contains(e.target) && e.target !== input) {
                clearSuggestions();
                // Reset dropdown state when clicking outside
                window.isDropdownOpen = false;
                window.isModalOpen = false;
            }
        });
    })();
    
    // Enhanced room autocomplete with validation
    (function(){
        const input = document.getElementById('edit_room_no');
        const container = document.getElementById('edit_room_suggestions');
        const errorDiv = document.getElementById('edit_room_validation_error');
        if (!input || !container) return;
        
        let timer = null;
        let activeIndex = -1;
        let lastItems = [];

        function escapeHtml(s){ if(!s) return ''; return String(s).replace(/[&<>"']/g, (m)=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
        
        function clearSuggestions(){ 
            container.innerHTML=''; 
            container.style.display='none'; 
            activeIndex=-1; 
            lastItems=[];
            // Reset dropdown state when clearing suggestions
            window.isDropdownOpen = false;
        }
        
        function renderSuggestions(items, showAll = false){ 
            lastItems = items || []; 
            if(!lastItems.length){ clearSuggestions(); return; } 
            container.innerHTML = ''; 
            
            const itemsToShow = showAll ? lastItems : lastItems.slice(0, 10);

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
            editRoomIsValid = true;
            hideError();
            clearSuggestions(); 
            window.isModalOpen = false;
            window.isDropdownOpen = false;
        }
        
        function highlightActive(){ 
            const nodes = container.querySelectorAll('.room-suggestion'); 
            nodes.forEach((n,i)=> n.classList.toggle('active', i===activeIndex)); 
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

        function validateRoomInput() {
            const currentValue = input.value.trim();
            if (!currentValue) {
                editRoomIsValid = true; // Allow empty
                hideError();
                return;
            }
            
            // Validate against full database via server
            fetch('{{ route("rooms.validate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ name: currentValue })
            })
            .then(async r => {
                const result = await r.json();
                editRoomIsValid = result.valid || false;
                if (!editRoomIsValid) {
                    showError('Please select a valid room from the list.');
                } else {
                    hideError();
                }
            })
            .catch(e => {
                console.error('Room validation error', e);
                editRoomIsValid = false;
                showError('Unable to validate room. Please try again.');
            });
        }

        input.addEventListener('input', ()=>{
            editRoomIsValid = false;
            clearTimeout(timer);
            const val = input.value.trim();
            if(!val){ clearSuggestions(); hideError(); return; }
            
            timer = setTimeout(()=>{
                fetch('{{ route("rooms.search") }}?q='+encodeURIComponent(val))
                    .then(async r=>{
                        const ct=(r.headers.get('content-type')||'').toLowerCase();
                        const text=await r.text();
                        if(ct.includes('application/json')){
                            try{ 
                                const rooms = JSON.parse(text);
                                renderSuggestions(rooms); // Show filtered results
                            }catch(e){ console.error('Room parse error',e); }
                        }
                    })
                    .catch(e=>console.error('Room fetch error',e));
            }, 300);
        });

        input.addEventListener('focus', () => {
            window.isModalOpen = true;
            window.isDropdownOpen = true;
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
                validateRoomInput();
                clearSuggestions();
                window.isModalOpen = false;
                window.isDropdownOpen = false;
            }, 200);
        });

        document.addEventListener('click',(e)=>{ 
            if(!container.contains(e.target) && e.target !== input) {
                clearSuggestions();
                // Reset dropdown state when clicking outside
                window.isDropdownOpen = false;
                window.isModalOpen = false;
            }
        });
    })();
});
</script>

<style>
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

/* Ensure suggestion dropdowns don't affect layout */
.icd-suggestions,
#edit_room_suggestions,
#edit_icd10_suggestions {
    position: absolute !important;
    z-index: 9999 !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    border-radius: 4px !important;
    contain: layout style !important;
}

/* Prevent suggestion containers from triggering layout recalculations */
.icd-suggestions *,
#edit_room_suggestions *,
#edit_icd10_suggestions * {
    contain: layout style !important;
}
</style>