@extends($layout ?? (auth()->user()->role === 'nurse' ? 'layouts.nurse' : 'layouts.doctor'))

@section('title', 'Messages')

@section('content')
<link rel="stylesheet" href="{{ asset('css/chat/chat.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="chat-container">
    <div class="chat-header">
        <h2><i class="fas fa-comments"></i> Messages</h2>
        <div class="chat-stats">
            <span class="stat">{{ $chatRooms->count() }} conversations</span>
            <button class="btn btn-primary" onclick="openCreateGroupModal()">
                <i class="fas fa-users-medical"></i> Create Role Group
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($chatRooms->count() > 0)
        <div class="chat-rooms-grid">
            @foreach($chatRooms as $room)
                <div class="chat-room-card" onclick="openChatRoom({{ $room->id }})">
                    <div class="room-header">
                        <div class="room-info">
                            <h3 class="room-name">{{ $room->name }}</h3>
                            <span class="patient-info">
                                Patient #{{ $room->patient_no }}
                                @if($room->patient)
                                    - {{ $room->patient->display_name }}
                                @endif
                            </span>
                        </div>
                        <div class="room-meta">
                            <span class="last-activity">
                                {{ $room->last_activity ? $room->last_activity->diffForHumans() : 'No activity' }}
                            </span>
                            <span class="participant-count">
                                <i class="fas fa-users"></i> {{ count($room->participants ?? []) }}
                            </span>
                        </div>
                    </div>
                    
                    @if($room->lastMessage->first())
                        <div class="last-message">
                            <strong>{{ $room->lastMessage->first()->sender_name }}:</strong>
                            <span class="message-preview">{{ Str::limit($room->lastMessage->first()->message, 80) }}</span>
                        </div>
                    @else
                        <div class="last-message">
                            <em>No messages yet</em>
                        </div>
                    @endif
                    
                    <div class="room-actions">
                        <button class="btn btn-primary btn-sm" onclick="event.stopPropagation(); openChatRoom({{ $room->id }})">
                            <i class="fas fa-comments"></i> Open Chat
                        </button>
                        @if($room->created_by === auth()->id())
                            <button class="btn btn-secondary btn-sm" onclick="event.stopPropagation(); archiveRoom({{ $room->id }})">
                                <i class="fas fa-archive"></i> Archive
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-comments"></i>
            </div>
            <h3>No Conversations Yet</h3>
            <p>Start a conversation by selecting a patient and clicking the "Message" button in the patient details.</p>
            <a href="{{ auth()->user()->role === 'nurse' ? '/nurse/patients' : '/doctor/patients' }}" class="btn btn-primary">
                <i class="fas fa-user-md"></i> Go to Patients
            </a>
        </div>
    @endif
</div>

