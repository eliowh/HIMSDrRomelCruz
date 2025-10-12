<!-- Lab Result Template Selection & Data Entry Modal -->
<div id="labTemplateModal" class="modal">
  <div class="modal-content" style="max-width:900px;">
    <span class="close" onclick="closeLabTemplateModal()">&times;</span>
    <h3 style="margin-top:0;">Generate Lab Result PDF</h3>
    <div id="templateStepSelect">
      <p style="margin:4px 0 12px;">Select a result form template for this laboratory order.</p>
      <div style="display:flex;gap:10px;margin-bottom:12px;">
        <input type="text" id="labTemplateSearch" placeholder="Search templates..." style="flex:1;padding:6px 8px;" />
        <button class="btn" onclick="reloadLabTemplates()">Reload</button>
      </div>
      <div id="labTemplateList" style="max-height:260px;overflow:auto;border:1px solid #ddd;border-radius:6px;padding:6px;background:#fafafa;"></div>
      <div style="margin-top:12px;text-align:right;">
        <button class="btn cancel-btn" onclick="closeLabTemplateModal()">Cancel</button>
      </div>
    </div>
    <div id="templateStepForm" style="display:none;">
      <div style="display:flex;justify-content:space-between;align-items:center;">
        <h4 id="labTemplateTitle" style="margin:0 0 10px;"></h4>
        <button class="btn" onclick="backToTemplateList()" type="button">&larr; Change Template</button>
      </div>
      <form id="labTemplateDynamicForm" onsubmit="submitLabTemplateForm(event)">
        <div id="labTemplateFields"></div>
        <div style="margin-top:18px;display:flex;justify-content:flex-end;gap:10px;">
          <button type="button" class="btn cancel-btn" onclick="closeLabTemplateModal()">Cancel</button>
          <button type="submit" class="btn complete-btn" id="labTemplateSubmitBtn">Generate PDF</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
let __labTemplates = [];
let __currentTemplate = null;
let __currentLabOrderId = null;

function openLabTemplateModal(orderId){
  __currentLabOrderId = orderId;
  const modal = document.getElementById('labTemplateModal');
  modal.classList.add('show');
  modal.classList.add('open');
  backToTemplateList();
  if(!__labTemplates.length){ reloadLabTemplates(); }
  
  // Ensure modal is properly focused and inputs can receive focus
  setTimeout(() => {
    modal.focus();
    // Remove any potential focus traps
    const inputs = modal.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
      input.removeAttribute('readonly');
      input.removeAttribute('disabled');
    });
  }, 100);
}
function closeLabTemplateModal(){
  document.getElementById('labTemplateModal').classList.remove('show','open');
  __currentTemplate = null; __currentLabOrderId = null;
}
function reloadLabTemplates(){
  fetch("<?php echo e(route('labtech.lab.templates')); ?>")
    .then(r=>r.json())
    .then(j=>{ if(j.success){ __labTemplates = j.templates; renderLabTemplateList(); } })
    .catch(e=>console.error('template load error',e));
}
function renderLabTemplateList(){
  const listEl = document.getElementById('labTemplateList');
  const term = (document.getElementById('labTemplateSearch').value||'').toLowerCase();
  listEl.innerHTML='';
  const filtered = __labTemplates.filter(t => !term || t.title.toLowerCase().includes(term) || t.code.toLowerCase().includes(term));
  if(!filtered.length){ listEl.innerHTML='<div style="padding:8px;">No templates found.</div>'; return; }
  filtered.forEach(t => {
    const div = document.createElement('div');
    div.className='lab-template-item';
    div.style.cssText='padding:8px 10px;border:1px solid #ccc;background:#fff;margin-bottom:6px;border-radius:6px;cursor:pointer;display:flex;justify-content:space-between;align-items:center;';
    div.innerHTML='<div><strong>'+t.title+'</strong><br><span style="font-size:11px;color:#666;">'+t.code+' • '+t.field_count+' fields</span></div><button class="btn" style="padding:4px 10px;">Select</button>';
    div.addEventListener('click', ()=> selectLabTemplate(t.code || t.key));
    listEl.appendChild(div);
  });
}
function selectLabTemplate(code){
  const tpl = __labTemplates.find(t => t.code === code || t.key === code);
  if(!tpl) return;
  __currentTemplate = tpl;
  document.getElementById('templateStepSelect').style.display='none';
  document.getElementById('templateStepForm').style.display='block';
  document.getElementById('labTemplateTitle').textContent = tpl.title;
  buildDynamicFields(tpl);
}
function backToTemplateList(){
  document.getElementById('templateStepSelect').style.display='block';
  document.getElementById('templateStepForm').style.display='none';
}
function buildDynamicFields(tpl){
  const container = document.getElementById('labTemplateFields');
  container.innerHTML='';
  const help = document.createElement('div');
  help.style.cssText='font-size:11px;color:#555;margin-bottom:8px;';
  help.textContent='Enter the measured values. Leave blank for any not performed.';
  container.appendChild(help);
  if(tpl.type === 'sectioned'){ fetchTemplateStructure(tpl); return; }
  // flat type needs full template definition from server (call structure endpoint?)
  fetchStructureAndRender(tpl);
}
function fetchTemplateStructure(tpl){ fetchStructureAndRender(tpl); }
function fetchStructureAndRender(tpl){
  // We already only have meta. Need full structure? For simplicity call a lightweight endpoint returning structure embedded in config.
  // Reuse list endpoint (already loaded minimal). We'll request again with a flag for structure.
  fetch("<?php echo e(route('labtech.lab.templates')); ?>?details=1")
    .then(r=>r.json())
    .then(j=>{
      if(!j.success || !j.templates_full) { simpleRenderFallback(); return; }
      const full = j.templates_full[tpl.key] || j.templates_full[tpl.code?.toLowerCase()] || null;
      if(!full){ simpleRenderFallback(); return; }
      renderFields(full);
    }).catch(e=>{ console.error(e); simpleRenderFallback(); });
  function simpleRenderFallback(){
    const div=document.createElement('div'); div.textContent='Template structure unavailable. Please reload.'; container.appendChild(div);
  }
}

