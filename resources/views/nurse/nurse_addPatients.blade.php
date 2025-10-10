 @extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/nursecss/nurse_addPatients.css') }}">
<link rel="stylesheet" href="{{ asset('css/nursecss/nurse_addPatients_fixes.css') }}">
<link rel="stylesheet" href="{{ asset('css/nursecss/two_column_form.css') }}">
<link rel="stylesheet" href="{{ asset('css/nursecss/suggestion_dropdowns.css') }}">
<link rel="stylesheet" href="{{ asset('css/pharmacycss/pharmacy.css') }}">
<div class="nurse-card">
    <h3>Add Patient</h3>

    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                nurseSuccess('Patient Added', '{{ session('success') }}');
            });
        </script>
    @endif

    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const errors = @json($errors->all());
                let errorMessage = errors.join('\n');
                nurseError('Validation Error', errorMessage);
            });
        </script>
    @endif

    <form action="/nurse/addPatients" method="POST">
        @csrf

        <div class="two-column-form">
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input id="first_name" type="text" name="first_name" placeholder="Enter first name" required value="{{ old('first_name') }}">
            </div>

            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input id="last_name" type="text" name="last_name" placeholder="Enter last name" required value="{{ old('last_name') }}">
            </div>

            <div class="form-group">
                <label for="middle_name">Middle Name</label>
                <input id="middle_name" type="text" name="middle_name" placeholder="Enter middle name" value="{{ old('middle_name') }}">
            </div>

            <div class="form-group">
                <label for="date_of_birth">Date of Birth</label>
                <input id="date_of_birth" type="date" name="date_of_birth" required value="{{ old('date_of_birth') }}">
            </div>

            <div class="form-group">
                <label for="province">Province</label>
                <input id="province" type="text" name="province" placeholder="Enter province" value="{{ old('province') }}">
            </div>

            <div class="form-group">
                <label for="city">City</label>
                <input id="city" type="text" name="city" placeholder="Enter city" value="{{ old('city') }}">
            </div>

            <div class="form-group">
                <label for="barangay">Barangay</label>
                <input id="barangay" type="text" name="barangay" placeholder="Enter barangay" required value="{{ old('barangay') }}">
            </div>

            <div class="form-group">
                <label for="nationality">Nationality</label>
                <input id="nationality" type="text" name="nationality" placeholder="Enter nationality" required value="{{ old('nationality','Filipino') }}">
            </div>

            <!-- START: Admission fields -->
            <div class="form-divider"></div>

            <div class="form-group">
                <label for="room-input">Room</label>
                <div class="input-validation-container">
                    <div class="suggestion-container">
                        <input id="room-input" type="text" name="room_no" autocomplete="off" value="{{ old('room_no') }}" placeholder="Type room name or price">
                        <div id="room-suggestions" class="suggestion-list"></div>
                    </div>
                    <div id="room-validation-error" class="validation-error"></div>
                </div>
            </div>

            <div class="form-group">
                <label for="admission-diagnosis">Admission Diagnosis (ICD-10)</label>
                <div class="input-validation-container">
                    <div class="suggestion-container">
                        <input id="admission-diagnosis" name="admission_diagnosis" type="text" autocomplete="off" placeholder="Type ICD-10 code or disease name" value="{{ old('admission_diagnosis') }}">
                        <div id="icd10-suggestions" class="suggestion-list"></div>
                    </div>
                    <div id="icd10-validation-error" class="validation-error"></div>
                </div>
            </div>

            <div class="form-group full-width">
                <label for="admission-diagnosis-desc">Admission Diagnosis Description</label>
                <input id="admission-diagnosis-desc" name="admission_diagnosis_description" type="text" placeholder="Description will appear here" readonly value="{{ old('admission_diagnosis_description') }}">
            </div>




            <div class="form-group">
                <label for="doctor_name">Doctor</label>
                <div style="display:flex;gap:8px;align-items:center;">
                    <div class="input-validation-container" style="flex:1;">
                        <div class="suggestion-container">
                            <input id="doctor_name" name="doctor_name" type="text" autocomplete="off" placeholder="Type doctor name or select" value="{{ old('doctor_name') }}" style="flex:1;">
                            <div id="doctor-suggestions" class="suggestion-list"></div>
                        </div>
                        <div id="doctor-validation-error" class="validation-error"></div>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="doctor_type">Doctor Type</label>
                <select id="doctor_type" name="doctor_type" required>
                    <option value="" disabled {{ old('doctor_type')=='' ? 'selected':'' }}>-- Select --</option>
                    <option value="PHYSICIAN" {{ old('doctor_type')=='PHYSICIAN' ? 'selected':'' }}>PHYSICIAN</option>
                    <option value="SURGEON / ROD" {{ old('doctor_type')=='SURGEON / ROD' ? 'selected':'' }}>SURGEON / ROD</option>
                    <option value="PHYSICIAN / ROD" {{ old('doctor_type')=='PHYSICIAN / ROD' ? 'selected':'' }}>PHYSICIAN / ROD</option>
                    <option value="ANESTHESIOLOGIST" {{ old('doctor_type')=='ANESTHESIOLOGIST' ? 'selected':'' }}>ANESTHESIOLOGIST</option>
                    <option value="GASTROENTEROLOGIST" {{ old('doctor_type')=='GASTROENTEROLOGIST' ? 'selected':'' }}>GASTROENTEROLOGIST</option>
                    <option value="NEUROLOGIST" {{ old('doctor_type')=='NEUROLOGIST' ? 'selected':'' }}>NEUROLOGIST</option>
                    <option value="ONCOLOGIST" {{ old('doctor_type')=='ONCOLOGIST' ? 'selected':'' }}>ONCOLOGIST</option>
                    <option value="OPHTHALMOLOGIST" {{ old('doctor_type')=='OPHTHALMOLOGIST' ? 'selected':'' }}>OPHTHALMOLOGIST</option>
                    <option value="ORTHOPAEDIC" {{ old('doctor_type')=='ORTHOPAEDIC' ? 'selected':'' }}>ORTHOPAEDIC</option>
                    <option value="OB-GYN / SURGEON" {{ old('doctor_type')=='OB-GYN / SURGEON' ? 'selected':'' }}>OB-GYN / SURGEON</option>
                    <option value="PEDIATRICIAN" {{ old('doctor_type')=='PEDIATRICIAN' ? 'selected':'' }}>PEDIATRICIAN</option>
                    <option value="INFECTIOUS MED." {{ old('doctor_type')=='INFECTIOUS MED.' ? 'selected':'' }}>INFECTIOUS MED.</option>
                    <option value="UROLOGIST" {{ old('doctor_type')=='UROLOGIST' ? 'selected':'' }}>UROLOGIST</option>
                    <option value="ENT" {{ old('doctor_type')=='ENT' ? 'selected':'' }}>ENT</option>
                    <option value="NEPHROLOGIST" {{ old('doctor_type')=='NEPHROLOGIST' ? 'selected':'' }}>NEPHROLOGIST</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="admission_type">Admission Type</label>
                <select id="admission_type" name="admission_type" required>
                    <option value="" disabled {{ old('admission_type')=='' ? 'selected':'' }}>-- Select Service --</option>
                    <option value="Inpatient" {{ old('admission_type')=='Inpatient' ? 'selected':'' }}>Inpatient</option>
                    <option value="Outpatient" {{ old('admission_type')=='Outpatient' ? 'selected':'' }}>Outpatient</option>
                    <option value="Emergency" {{ old('admission_type')=='Emergency' ? 'selected':'' }}>Emergency</option>
                </select>
            </div>
            <!-- END: Admission fields -->
            
            
            
                        <div class="form-actions full-width">
                <button type="submit" class="btn submit-btn">Admit</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize global state variables
    window.isDropdownOpen = false;
    window.isModalOpen = false;
    
    let icdIsValid = false;
    let roomIsValid = false;

    // Enhanced ICD-10 autocomplete
    (function(){
        const input = document.getElementById('admission-diagnosis');
        const descField = document.getElementById('admission-diagnosis-desc');
        const container = document.getElementById('icd10-suggestions');
        const errorDiv = document.getElementById('icd10-validation-error');
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
            icdIsValid = true;
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
                errorDiv.textContent = '';
                errorDiv.style.display = 'none';
                errorDiv.classList.remove('visible');
            }
        }

        function validateIcdInput() {
            const currentValue = input.value.trim();
            if (!currentValue) {
                icdIsValid = true; // Allow empty
                hideError();
                return;
            }

            // Validate against full database via server
            fetch('/icd10/validate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ code: currentValue })
            })
            .then(async r => {
                const result = await r.json();
                icdIsValid = result.valid || false;
                if (!icdIsValid) {
                    showError('Please select a valid ICD-10 code from the list.');
                } else {
                    hideError();
                }
            })
            .catch(e => {
                console.error('ICD validation error', e);
                // Fallback to local validation if server fails
                icdIsValid = allCodes.some(c => c.code === currentValue);
                if (!icdIsValid) {
                    showError('Unable to validate ICD-10 code. Please try again.');
                } else {
                    hideError();
                }
            });
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
                            window.allCodes = allCodes; // Make it globally accessible for validation fallback
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
            icdIsValid = false;
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
            window.isModalOpen = true;
            window.isDropdownOpen = true;
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
        
        // Additional cleanup for escape key and blur events
        function resetDropdownState() {
            window.isDropdownOpen = false;
            window.isModalOpen = false;
        }
        
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                resetDropdownState();
            }
        });
        
        loadAllCodes(false);
    })();

    // Enhanced room autocomplete
    (function(){
        const input = document.getElementById('room-input');
        const container = document.getElementById('room-suggestions');
        const errorDiv = document.getElementById('room-validation-error');
        if (!input || !container) return;
        
        let timer = null;
        let activeIndex = -1;
        let lastItems = [];
        let masterRoomList = []; // This will hold the definitive list of all rooms

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
            roomIsValid = true;
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
                roomIsValid = true; // Or false if required, true allows empty
                hideError();
                return;
            }
            
            // Validate against full database via server
            fetch('/rooms/validate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ name: currentValue })
            })
            .then(async r => {
                const result = await r.json();
                roomIsValid = result.valid || false;
                if (!roomIsValid) {
                    showError('Please select a valid room from the list.');
                } else {
                    hideError();
                }
            })
            .catch(e => {
                console.error('Room validation error', e);
                // Fallback to local validation if server fails
                roomIsValid = masterRoomList.some(r => r.name === currentValue);
                if (!roomIsValid) {
                    showError('Please select a valid room from the list.');
                } else {
                    hideError();
                }
            });
        }
        
        function loadAllRooms(showSuggestions = false) {
            // Avoid re-fetching if we already have the master list
            if (masterRoomList.length > 0) {
                if (showSuggestions) {
                    renderSuggestions(masterRoomList, true);
                }
                return;
            }
            fetch('/rooms/search?q=')
                .then(async r => {
                    const ct = (r.headers.get('content-type') || '').toLowerCase();
                    const text = await r.text();
                    if (ct.includes('application/json')) {
                        try {
                            masterRoomList = JSON.parse(text); // Store the definitive list
                            window.allRooms = masterRoomList; // Make it globally accessible for the final form validation
                            if (showSuggestions) {
                                renderSuggestions(masterRoomList, true);
                            }
                        } catch(e) { console.error('Room parse error', e); }
                    }
                })
                .catch(e => console.error('Room fetch error', e));
        }

        input.addEventListener('input', ()=>{
            roomIsValid = false;
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
            if (!input.value.trim()) {
                loadAllRooms(true);
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
        
        // Additional cleanup for escape key and blur events
        function resetDropdownState() {
            window.isDropdownOpen = false;
            window.isModalOpen = false;
        }
        
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                resetDropdownState();
            }
        });

        loadAllRooms(false);
    })();

    // Doctor autosuggest (remote) - mirrors ICD-10 behavior
    (function(){
        const input = document.getElementById('doctor_name');
        const container = document.getElementById('doctor-suggestions');
        const errorDiv = document.getElementById('doctor-validation-error');
        const typeSelect = document.getElementById('doctor_type');
        if (!input || !container) return;

        let timer = null;
        let activeIndex = -1;
        let lastItems = [];
        let masterDoctorList = []; // definitive list cached on first load

        function clearSuggestions(){ container.innerHTML=''; container.style.display='none'; activeIndex=-1; lastItems=[]; window.isDropdownOpen = false; }

        function renderSuggestions(items, showAll = false){
            lastItems = items || [];
            if(!lastItems.length){ clearSuggestions(); return; }
            container.innerHTML = '';

            const itemsToShow = showAll ? lastItems : lastItems.slice(0, 30);
            itemsToShow.forEach((it, idx)=>{
                const el = document.createElement('div');
                el.className = 'icd-suggestion';
                el.dataset.index = idx;
                el.innerHTML = '<span class="code">'+escapeHtml(it.name)+'</span>' + (it.type ? (' <span class="desc">'+escapeHtml(it.type)+'</span>') : '');
                el.addEventListener('click', ()=> selectItem(idx, itemsToShow));
                container.appendChild(el);
            });
            // If we are showing a truncated list and the master list exists, show a footer to view all
            if (!showAll && lastItems.length > itemsToShow.length) {
                const footer = document.createElement('div');
                footer.className = 'icd-suggestion footer-suggestion';
                footer.style.fontWeight = '600';
                footer.style.textAlign = 'center';
                footer.style.background = '#fff';
                footer.style.cursor = 'pointer';
                footer.textContent = 'Show all results';
                footer.addEventListener('click', () => renderSuggestions(masterDoctorList, true));
                container.appendChild(footer);
            }
            container.style.display = 'block';
            activeIndex = -1;
            window.isDropdownOpen = true;
        }

        function escapeHtml(s){ if(!s) return ''; return String(s).replace(/[&<>"]/g, (m)=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m])); }

        function selectItem(idx, items = lastItems){
            const item = items[idx]; if(!item) return;
            input.value = item.name || '';
            if (typeSelect && item.type) typeSelect.value = item.type;
            clearSuggestions();
        }

        function loadAllDoctors(showSuggestions = false){
            if (masterDoctorList.length > 0) {
                if (showSuggestions) renderSuggestions(masterDoctorList, true);
                return;
            }
            fetch('/doctors/search?q=')
                .then(async r => {
                    const ct = (r.headers.get('content-type') || '').toLowerCase();
                    const text = await r.text();
                    if (ct.includes('application/json')) {
                        try {
                            masterDoctorList = JSON.parse(text);
                            if (showSuggestions) renderSuggestions(masterDoctorList, true);
                        } catch(e) { console.error('Doctors parse error', e); }
                    }
                })
                .catch(e => console.error('Doctors fetch error', e));
        }

        function validateDoctorInput(){
            const currentValue = input.value.trim();
            if (!currentValue) { hideDoctorError(); return Promise.resolve(true); }

            return fetch('/doctors/validate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ name: currentValue })
            })
            .then(async r => {
                const result = await r.json();
                if (result.valid) {
                    if (typeSelect && result.type) typeSelect.value = result.type;
                    hideDoctorError();
                    return true;
                }
                // fallback to client-side master list
                const found = masterDoctorList.some(d => d.name === currentValue);
                if (found) { hideDoctorError(); return true; }
                showDoctorError('Please select a valid doctor from the list.');
                return false;
            })
            .catch(e => {
                console.error('Doctor validation error', e);
                const found = masterDoctorList.some(d => d.name === currentValue);
                if (found) { hideDoctorError(); return true; }
                showDoctorError('Unable to validate doctor. Please try again.');
                return false;
            });
        }

        function showDoctorError(msg){ if (errorDiv) { errorDiv.textContent = msg; errorDiv.classList.add('visible'); errorDiv.style.display='block'; } }
        function hideDoctorError(){ if (errorDiv) { errorDiv.textContent = ''; errorDiv.classList.remove('visible'); errorDiv.style.display='none'; } }

        input.addEventListener('input', ()=>{
            clearTimeout(timer);
            const q = input.value.trim();
            if(!q){ clearSuggestions(); return; }
            timer = setTimeout(()=>{
                fetch('/doctors/search?q='+encodeURIComponent(q))
                    .then(r => r.json())
                    .then(data => renderSuggestions(data || []))
                    .catch(e => console.error('Doctor fetch error', e));
            }, 250);
        });

        input.addEventListener('focus', ()=>{
            window.isModalOpen = true;
            window.isDropdownOpen = true;
            const q = input.value.trim();
            if (!q) {
                // show master list (cached) if available, otherwise fetch and show
                if (masterDoctorList.length > 0) renderSuggestions(masterDoctorList.slice(0,50), true);
                else loadAllDoctors(true);
            } else {
                // perform a quick filtered search to show relevant suggestions
                fetch('/doctors/search?q='+encodeURIComponent(q))
                    .then(r => r.json())
                    .then(data => renderSuggestions(data || []))
                    .catch(e => console.error('Doctor fetch error', e));
            }
        });

        input.addEventListener('keydown', (e)=>{
            const nodes = container.querySelectorAll('div');
            if(!nodes.length) return;
            if(e.key === 'ArrowDown'){ e.preventDefault(); activeIndex = (activeIndex+1) % nodes.length; nodes.forEach((n,i)=> n.classList.toggle('active', i===activeIndex)); }
            else if(e.key === 'ArrowUp'){ e.preventDefault(); activeIndex = activeIndex<=0 ? nodes.length-1 : activeIndex-1; nodes.forEach((n,i)=> n.classList.toggle('active', i===activeIndex)); }
            else if(e.key === 'Enter'){ e.preventDefault(); if(activeIndex>=0) selectItem(activeIndex); }
            else if(e.key === 'Escape'){ clearSuggestions(); }
        });

        input.addEventListener('blur', () => {
            setTimeout(() => {
                validateDoctorInput().then(()=>{
                    clearSuggestions();
                    window.isModalOpen = false;
                    window.isDropdownOpen = false;
                });
            }, 200);
        });

        // load master list on init for better UX
        loadAllDoctors(false);
    })();

    // Form validation before submission
    const form = document.querySelector('form[action*="/nurse/addPatients"]');
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault(); // Always prevent default to handle validation asynchronously
            
            const roomInput = document.getElementById('room-input');
            const icdInput = document.getElementById('admission-diagnosis');
            const roomValue = roomInput?.value.trim() || '';
            const icdValue = icdInput?.value.trim() || '';
            
            let validationErrors = [];
            let validationPromises = [];
            
            // Validate room if not empty
            if (roomValue) {
                const roomValidation = fetch('/rooms/validate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ name: roomValue })
                })
                .then(async r => {
                    const result = await r.json();
                    if (!result.valid) {
                        validationErrors.push('Please select a valid room from the list.');
                    }
                })
                .catch(e => {
                    console.error('Room validation error', e);
                    validationErrors.push('Unable to validate room. Please try again.');
                });
                
                validationPromises.push(roomValidation);
            }
            
            // Validate ICD if not empty
            if (icdValue) {
                const icdValidation = fetch('/icd10/validate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ code: icdValue })
                })
                .then(async r => {
                    const result = await r.json();
                    if (!result.valid) {
                        validationErrors.push('Please select a valid ICD-10 code from the list.');
                    }
                })
                .catch(e => {
                    console.error('ICD validation error', e);
                    validationErrors.push('Unable to validate ICD-10 code. Please try again.');
                });
                
                validationPromises.push(icdValidation);
            }
            
            // Wait for all validations to complete
            await Promise.all(validationPromises);
            
            // If there are validation errors, show them and stop submission
            if (validationErrors.length > 0) {
                const errorMessage = validationErrors.join(' ');
                if (window.showNotification) {
                    window.showNotification('error', 'Validation Error', errorMessage);
                } else {
                    alert('Validation Error: ' + errorMessage);
                }
                return false;
            }
            
            // If validation passes, submit the form
            form.submit();
        });
    }

        // doctorMap and legacy select removed; autosuggest will populate doctor_name and doctor_type via remote search

    // Auto-capitalize text inputs
    function initializeAutoCapitalization() {
        // Select all text input fields that should be auto-capitalized
        const fieldsToCapitalize = [
            'first_name',
            'last_name', 
            'middle_name',
            'province',
            'city',
            'barangay',
            'nationality',
            'doctor_name'
        ];

        fieldsToCapitalize.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                // Add event listener for input events
                field.addEventListener('input', function(e) {
                    const cursorPosition = e.target.selectionStart;
                    const originalValue = e.target.value;
                    
                    // Capitalize first letter of each word
                    const capitalizedValue = originalValue.replace(/\b\w/g, function(char) {
                        return char.toUpperCase();
                    });
                    
                    // Only update if the value changed to avoid cursor jumping
                    if (originalValue !== capitalizedValue) {
                        e.target.value = capitalizedValue;
                        
                        // Restore cursor position
                        const newCursorPosition = cursorPosition + (capitalizedValue.length - originalValue.length);
                        e.target.setSelectionRange(newCursorPosition, newCursorPosition);
                    }
                });

                // Also handle paste events
                field.addEventListener('paste', function(e) {
                    setTimeout(() => {
                        const originalValue = e.target.value;
                        const capitalizedValue = originalValue.replace(/\b\w/g, function(char) {
                            return char.toUpperCase();
                        });
                        e.target.value = capitalizedValue;
                    }, 10);
                });
            }
        });
    }

    // Initialize auto-capitalization after DOM is loaded
    initializeAutoCapitalization();
});
</script>
<style>
/* Dropdown buttons styling */
.dropdown-btn {
    background: #f8f9fa;
    border: 1px solid #ddd;
    border-left: none;
    padding: 8px 12px;
    cursor: pointer;
    user-select: none;
    transition: background-color 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
}
.dropdown-btn:hover {
    background: #e9ecef;
}
.dropdown-btn:active {
    background: #dee2e6;
}

