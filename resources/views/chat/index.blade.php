@extends('layouts.doctor')

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
            <a href="{{ route('doctor.patients') }}" class="btn btn-primary">
                <i class="fas fa-user-md"></i> Go to Patients
            </a>
        </div>
    @endif
</div>

<script>
function openChatRoom(roomId) {
    window.location.href = `/doctor/chat/${roomId}`;
}

function archiveRoom(roomId) {
    if (confirm('Are you sure you want to archive this conversation? It will no longer be visible in your active chats.')) {
        fetch(`/doctor/chat/${roomId}/archive`, {
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