@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ url('css/nursecss/nurse_addPatients.css') }}">
<link rel="stylesheet" href="{{ url('css/nursecss/nurse_addPatients_fixes.css') }}">
<div class="nurse-card">
    <h3>Add Patient</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul style="margin:0;padding-left:18px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ url('/nurse/addPatients') }}" method="POST">
        @csrf

        <label>First Name</label>
        <input type="text" name="first_name" required value="{{ old('first_name') }}">

        <label>Middle Name</label>
        <input type="text" name="middle_name" value="{{ old('middle_name') }}">

        <label>Last Name</label>
        <input type="text" name="last_name" required value="{{ old('last_name') }}">

        <label>Date of Birth</label>
        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}">

        <div class="form-row">
            <div class="form-col">
                <label>Age</label>
                <div class="age-inputs">
                    <div class="age-item">
                        <label class="small-label">(years)</label>
                        <input type="number" name="age_years" min="0" value="{{ old('age_years') }}">
                    </div>
                    
                </div>
            </div>
        </div>

        <label>Province</label>
        <select name="province" required>
            <option value="Bulacan" {{ old('province','Bulacan')=='Bulacan' ? 'selected':'' }}>Bulacan</option>
            <!-- add others if needed -->
        </select>

        <label>City</label>
        <select name="city" required>
            <option value="Malolos City" {{ old('city','Malolos City')=='Malolos City' ? 'selected':'' }}>Malolos City</option>
            <!-- add others if needed -->
        </select>

        <label>Barangay</label>
        <input type="text" name="barangay" value="{{ old('barangay') }}">

        <label>Nationality</label>
        <input type="text" name="nationality" value="{{ old('nationality','Filipino') }}">

        <!-- START: Admission fields -->
        <hr style="margin:14px 0;border:none;border-top:1px solid #eee;">

        <label>Room</label>
        <div style="position:relative;">
            <input id="room-input" type="text" name="room_no" autocomplete="off" value="{{ old('room_no') }}" placeholder="Type room name or price">
            <div id="room-suggestions" class="icd-suggestions" style="position:absolute; left:0; right:0; z-index:2000; display:none;"></div>
        </div>

    <!-- Room price removed per request; only store the room name -->

        <label>Admission Diagnosis (Adm. Diag)</label>
        <div style="position:relative;">
            <input id="admission-diagnosis" name="admission_diagnosis" type="text" autocomplete="off" placeholder="Type ICD-10 code or disease name" value="{{ old('admission_diagnosis') }}" />
            <div id="icd10-suggestions" class="icd-suggestions" style="position:absolute; left:0; right:0; z-index:2000; display:none;"></div>
            <div id="icd10-debug" style="position:relative; margin-top:6px; font-size:0.9rem; color:#666;"></div>
        </div>

        <label>Admission Diagnosis Description</label>
        <input id="admission-diagnosis-desc" name="admission_diagnosis_description" type="text" placeholder="Description will appear here" readonly value="{{ old('admission_diagnosis_description') }}" />

        <div class="form-row" style="gap:10px;">
            <div class="form-col">
                <label>Admission Type (Adm. Type)</label>
                <select name="admission_type" required>
                    <option value="" disabled {{ old('admission_type')=='' ? 'selected':'' }}>-- Select --</option>
                    <option value="Emergency" {{ old('admission_type')=='Emergency' ? 'selected':'' }}>Emergency</option>
                    <option value="Elective" {{ old('admission_type')=='Elective' ? 'selected':'' }}>Elective</option>
                    <option value="Transfer" {{ old('admission_type')=='Transfer' ? 'selected':'' }}>Transfer</option>
                </select>
            </div>

            <div class="form-col">
                <label>Service</label>
                <select name="service" required>
                    <option value="" disabled {{ old('service')=='' ? 'selected':'' }}>-- Select Service --</option>
                    <option value="Inpatient" {{ old('service')=='Inpatient' ? 'selected':'' }}>Inpatient</option>
                    <option value="Outpatient" {{ old('service')=='Outpatient' ? 'selected':'' }}>Outpatient</option>
                    <option value="Surgery" {{ old('service')=='Surgery' ? 'selected':'' }}>Surgery</option>
                    <option value="Emergency" {{ old('service')=='Emergency' ? 'selected':'' }}>Emergency</option>
                </select>
            </div>
        </div>

        <div class="form-row" style="gap:10px;margin-top:8px;">
            <div class="form-col">
                <label>Doctor</label>
                <input type="text" name="doctor_name" value="{{ old('doctor_name') }}">
            </div>
            <div class="form-col">
                <label>Doctor Type</label>
                <select name="doctor_type" required>
                    <option value="" disabled {{ old('doctor_type')=='' ? 'selected':'' }}>-- Select --</option>
                    <option value="Consultant" {{ old('doctor_type')=='Consultant' ? 'selected':'' }}>Consultant</option>
                    <option value="Resident" {{ old('doctor_type')=='Resident' ? 'selected':'' }}>Resident</option>
                    <option value="Intern" {{ old('doctor_type')=='Intern' ? 'selected':'' }}>Intern</option>
                </select>
            </div>
        </div>
        <!-- END: Admission fields -->

        <p style="font-size:0.9em;color:#666">Patient No will be assigned automatically (starts at 250001).</p>

        <button type="submit">Create Patient</button>
    </form>
