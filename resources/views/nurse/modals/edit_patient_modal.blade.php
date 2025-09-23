<!-- Edit Patient Modal -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <span class="close modal-close">&times;</span>
        <h3>Edit Patient</h3>
        <form id="editPatientForm">
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                <div class="form-grid">
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
                        <input id="edit_date_of_birth" name="date_of_birth" type="date" />
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_province">Province</label>
                        <input id="edit_province" name="province" placeholder="Province" />
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_city">City</label>
                        <input id="edit_city" name="city" placeholder="City" />
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_barangay">Barangay</label>
                        <input id="edit_barangay" name="barangay" placeholder="Barangay" />
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_nationality">Nationality</label>
                        <input id="edit_nationality" name="nationality" placeholder="Nationality" />
                    </div>

                    <div class="form-group">
                        <label for="edit_room_no">Room</label>
                        <div style="position:relative;">
                            <input id="edit_room_no" name="room_no" placeholder="Room No" autocomplete="off" />
                            <div id="edit_room_suggestions" class="icd-suggestions" style="position:absolute; left:0; right:0; z-index:2000; display:none;"></div>
                        </div>
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
                        <input id="edit_doctor_name" name="doctor_name" placeholder="Doctor" />
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
                    
                    <div class="form-group">
                        <label for="edit_admission_diagnosis">Admission Diagnosis (ICD-10)</label>
                        <div style="position:relative;">
                            <input id="edit_admission_diagnosis" name="admission_diagnosis" type="text" autocomplete="off" placeholder="Type ICD-10 code or disease name" />
                            <div id="edit_icd10_suggestions" class="icd-suggestions" style="position:absolute; left:0; right:0; z-index:2000; display:none;"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_admission_diagnosis_description">Admission Diagnosis Description</label>
                        <input id="edit_admission_diagnosis_description" name="admission_diagnosis_description" type="text" placeholder="Description will appear here" readonly />
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn cancel-btn modal-close">Cancel</button>
                    <button id="savePatientBtn" type="button" class="btn submit-btn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add ICD-10 and Room suggestion scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
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
    
    // ICD-10 Suggestion functionality
    (function() {
        const input = document.getElementById('edit_admission_diagnosis');
        const descField = document.getElementById('edit_admission_diagnosis_description');
        const container = document.getElementById('edit_icd10_suggestions');
        if (!input || !container) return;
        
        let timer = null;
        let activeIndex = -1;
        let lastItems = [];
        
        function clearSuggestions() {
            container.innerHTML = '';
            container.style.display = 'none';
            activeIndex = -1;
            lastItems = [];
        }
        
        function renderSuggestions(items) {
            lastItems = items;
            if (!items || !items.length) {
                clearSuggestions();
                return;
            }
            
            container.innerHTML = '';
            items.forEach((it, idx) => {
                const el = document.createElement('div');
                el.className = 'icd-suggestion';
                el.dataset.index = idx;
                el.innerHTML = '<span class="code">' + escapeHtml(it.code) + '</span> <span class="desc">' + escapeHtml(it.description) + '</span>';
                el.addEventListener('click', () => selectItem(idx));
                container.appendChild(el);
            });
            
            container.style.display = 'block';
            activeIndex = -1;
        }
        
        function escapeHtml(s) {
            if (!s) return '';
            return s.replace(/[&<>"']/g, (m) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            }[m]));
        }
        
        function selectItem(idx) {
            const item = lastItems[idx];
            if (!item) return;
            input.value = item.code || item.description || '';
            descField.value = item.description || '';
            clearSuggestions();
        }
        
        function highlightActive() {
            const nodes = container.querySelectorAll('.icd-suggestion');
            nodes.forEach((n, i) => n.classList.toggle('active', i === activeIndex));
        }
        
        input.addEventListener('input', () => {
            const q = input.value.trim();
            if (timer) clearTimeout(timer);
            if (q.length < 1) {
                clearSuggestions();
                return;
            }
            
            timer = setTimeout(() => {
                const url = '{{ route("icd10.search") }}?q=' + encodeURIComponent(q);
                
                fetch(url)
                    .then(async r => {
                        const ct = (r.headers.get('content-type') || '').toLowerCase();
                        const text = await r.text();
                        
                        if (ct.includes('application/json')) {
                            try {
                                const data = JSON.parse(text);
                                renderSuggestions(Array.isArray(data) ? data : []);
                            } catch (parseErr) {
                                console.error('Failed to parse JSON', parseErr, text);
                                clearSuggestions();
                            }
                        } else {
                            console.warn('Non-JSON response for ICD10 search', r.status, ct, text);
                            clearSuggestions();
                        }
                    })
                    .catch(e => {
                        console.error('ICD10 fetch error', e);
                        clearSuggestions();
                    });
            }, 250);
        });
        
        input.addEventListener('keydown', (e) => {
            const nodes = container.querySelectorAll('.icd-suggestion');
            if (!nodes.length) return;
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = Math.min(activeIndex + 1, nodes.length - 1);
                highlightActive();
                nodes[activeIndex].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = Math.max(activeIndex - 1, 0);
                highlightActive();
                nodes[activeIndex].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeIndex >= 0) selectItem(activeIndex);
            } else if (e.key === 'Escape') {
                clearSuggestions();
            }
        });
        
        document.addEventListener('click', (e) => {
            if (!container.contains(e.target) && e.target !== input) clearSuggestions();
        });
    })();
    
    // Room Suggestion functionality
    (function() {
        const input = document.getElementById('edit_room_no');
        const container = document.getElementById('edit_room_suggestions');
        if (!input || !container) return;
        
        let timer = null;
        let activeIndex = -1;
        let lastItems = [];
        
        function escapeHtml(s) {
            if (!s) return '';
            return String(s).replace(/[&<>"']/g, (m) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            }[m]));
        }
        
        function clearSuggestions() {
            container.innerHTML = '';
            container.style.display = 'none';
            activeIndex = -1;
            lastItems = [];
        }
        
        function renderSuggestions(items) {
            lastItems = items || [];
            if (!lastItems.length) {
                clearSuggestions();
                return;
            }
            
            container.innerHTML = '';
            lastItems.forEach((it, idx) => {
                const el = document.createElement('div');
                el.className = 'icd-suggestion';
                el.dataset.index = idx;
                el.innerHTML = '<span class="code">' + escapeHtml(it.name) + '</span>';
                el.addEventListener('click', () => selectItem(idx));
                container.appendChild(el);
            });
            
            container.style.display = 'block';
            activeIndex = -1;
        }
        
        function selectItem(idx) {
            const item = lastItems[idx];
            if (!item) return;
            input.value = item.name || '';
            clearSuggestions();
        }
        
        function highlightActive() {
            const nodes = container.querySelectorAll('.icd-suggestion');
            nodes.forEach((n, i) => n.classList.toggle('active', i === activeIndex));
        }
        
        input.addEventListener('input', () => {
            const q = input.value.trim();
            if (timer) clearTimeout(timer);
            if (q.length < 1) {
                clearSuggestions();
                return;
            }
            
            timer = setTimeout(() => {
                const url = '{{ route("rooms.search") }}?q=' + encodeURIComponent(q);
                fetch(url).then(async r => {
                    const ct = (r.headers.get('content-type') || '').toLowerCase();
                    const text = await r.text();
                    if (ct.includes('application/json')) {
                        try {
                            const data = JSON.parse(text);
                            renderSuggestions(Array.isArray(data) ? data : []);
                        } catch (e) {
                            console.error('Room parse error', e);
                            clearSuggestions();
                        }
                    } else {
                        console.warn('Non-JSON response for rooms search', r.status);
                        clearSuggestions();
                    }
                }).catch(e => {
                    console.error('Rooms fetch error', e);
                    clearSuggestions();
                });
            }, 200);
        });
        
        input.addEventListener('keydown', (e) => {
            const nodes = container.querySelectorAll('.icd-suggestion');
            if (!nodes.length) return;
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = Math.min(activeIndex + 1, nodes.length - 1);
                highlightActive();
                nodes[activeIndex].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = Math.max(activeIndex - 1, 0);
                highlightActive();
                nodes[activeIndex].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeIndex >= 0) selectItem(activeIndex);
            } else if (e.key === 'Escape') {
                clearSuggestions();
            }
        });
        
        document.addEventListener('click', (e) => {
            if (!container.contains(e.target) && e.target !== input) clearSuggestions();
        });
    })();
});
</script>