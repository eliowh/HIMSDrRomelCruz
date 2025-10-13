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