/* Validation error styling */
.validation-error {
    color: #dc3545;
    font-size: 0.875em;
    margin-top: 4px;
    padding: 4px 8px;
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    border-radius: 4px;
    display: none;
}

/* ICD suggestions container */
.suggestion-list, .icd-suggestions {
    background: white;
    border: 1px solid #ddd;
    border-top: none;
    max-height: 200px;
    overflow-y: auto;
}

/* Room suggestions styling */
.room-suggestion {
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
    transition: background-color 0.2s;
}
.room-suggestion:hover,
.room-suggestion.active {
    background-color: #f0f8ff;
}
.room-suggestion .code {
    font-weight: bold;
    color: #2c5f2d;
}
.room-suggestion .desc {
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

/* Ensure suggestion container is placed correctly relative to input */
.suggestion-container { position: relative; }

/* Suggestion dropdown visual (scrollable, elevated, rounded) */
.suggestion-list {
    position: absolute !important;
    left: 0;
    right: 0;
    z-index: 99999 !important;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
    max-height: 320px;
    overflow-y: auto;
    padding: 4px 0;
}

.icd-suggestion {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
}
.icd-suggestion .code {
    font-weight: 700;
    color: #0b5ed7;
    margin-right: 12px;
}
.icd-suggestion .desc {
    color: #374151;
    font-size: 0.95em;
    margin-left: 12px;
    flex: 1;
    text-align: left;
}

.icd-suggestion.footer-suggestion {
    border-top: 1px solid #eee;
    background: #ffffff;
}

/* Make sure the dropdown doesn't get clipped by parent overflow */
.suggestion-list::-webkit-scrollbar { width: 10px; }
.suggestion-list::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.12); border-radius: 6px; }

/* Ensure suggestion dropdowns don't affect layout */
.suggestion-list,
.icd-suggestions,
#room-suggestions,
#icd10-suggestions {
    position: absolute !important;
    z-index: 9999 !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    border-radius: 4px !important;
    contain: layout style !important;
}

/* Prevent suggestion containers from triggering layout recalculations */
.suggestion-list *,
.icd-suggestions *,
#room-suggestions *,
#icd10-suggestions * {
    contain: layout style !important;
}
</style>
@endpush

@include('nurse.modals.notification_system')