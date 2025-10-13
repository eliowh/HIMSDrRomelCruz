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
                <label for="sex">Sex</label>
                <select id="sex" name="sex" required>
                    <option value="" disabled selected>-- Select Sex --</option>
                    <option value="male" {{ old('sex') == 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ old('sex') == 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other" {{ old('sex') == 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="contact_number">Contact Number</label>
                <input id="contact_number" type="number" name="contact_number" placeholder="Enter contact number" min="1000000000" max="99999999999" maxlength="11" oninput="if(this.value.length > 11) this.value = this.value.slice(0, 11);" value="{{ old('contact_number') }}">
            </div>

            <div class="form-group">
                <label for="province">Province</label>
                <select id="province" name="province" data-selected="{{ old('province') }}">
                    <option value="" disabled selected>-- Loading provinces... --</option>
                </select>
            </div>

            <div class="form-group">
                <label for="city">City</label>
                <select id="city" name="city" data-selected="{{ old('city') }}">
                    <option value="" disabled selected>-- Select province first --</option>
                </select>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    // Use local proxy endpoints to avoid CORS issues
                    const API_BASE = '/api/locations';
                    const provinceSel = document.getElementById('province');
                    const citySel = document.getElementById('city');
                    const selectedProvince = provinceSel.getAttribute('data-selected') || '';
                    const selectedCity = citySel.getAttribute('data-selected') || '';

                    function clearSelect(sel) {
                        while (sel.firstChild) sel.removeChild(sel.firstChild);
                    }

                    function addOption(sel, value, text, isSelected, dataCode) {
                        const opt = document.createElement('option');
                        opt.value = value;
                        opt.textContent = text;
                        if (isSelected) opt.selected = true;
                        if (dataCode !== undefined && dataCode !== null) opt.dataset.code = dataCode;
                        sel.appendChild(opt);
                    }

                    // load provinces and store codes in data-code while keeping option value as the province name
                    let provincesList = [];
                    fetch(API_BASE + '/provinces')
                        .then(r => {
                            console.log('Provinces fetch status', r.status);
                            return r.ok ? r.json() : Promise.reject('No provinces.json');
                        })
                        .then(list => {
                            console.log('Provinces received:', Array.isArray(list) ? list.length : typeof list);
                            provincesList = Array.isArray(list) ? list : [];
                            clearSelect(provinceSel);
                            addOption(provinceSel, '', '-- Select Province --', false, '');
                            provincesList.forEach(p => {
                                const name = p.name || p.province_name || p.provDesc || p.prov_name || p.province || '';
                                const code = p.code || p.province_code || p.provCode || p.prov_code || p.id || '';
                                if (!name) return;
                                const isSelected = selectedProvince && (selectedProvince === name);
                                addOption(provinceSel, name, name, isSelected, code);
                            });

                            if (selectedProvince) {
                                // try to find the province option that matches the selected name and get its code
                                const opt = Array.from(provinceSel.options).find(o => o.value === selectedProvince);
                                let code = opt ? opt.dataset.code : '';
                                // if code is empty, try to derive from provincesList by normalized name
                                if (!code && provincesList.length) {
                                    const normTarget = normalize(selectedProvince);
                                    const found = provincesList.find(pp => normalize(pp.name || pp.province_name || pp.provDesc || pp.prov_name || pp.province || '') === normTarget);
                                    if (found) code = found.code || found.provCode || found.province_code || found.prov_code || found.id || '';
                                    }
                                    // set province select value explicitly and trigger change so cities load immediately
                                    const resolvedOpt = Array.from(provinceSel.options).find(o => o.value === selectedProvince) || Array.from(provinceSel.options).find(o => o.dataset && o.dataset.code === code);
                                    if (resolvedOpt) provinceSel.value = resolvedOpt.value;
                                    provinceSel.dispatchEvent(new Event('change', { bubbles: true }));
                                    loadCitiesForProvince(selectedProvince, code);
                            }
                        })
                        .catch(err => {
                            console.warn('Failed to load provinces from PSGC API', err);
                            clearSelect(provinceSel);
                            addOption(provinceSel, '', '-- Unable to load provinces --', false, '');
                        });

                    function normalize(s) {
                        if (!s) return '';
                        return s.toString().normalize('NFD').replace(/\p{Diacritic}/gu, '').replace(/[^\w\s]/g, '').toLowerCase().trim();
                    }

                    function loadCitiesForProvince(provinceName, provinceCode) {
                        clearSelect(citySel);
                        addOption(citySel, '', '-- Loading cities... --', false, '');

                        // pass province_code if available for better matching
                        const citiesUrl = API_BASE + '/cities' + (provinceCode ? ('?province_code=' + encodeURIComponent(provinceCode)) : ('?province=' + encodeURIComponent(provinceName)));
                        fetch(citiesUrl)
                            .then(r => {
                                console.log('Cities fetch url:', citiesUrl, 'status', r.status);
                                return r.ok ? r.json() : Promise.reject('No cities.json');
                            })
                            .then(list => {
                                console.log('Cities received:', Array.isArray(list) ? list.length : typeof list);
                                clearSelect(citySel);
                                addOption(citySel, '', '-- Select City --', false, '');

                                let matched = [];

                                // helper to get province-like string from city entry
                                const cityProvinceOf = c => (c.province_name || c.provDesc || c.prov_name || c.province || c.region || '').toString();

                                if (provinceCode) {
                                    matched = list.filter(c => {
                                        // include various possible province-code fields from PSGC JSON
                                        const ccode = c.provinceCode || c.provCode || c.province_code || c.provinceId || c.province_id || c.prov_code || c.province || c.psgc10DigitCode || c.psgc10digitcode || c.code || c.id || '';
                                        return ccode && (ccode.toString() === provinceCode.toString());
                                    });
                                }

                                if (!matched.length && provinceName) {
                                    const normTarget = normalize(provinceName);
                                    matched = list.filter(c => {
                                        const prov = cityProvinceOf(c);
                                        return normalize(prov) === normTarget || normalize(prov).includes(normTarget) || normalize((c.name||c.city_name||c.citymunDesc||c.municipality||c.city||'')).includes(normTarget);
                                    });
                                }

                                // last resort: fuzzy match using substring normalization on city name
                                if (!matched.length && provinceName) {
                                    const normTarget = normalize(provinceName);
                                    matched = list.filter(c => normalize(c.name || c.city_name || c.citymunDesc || c.municipality || c.city || '').includes(normTarget));
                                }

                                console.log('Matched cities count:', matched.length);
                                if (!matched.length) {
                                    clearSelect(citySel);
                                    addOption(citySel, '', '-- No cities found for selected province --', false, '');
                                    return;
                                }

                                matched.forEach(c => {
                                    const cname = c.name || c.city_name || c.citymunDesc || c.municipality || c.city || '';
                                    if (!cname) return;
                                    const isSelected = selectedCity && (selectedCity === cname);
                                    addOption(citySel, cname, cname, isSelected, c.code || c.city_code || c.id || '');
                                });
                            })
                            .catch(err => {
                                console.warn('Failed to load cities from PSGC API', err);
                                clearSelect(citySel);
                                addOption(citySel, '', '-- Unable to load cities --', false, '');
                            });
                    }

                    provinceSel.addEventListener('change', function () {
                        const selOpt = this.options[this.selectedIndex];
                        const provName = selOpt ? selOpt.value : '';
                        const provCode = selOpt && selOpt.dataset ? selOpt.dataset.code : '';
                        if (provName) loadCitiesForProvince(provName, provCode);
                        else {
                            clearSelect(citySel);
                            addOption(citySel, '', '-- Select province first --', false, '');
                        }
                    });
                });
            </script>

            <div class="form-group">
                <label for="barangay">Barangay</label>
                <input id="barangay" type="text" name="barangay" placeholder="Enter barangay" required value="{{ old('barangay') }}">
            </div>

            <div class="form-group">
                <label for="nationality">Nationality</label>
                <input id="nationality" type="text" name="nationality" placeholder="Enter nationality" required value="{{ old('nationality','Filipino') }}">
            </div>

            <!-- START: Social History -->
            <div class="form-divider"></div>
            <div class="section-header full-width">
                <h4 style="color: #2c5f2d; margin: 0; font-size: 16px; font-weight: 600;">Social History</h4>
            </div>

            <!-- Lifestyle Habits Category -->
            <div class="form-group full-width">
                <label style="font-weight: 600; color: #495057;">Lifestyle Habits</label>
            </div>

            <div class="form-group full-width">
                <label for="smoking_history">Do you smoke? If yes, how often and how many years?</label>
                <textarea id="smoking_history" name="smoking_history" rows="2" placeholder="Please specify smoking frequency, duration, and type (cigarettes, cigars, etc.)" value="{{ old('smoking_history') }}"></textarea>
            </div>

            <div class="form-group full-width">
                <label for="alcohol_consumption">Do you drink alcohol? If yes, how frequently?</label>
                <textarea id="alcohol_consumption" name="alcohol_consumption" rows="2" placeholder="Please specify frequency and type of alcohol consumption" value="{{ old('alcohol_consumption') }}"></textarea>
            </div>

            <div class="form-group full-width">
                <label for="recreational_drugs">Do you use recreational drugs?</label>
                <textarea id="recreational_drugs" name="recreational_drugs" rows="2" placeholder="Please specify any recreational drug use" value="{{ old('recreational_drugs') }}"></textarea>
            </div>

            <div class="form-group full-width">
                <label for="exercise_activity">How often do you exercise or engage in physical activity?</label>
                <textarea id="exercise_activity" name="exercise_activity" rows="2" placeholder="Please describe your exercise routine and physical activity level" value="{{ old('exercise_activity') }}"></textarea>
            </div>
            <!-- END: Social History -->

            <!-- START: General Health History -->
            <div class="form-divider"></div>
            <div class="section-header full-width">
                <h4 style="color: #2c5f2d; margin: 0; font-size: 16px; font-weight: 600;">General Health History</h4>
            </div>

            <!-- Medical Conditions Category -->
            <div class="form-group full-width">
                <label style="font-weight: 600; color: #495057;">Medical Conditions</label>
            </div>
            
            <div class="form-group full-width">
                <label for="chronic_illnesses">Have you been diagnosed with any chronic illnesses?</label>
                <textarea id="chronic_illnesses" name="chronic_illnesses" rows="2" placeholder="Please specify any chronic illnesses (diabetes, hypertension, heart disease, etc.)" value="{{ old('chronic_illnesses') }}"></textarea>
            </div>

            <div class="form-group full-width">
                <label for="hospitalization_history">Have you ever been hospitalized before? If yes, for what reason and when?</label>
                <textarea id="hospitalization_history" name="hospitalization_history" rows="2" placeholder="Please specify reasons and dates of previous hospitalizations" value="{{ old('hospitalization_history') }}"></textarea>
            </div>

            <div class="form-group full-width">
                <label for="surgery_history">Have you ever undergone surgery? If yes, what type and when?</label>
                <textarea id="surgery_history" name="surgery_history" rows="2" placeholder="Please specify types of surgeries and dates performed" value="{{ old('surgery_history') }}"></textarea>
            </div>

            <div class="form-group full-width">
                <label for="accident_injury_history">Do you have any history of accidents or injuries?</label>
                <textarea id="accident_injury_history" name="accident_injury_history" rows="2" placeholder="Please describe any significant accidents or injuries" value="{{ old('accident_injury_history') }}"></textarea>
            </div>

            <!-- Medications Category -->
            <div class="form-group full-width">
                <label style="font-weight: 600; color: #495057;">Medications</label>
            </div>

            <div class="form-group full-width">
                <label for="current_medications">Are you currently taking any medications? If yes, please list them.</label>
                <textarea id="current_medications" name="current_medications" rows="3" placeholder="Please list all current medications, dosages, and frequency" value="{{ old('current_medications') }}"></textarea>
            </div>

            <div class="form-group full-width">
                <label for="long_term_medications">Have you taken long-term medications in the past?</label>
                <textarea id="long_term_medications" name="long_term_medications" rows="2" placeholder="Please specify any long-term medications you have taken previously" value="{{ old('long_term_medications') }}"></textarea>
            </div>

            <!-- Allergies Category -->
            <div class="form-group full-width">
                <label style="font-weight: 600; color: #495057;">Allergies</label>
            </div>

            <div class="form-group full-width">
                <label for="known_allergies">Do you have any known allergies? If yes, what reactions have you experienced?</label>
                <textarea id="known_allergies" name="known_allergies" rows="2" placeholder="Please specify allergies (food, medications, environmental) and reactions" value="{{ old('known_allergies') }}"></textarea>
            </div>

            <!-- Family History Category -->
            <div class="form-group full-width">
                <label style="font-weight: 600; color: #495057;">Family History</label>
            </div>

            <div class="form-group full-width">
                <label for="family_history_chronic">Do you have any family history of chronic diseases?</label>
                <textarea id="family_history_chronic" name="family_history_chronic" rows="2" placeholder="Please specify any family history of chronic diseases (heart disease, diabetes, cancer, etc.)" value="{{ old('family_history_chronic') }}"></textarea>
            </div>
            <!-- END: General Health History -->
            
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