<!-- Create Role Group Modal -->
<div id="createGroupModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-users-medical"></i> Create Role-Based Group Chat</h3>
            <button type="button" class="close-btn" onclick="closeCreateGroupModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="createGroupForm">
                <div class="form-group">
                    <label for="groupName">Group Name *</label>
                    <input type="text" id="groupName" name="name" class="form-control" 
                           placeholder="e.g., Emergency Team, Patient #123 Care Team" required>
                </div>
                
                <div class="form-group">
                    <label for="patientNo">Patient Number (Optional)</label>
                    <input type="text" id="patientNo" name="patient_no" class="form-control" 
                           placeholder="Associate with specific patient">
                </div>
                
                <div class="form-group">
                    <label>Select Roles to Include *</label>
                    <div class="roles-grid">
                        <div class="role-category">
                            <h5><i class="fas fa-user-shield"></i> Administration</h5>
                            <label class="role-checkbox">
                                <input type="checkbox" name="roles[]" value="admin">
                                <span class="checkmark"></span>
                                Administrators
                            </label>
                        </div>
                        
                        <div class="role-category">
                            <h5><i class="fas fa-user-md"></i> Medical Staff</h5>
                            <label class="role-checkbox">
                                <input type="checkbox" name="roles[]" value="doctor">
                                <span class="checkmark"></span>
                                Doctors
                            </label>
                            <label class="role-checkbox">
                                <input type="checkbox" name="roles[]" value="nurse">
                                <span class="checkmark"></span>
                                Nurses
                            </label>
                        </div>
                        
                        <div class="role-category">
                            <h5><i class="fas fa-flask"></i> Laboratory</h5>
                            <label class="role-checkbox">
                                <input type="checkbox" name="roles[]" value="lab_technician">
                                <span class="checkmark"></span>
                                Lab Technicians
                            </label>
                        </div>
                        
                        <div class="role-category">
                            <h5><i class="fas fa-pills"></i> Pharmacy</h5>
                            <label class="role-checkbox">
                                <input type="checkbox" name="roles[]" value="pharmacy">
                                <span class="checkmark"></span>
                                Pharmacy Staff
                            </label>
                        </div>
                        
                        <div class="role-category">
                            <h5><i class="fas fa-dollar-sign"></i> Financial</h5>
                            <label class="role-checkbox">
                                <input type="checkbox" name="roles[]" value="cashier">
                                <span class="checkmark"></span>
                                Cashiers
                            </label>
                            <label class="role-checkbox">
                                <input type="checkbox" name="roles[]" value="billing">
                                <span class="checkmark"></span>
                                Billing Staff
                            </label>
                        </div>
                        
                        <div class="role-category">
                            <h5><i class="fas fa-boxes"></i> Operations</h5>
                            <label class="role-checkbox">
                                <input type="checkbox" name="roles[]" value="inventory">
                                <span class="checkmark"></span>
                                Inventory Staff
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeCreateGroupModal()">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Group
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    z-index: 10000;
    display: flex;
    justify-content: center;
    align-items: center;
    animation: fadeIn 0.3s ease-in-out;
}

.modal-content {
    background: white;
    border-radius: 12px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    animation: slideIn 0.3s ease-out;
}

.modal-header {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    padding: 20px 25px;
    border-radius: 12px 12px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}

.close-btn {
    background: none;
    border: none;
    color: white;
    font-size: 20px;
    cursor: pointer;
    padding: 5px;
    border-radius: 4px;
    transition: background-color 0.2s;
}

.close-btn:hover {
    background-color: rgba(255, 255, 255, 0.2);
}

.modal-body {
    padding: 25px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #374151;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
}

.roles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 15px;
}

.role-category {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 15px;
    background-color: #f9fafb;
}

.role-category h5 {
    margin: 0 0 12px 0;
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 8px;
}

.role-checkbox {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
    cursor: pointer;
    font-size: 14px;
    position: relative;
}

.role-checkbox input[type="checkbox"] {
    margin-right: 10px;
    transform: scale(1.2);
}

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid #e5e7eb;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background: #2563eb;
    transform: translateY(-1px);
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideIn {
    from { 
        transform: translateY(-30px) scale(0.9);
        opacity: 0;
    }
    to { 
        transform: translateY(0) scale(1);
        opacity: 1;
    }
}
</style>

<script>
function openCreateGroupModal() {
    document.getElementById('createGroupModal').style.display = 'flex';
}

function closeCreateGroupModal() {
    document.getElementById('createGroupModal').style.display = 'none';
    document.getElementById('createGroupForm').reset();
}

// Handle form submission
document.getElementById('createGroupForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const selectedRoles = formData.getAll('roles[]');
    
    if (selectedRoles.length === 0) {
        alert('Please select at least one role to include in the group.');
        return;
    }
    
    const data = {
        name: formData.get('name'),
        patient_no: formData.get('patient_no'),
        roles: selectedRoles
    };
    
    const userRole = '{{ auth()->user()->role }}';
    const baseUrl = userRole === 'nurse' ? '/nurse/chat' : '/doctor/chat';
    
    fetch(`${baseUrl}/create-role-group`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeCreateGroupModal();
            // Redirect to the new chat room
            const userRole = '{{ auth()->user()->role }}';
            const baseUrl = userRole === 'nurse' ? '/nurse/chat' : '/doctor/chat';
            window.location.href = `${baseUrl}/${data.chat_room_id}`;
        } else {
            alert(data.message || 'Failed to create group chat');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while creating the group chat');
    });
});

// Close modal when clicking outside
document.getElementById('createGroupModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCreateGroupModal();
    }
});
</script>