function fetchPatientDataForLabOrder(){
  if(!__currentLabOrderId) return;
  
  // Fetch lab order details to get patient information
  fetch(`/labtech/orders/${__currentLabOrderId}/details`)
    .then(r=>r.json())
    .then(j=>{
      if(j.success && j.order && j.order.patient) {
        const patient = j.order.patient;
        // Set sex value in hidden input
        const sexInput = document.getElementById('patient-sex-input');
        if(sexInput && patient.sex) {
          // Convert sex to the format expected by the form (M/F)
          const sexValue = patient.sex.toLowerCase() === 'male' ? 'M' : 
                          patient.sex.toLowerCase() === 'female' ? 'F' : 
                          patient.sex.toUpperCase();
          sexInput.value = sexValue;
        }
      }
    })
    .catch(e=>console.error('Error fetching patient data:', e));
}
function renderFields(full){
  const container = document.getElementById('labTemplateFields');
  container.querySelectorAll('.field-block').forEach(e=>e.remove());
  
  // Get patient data from the lab order to populate sex automatically
  fetchPatientDataForLabOrder();
  
  if(full.sections){
    Object.keys(full.sections).forEach(section => {
      const secEl = document.createElement('div');
      secEl.className='field-block';
      secEl.innerHTML='<h5 style="margin:14px 0 6px;">'+section+'</h5>';
      full.sections[section].forEach(f => {
        const row = document.createElement('div');
        row.style.cssText='display:flex;gap:8px;margin-bottom:6px;';
        if(f.key === 'sex') {
          // Skip sex field - will be populated automatically from patient data
          return;
        }
        row.innerHTML='<label style="flex:0 0 220px;font-size:12px;">'+f.label+'</label><input name="'+f.key+'" style="flex:1;padding:4px 6px;" type="text" autocomplete="off" tabindex="0" />';
        secEl.appendChild(row);
      });
      container.appendChild(secEl);
    });
  } else if(full.fields){
    const block = document.createElement('div'); block.className='field-block';
    full.fields.forEach(f => {
      const row = document.createElement('div');
      row.style.cssText='display:flex;gap:8px;margin-bottom:6px;';
      if(f.key === 'sex') {
        // Create hidden input that will be populated with patient sex data
        row.innerHTML='<input type="hidden" name="'+f.key+'" id="patient-sex-input" />';
      } else {
        row.innerHTML='<label style="flex:0 0 220px;font-size:12px;">'+f.label+'</label><input name="'+f.key+'" style="flex:1;padding:4px 6px;" type="text" autocomplete="off" tabindex="0" />';
      }
      block.appendChild(row);
    });
    container.appendChild(block);
  }
  
  // Ensure all inputs are focusable
  setTimeout(() => {
    const inputs = container.querySelectorAll('input[type="text"]');
    inputs.forEach(input => {
      input.removeAttribute('readonly');
      input.removeAttribute('disabled');
      input.setAttribute('tabindex', '0');
      
      // Add click handler to ensure focus works
      input.addEventListener('click', function() {
        this.focus();
      });
      
      // Add focus handler for debugging
      input.addEventListener('focus', function() {
        console.log('Input focused:', this.name);
      });
    });
  }, 50);
}
function submitLabTemplateForm(e){
  e.preventDefault();
  if(!__currentTemplate || !__currentLabOrderId) return;
  const form = e.target;
  const data = new FormData(form);
  const values = {};
  data.forEach((v,k)=>{ if(v!=='' ) values[k]=v; });
  const payload = { template_key: (__currentTemplate.key||__currentTemplate.code||'').toLowerCase(), values };
  const btn = document.getElementById('labTemplateSubmitBtn');
  btn.disabled = true; btn.textContent='Generating...';
  fetch(`/labtech/orders/${__currentLabOrderId}/generate-template`, {
    method:'POST',
    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').getAttribute('content')},
    body: JSON.stringify(payload)
  }).then(r=>{
    if(!r.ok) {
      // Server returned error status
      return r.text().then(text => {
        try {
          const json = JSON.parse(text);
          throw new Error(json.message || 'Server error');
        } catch(e) {
          // Not JSON, probably HTML error page
          throw new Error(`Server error (${r.status}): Please check if you're logged in as lab technician`);
        }
      });
    }
    return r.json();
  }).then(j=>{
    if(!j.success){ alert('Failed: '+(j.message||'Unknown error')); return; }
    alert('PDF generated and attached.');
    closeLabTemplateModal();
    if(typeof refreshLabOrders === 'function') refreshLabOrders(); else location.reload();
  }).catch(e=>{ console.error(e); alert('Error: ' + e.message); })
    .finally(()=>{ btn.disabled=false; btn.textContent='Generate PDF'; });
}
// Search listener
setTimeout(()=>{
  const search = document.getElementById('labTemplateSearch');
  if(search){ search.addEventListener('input', renderLabTemplateList); }
}, 50);
</script><?php /**PATH D:\xamppLatest\htdocs\HIMSDrRomelCruz\resources\views/labtech/modals/template_select_modal.blade.php ENDPATH**/ ?>