/* Health History Section Styling */
.section-header {
    margin: 20px 0 15px 0;
    padding-bottom: 8px;
    border-bottom: 2px solid #e9ecef;
}

.section-header h4 {
    margin: 0;
    padding: 0;
    display: flex;
    align-items: center;
}

.section-header h4:before {
    content: '';
    width: 4px;
    height: 20px;
    background: #2c5f2d;
    margin-right: 10px;
}

/* Category labels styling */
.form-group label[style*="font-weight: 600"] {
    background: #f8f9fa;
    padding: 8px 12px;
    border-radius: 4px;
    border-left: 3px solid #2c5f2d;
    margin-bottom: 15px !important;
    display: block;
    font-size: 14px;
}

/* Textarea styling for health history */
textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-family: inherit;
    font-size: 14px;
    line-height: 1.4;
    resize: vertical;
    min-height: 60px;
}

textarea:focus {
    outline: none;
    border-color: #2c5f2d;
    box-shadow: 0 0 0 2px rgba(44, 95, 45, 0.1);
}

/* Form divider styling */
.form-divider {
    width: 100%;
    height: 1px;
    background: linear-gradient(to right, transparent, #ddd, transparent);
    margin: 30px 0 20px 0;
    grid-column: 1 / -1;
}
</style>
@endpush

@include('nurse.modals.notification_system')