<!-- Create Role Group Modal -->
<div id="createGroupModal" class="group-modal-overlay" style="display: none;">
    <div class="group-modal">
        <div class="group-modal-header">
            <h3><i class="fas fa-users-medical"></i> Create Role-Based Group Chat</h3>
            <button class="close-modal" onclick="closeCreateGroupModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="createGroupForm" onsubmit="createRoleGroup(event)">
            <div class="group-modal-body">
                <div class="form-group">
                    <label for="groupName">Group Name</label>
                    <input type="text" id="groupName" name="name" class="form-control" 
                           placeholder="Enter group name (e.g., Emergency Team, Lab Results Review)" required>
                </div>
                
                <div class="form-group">
                    <label for="patientNo">Patient Number (Optional)</label>
                    <input type="text" id="patientNo" name="patient_no" class="form-control" 
                           placeholder="Enter patient number if group is for specific patient">
                </div>
                
                <div class="form-group">
                    <label>Select Roles to Include:</label>
                    <div class="roles-grid">
                        <label class="role-checkbox">
                            <input type="checkbox" name="roles[]" value="doctor">
                            <span class="checkmark"></span>
                            <i class="fas fa-user-md"></i>
                            Doctors
                        </label>
                        <label class="role-checkbox">
                            <input type="checkbox" name="roles[]" value="nurse">
                            <span class="checkmark"></span>
                            <i class="fas fa-user-nurse"></i>
                            Nurses
                        </label>
                        <label class="role-checkbox">
                            <input type="checkbox" name="roles[]" value="lab_technician">
                            <span class="checkmark"></span>
                            <i class="fas fa-flask"></i>
                            Lab Technicians
                        </label>
                        <label class="role-checkbox">
                            <input type="checkbox" name="roles[]" value="pharmacy">
                            <span class="checkmark"></span>
                            <i class="fas fa-pills"></i>
                            Pharmacy
                        </label>
                        <label class="role-checkbox">
                            <input type="checkbox" name="roles[]" value="cashier">
                            <span class="checkmark"></span>
                            <i class="fas fa-cash-register"></i>
                            Cashiers
                        </label>
                        <label class="role-checkbox">
                            <input type="checkbox" name="roles[]" value="admin">
                            <span class="checkmark"></span>
                            <i class="fas fa-user-shield"></i>
                            Administrators
                        </label>
                        <label class="role-checkbox">
                            <input type="checkbox" name="roles[]" value="inventory">
                            <span class="checkmark"></span>
                            <i class="fas fa-boxes"></i>
                            Inventory
                        </label>
                        <label class="role-checkbox">
                            <input type="checkbox" name="roles[]" value="billing">
                            <span class="checkmark"></span>
                            <i class="fas fa-file-invoice-dollar"></i>
                            Billing
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="group-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeCreateGroupModal()">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary" id="createGroupBtn">
                    <i class="fas fa-users-medical"></i> Create Group
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Role-Based Side Panel -->
<div id="roleSidePanel" class="role-side-panel">
    <div class="role-panel-header">
        <h3><i class="fas fa-users-cog"></i> Quick Role Messages</h3>
        <button class="toggle-panel" onclick="toggleRolePanel()">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
    <div class="role-panel-content">
        <div class="role-quick-access">
            <button class="role-quick-btn" onclick="filterByRole('doctor')">
                <i class="fas fa-user-md"></i>
                <span>Doctors</span>
                <span class="role-count" id="doctorCount">0</span>
            </button>
            <button class="role-quick-btn" onclick="filterByRole('nurse')">
                <i class="fas fa-user-nurse"></i>
                <span>Nurses</span>
                <span class="role-count" id="nurseCount">0</span>
            </button>
            <button class="role-quick-btn" onclick="filterByRole('lab_technician')">
                <i class="fas fa-flask"></i>
                <span>Lab Tech</span>
                <span class="role-count" id="labCount">0</span>
            </button>
            <button class="role-quick-btn" onclick="filterByRole('pharmacy')">
                <i class="fas fa-pills"></i>
                <span>Pharmacy</span>
                <span class="role-count" id="pharmacyCount">0</span>
            </button>
            <button class="role-quick-btn" onclick="filterByRole('all')">
                <i class="fas fa-users"></i>
                <span>All Roles</span>
                <span class="role-count" id="allCount">{{ $chatRooms->count() }}</span>
            </button>
        </div>
        <div class="role-actions">
            <button class="btn btn-sm btn-primary" onclick="openCreateGroupModal()">
                <i class="fas fa-plus"></i> New Group
            </button>
        </div>
    </div>