</div>
@endsection

@push('scripts')

<script>
(function(){
    const input = document.getElementById('admission-diagnosis');
    const descField = document.getElementById('admission-diagnosis-desc');
    const container = document.getElementById('icd10-suggestions');
    if (!input || !container) return;
    let timer = null; let activeIndex = -1; let lastItems = [];
    function clearSuggestions(){ container.innerHTML=''; container.style.display='none'; activeIndex=-1; lastItems=[]; }
    function renderSuggestions(items){ lastItems=items; if(!items||!items.length){ clearSuggestions(); return;} container.innerHTML=''; items.forEach((it,idx)=>{ const el=document.createElement('div'); el.className='icd-suggestion'; el.dataset.index=idx; el.innerHTML = '<span class="code">'+escapeHtml(it.code)+'</span> <span class="desc">'+escapeHtml(it.description)+'</span>'; el.addEventListener('click',()=>selectItem(idx)); container.appendChild(el); }); container.style.display='block'; activeIndex=-1; }
    function escapeHtml(s){ if(!s) return ''; return s.replace(/[&<>"']/g, (m)=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[m])); }
    function selectItem(idx){ const item=lastItems[idx]; if(!item) return; input.value = item.code || item.description || ''; descField.value = item.description || ''; clearSuggestions(); }
    function highlightActive(){ const nodes = container.querySelectorAll('.icd-suggestion'); nodes.forEach((n,i)=> n.classList.toggle('active', i===activeIndex)); }
    input.addEventListener('input', ()=>{ const q = input.value.trim(); if(timer) clearTimeout(timer); if(q.length<1){ clearSuggestions(); return; } // changed min chars to 1 for testing
        // show debug
        const dbg = document.getElementById('icd10-debug'); if(dbg) dbg.textContent = 'Searching for: ' + q;
        timer = setTimeout(()=>{ const url = '{{ route("icd10.search") }}?q='+encodeURIComponent(q); console.log('ICD10 fetch', url);
            fetch(url)
            .then(async r=>{
                console.log('ICD10 response', r.status);
                const dbg = document.getElementById('icd10-debug');
                const ct = (r.headers.get('content-type') || '').toLowerCase();
                const text = await r.text();
                // If server returns JSON, parse and render. Otherwise show helpful debug with a snippet of the HTML/error
                if (ct.includes('application/json')) {
                    try {
                        const data = JSON.parse(text);
                        console.log('ICD10 data', data);
                        renderSuggestions(Array.isArray(data)?data:[]);
                        if(dbg) dbg.textContent = 'Found ' + (Array.isArray(data)?data.length:0) + ' results';
                    } catch(parseErr){
                        console.error('Failed to parse JSON', parseErr, text);
                        if(dbg) dbg.textContent = 'Invalid JSON response';
                        clearSuggestions();
                    }
                } else {
                    console.warn('Non-JSON response for ICD10 search', r.status, ct, text);
                    if(dbg){
                        const snippet = escapeHtml(text.slice(0,1500));
                        dbg.innerHTML = 'Server returned non-JSON response (status ' + r.status + '). Showing first 1500 chars:<br><details><summary>Show snippet</summary><pre style="white-space:pre-wrap;">' + snippet + '</pre></details>';
                    }
                    clearSuggestions();
                }
            })
            .catch(e=>{ console.error('ICD10 fetch error', e); const dbg = document.getElementById('icd10-debug'); if(dbg) dbg.textContent = 'Fetch error: ' + (e.message||e); clearSuggestions(); }); }, 250); });
    input.addEventListener('keydown',(e)=>{ const nodes = container.querySelectorAll('.icd-suggestion'); if(!nodes.length) return; if(e.key==='ArrowDown'){ e.preventDefault(); activeIndex = Math.min(activeIndex+1, nodes.length-1); highlightActive(); nodes[activeIndex].scrollIntoView({block:'nearest'});
    } else if(e.key==='ArrowUp'){ e.preventDefault(); activeIndex = Math.max(activeIndex-1,0); highlightActive(); nodes[activeIndex].scrollIntoView({block:'nearest'});
    } else if(e.key==='Enter'){ e.preventDefault(); if(activeIndex>=0) selectItem(activeIndex); } else if(e.key==='Escape'){ clearSuggestions(); } });
    document.addEventListener('click',(e)=>{ if(!container.contains(e.target) && e.target !== input) clearSuggestions(); });
})();
</script>
<script>
(function(){
    // Room autocomplete (lightweight, suggests only room name)
    const input = document.getElementById('room-input');
    const container = document.getElementById('room-suggestions');
    if (!input || !container) return;
    let timer = null;
    let activeIndex = -1;
    let lastItems = [];

    function escapeHtml(s){ if(!s) return ''; return String(s).replace(/[&<>"']/g, (m)=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
    function clearSuggestions(){ container.innerHTML=''; container.style.display='none'; activeIndex=-1; lastItems=[]; }
    function renderSuggestions(items){ lastItems = items || []; if(!lastItems.length){ clearSuggestions(); return; } container.innerHTML = ''; lastItems.forEach((it, idx)=>{ const el = document.createElement('div'); el.className = 'icd-suggestion'; el.dataset.index = idx; el.innerHTML = '<span class="code">'+escapeHtml(it.name)+'</span>'; el.addEventListener('click', ()=> selectItem(idx)); container.appendChild(el); }); container.style.display = 'block'; activeIndex = -1; }
    function selectItem(idx){ const item = lastItems[idx]; if(!item) return; input.value = item.name || ''; clearSuggestions(); }
    function highlightActive(){ const nodes = container.querySelectorAll('.icd-suggestion'); nodes.forEach((n,i)=> n.classList.toggle('active', i===activeIndex)); }

    input.addEventListener('input', ()=>{
        const q = input.value.trim(); if(timer) clearTimeout(timer); if(q.length < 1){ clearSuggestions(); return; }
        timer = setTimeout(()=>{
            const url = '{{ route("rooms.search") }}?q=' + encodeURIComponent(q);
            fetch(url).then(async r => {
                const ct = (r.headers.get('content-type') || '').toLowerCase();
                const text = await r.text();
                if(ct.includes('application/json')){
                    try{
                        const data = JSON.parse(text);
                        renderSuggestions(Array.isArray(data)?data:[]);
                    }catch(e){ console.error('Room parse error', e); clearSuggestions(); }
                } else {
                    console.warn('Non-JSON response for rooms search', r.status);
                    clearSuggestions();
                }
            }).catch(e=>{ console.error('Rooms fetch error', e); clearSuggestions(); });
        }, 200);
    });

    input.addEventListener('keydown', (e)=>{
        const nodes = container.querySelectorAll('.icd-suggestion'); if(!nodes.length) return;
        if(e.key === 'ArrowDown'){ e.preventDefault(); activeIndex = Math.min(activeIndex+1, nodes.length-1); highlightActive(); nodes[activeIndex].scrollIntoView({block:'nearest'}); }
        else if(e.key === 'ArrowUp'){ e.preventDefault(); activeIndex = Math.max(activeIndex-1, 0); highlightActive(); nodes[activeIndex].scrollIntoView({block:'nearest'}); }
        else if(e.key === 'Enter'){ e.preventDefault(); if(activeIndex >= 0) selectItem(activeIndex); }
        else if(e.key === 'Escape'){ clearSuggestions(); }
    });

    document.addEventListener('click', (e)=>{ if(!container.contains(e.target) && e.target !== input) clearSuggestions(); });
})();
</script>
@endpush