</div>

<style>
/* Role Side Panel Styles */
.role-side-panel {
    position: fixed;
    right: -300px;
    top: 50%;
    transform: translateY(-50%);
    width: 280px;
    background: white;
    border-radius: 12px 0 0 12px;
    box-shadow: -5px 0 20px rgba(0, 0, 0, 0.1);
    transition: right 0.3s ease;
    z-index: 1000;
    border: 1px solid #e1e5e9;
}

.role-side-panel.open {
    right: 0;
}

.role-panel-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 12px 0 0 0;
}

.role-panel-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}

.toggle-panel {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    padding: 8px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
}

.toggle-panel:hover {
    background: rgba(255, 255, 255, 0.3);
}

.role-panel-content {
    padding: 20px;
}

.role-quick-access {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 20px;
}

.role-quick-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 15px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    text-align: left;
    width: 100%;
}

.role-quick-btn:hover {
    background: #e9ecef;
    border-color: #dee2e6;
    transform: translateY(-1px);
}

.role-quick-btn i {
    color: #6c757d;
    width: 20px;
    text-align: center;
}

.role-quick-btn span:first-of-type {
    flex: 1;
    font-weight: 500;
    color: #495057;
}

.role-count {
    background: #007bff;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    min-width: 20px;
    text-align: center;
}

.role-actions {
    border-top: 1px solid #e9ecef;
    padding-top: 15px;
}

/* Group Modal Styles */
.group-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    z-index: 10000;
    display: flex;
    justify-content: center;
    align-items: center;
    animation: fadeIn 0.3s ease-in-out;
}

.group-modal {
    background: white;
    border-radius: 12px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow: hidden;
    animation: slideIn 0.3s ease-out;
}

.group-modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.group-modal-header h3 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
}

.close-modal {
    background: none;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    padding: 5px;
    border-radius: 4px;
    transition: background 0.2s;
}

.close-modal:hover {
    background: rgba(255, 255, 255, 0.2);
}

.group-modal-body {
    padding: 25px;
    max-height: 60vh;
    overflow-y: auto;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #495057;
}

.form-control {
    width: 100%;
    padding: 12px;
    border: 1px solid #ced4da;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.25);
}

.roles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 10px;
}

.role-checkbox {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    user-select: none;
}

.role-checkbox:hover {
    border-color: #667eea;
    background: #f8f9fa;
}

.role-checkbox input[type="checkbox"] {
    display: none;
}

.checkmark {
    width: 20px;
    height: 20px;
    border: 2px solid #ced4da;
    border-radius: 4px;
    position: relative;
    transition: all 0.2s;
}

.role-checkbox input[type="checkbox"]:checked + .checkmark {
    background: #667eea;
    border-color: #667eea;
}

.role-checkbox input[type="checkbox"]:checked + .checkmark::after {
    content: '✓';
    position: absolute;
    color: white;
    font-size: 14px;
    font-weight: bold;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.role-checkbox input[type="checkbox"]:checked ~ i {
    color: #667eea;
}

.role-checkbox i {
    color: #6c757d;
    transition: color 0.2s;
}

.group-modal-footer {
    padding: 20px 25px;
    background: #f8f9fa;
    display: flex;
    gap: 15px;
    justify-content: flex-end;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover {
    background: #5a6fd8;
    transform: translateY(-1px);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #545b62;
}

.chat-stats {
    display: flex;
    align-items: center;
    gap: 15px;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideIn {
    from { 
        transform: translateY(-30px) scale(0.9);
        opacity: 0;
    }
    to { 
        transform: translateY(0) scale(1);
        opacity: 1;
    }
}
</style>

<script>
let currentRoleFilter = 'all';

// Initialize role panel on page load
document.addEventListener('DOMContentLoaded', function() {
    updateRoleCounts();
    
    // Auto-open side panel after 2 seconds to show the feature
    setTimeout(() => {
        document.getElementById('roleSidePanel').classList.add('open');
    }, 2000);
    
    // Auto-close after 5 seconds if not interacted with
    setTimeout(() => {
        if (!document.getElementById('roleSidePanel').matches(':hover')) {
            document.getElementById('roleSidePanel').classList.remove('open');
        }
    }, 7000);
});

function openCreateGroupModal() {
    document.getElementById('createGroupModal').style.display = 'flex';
    document.getElementById('groupName').focus();
}

function closeCreateGroupModal() {
    document.getElementById('createGroupModal').style.display = 'none';
    document.getElementById('createGroupForm').reset();
}

function toggleRolePanel() {
    const panel = document.getElementById('roleSidePanel');
    panel.classList.toggle('open');
    
    const toggleBtn = panel.querySelector('.toggle-panel i');
    if (panel.classList.contains('open')) {
        toggleBtn.classList.remove('fa-chevron-right');
        toggleBtn.classList.add('fa-chevron-left');
    } else {
        toggleBtn.classList.remove('fa-chevron-left');
        toggleBtn.classList.add('fa-chevron-right');
    }
}

function createRoleGroup(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const roles = formData.getAll('roles[]');
    
    if (roles.length === 0) {
        alert('Please select at least one role to include in the group.');
        return;
    }
    
    const submitBtn = document.getElementById('createGroupBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
    
    // Convert FormData to regular object
    const data = {
        name: formData.get('name'),
        patient_no: formData.get('patient_no'),
        roles: roles
    };
    
    const userRole = '{{ auth()->user()->role }}';
    const baseUrl = userRole === 'nurse' ? '/nurse/chat' : '/doctor/chat';
    
    fetch(`${baseUrl}/create-role-group`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeCreateGroupModal();
            // Redirect to the new chat room
            const userRole = '{{ auth()->user()->role }}';
            const baseUrl = userRole === 'nurse' ? '/nurse/chat' : '/doctor/chat';
            window.location.href = `${baseUrl}/${data.chat_room_id}`;
        } else {
            alert(data.message || 'Failed to create group chat.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while creating the group chat.');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

function filterByRole(role) {
    currentRoleFilter = role;
    const chatCards = document.querySelectorAll('.chat-room-card');
    
    chatCards.forEach(card => {
        if (role === 'all') {
            card.style.display = 'block';
        } else {
            // This is a simplified filter - in a real implementation, 
            // you'd need to store role information in the chat cards
            // For now, we'll show all cards but highlight the filter
            card.style.display = 'block';
        }
    });
    
    // Update active state
    document.querySelectorAll('.role-quick-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    event.target.closest('.role-quick-btn').classList.add('active');
}

function updateRoleCounts() {
    // In a real implementation, you'd make an API call to get actual counts
    // For now, we'll use placeholder counts
    const counts = {
        doctor: {{ \App\Models\ChatRoom::whereJsonContains('participants', [\Auth::id()])->count() }},
        nurse: 0,
        lab: 0,
        pharmacy: 0,
        all: {{ $chatRooms->count() }}
    };
    
    Object.keys(counts).forEach(role => {
        const element = document.getElementById(role + 'Count');
        if (element) {
            element.textContent = counts[role];
        }
    });
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    const modal = document.getElementById('createGroupModal');
    if (e.target === modal) {
        closeCreateGroupModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('createGroupModal');
        if (modal && modal.style.display === 'flex') {
            closeCreateGroupModal();
        }
    }
});

function openChatRoom(roomId) {
    const userRole = '{{ auth()->user()->role }}';
    const baseUrl = userRole === 'nurse' ? '/nurse/chat' : '/doctor/chat';
    window.location.href = `${baseUrl}/${roomId}`;
}

function archiveRoom(roomId) {
    if (confirm('Are you sure you want to archive this conversation? It will no longer be visible in your active chats.')) {
        const userRole = '{{ auth()->user()->role }}';
        const baseUrl = userRole === 'nurse' ? '/nurse/chat' : '/doctor/chat';
        
        fetch(`${baseUrl}/${roomId}/archive`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to archive conversation');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to archive conversation');
        });
    }
}
</script>

@endsection