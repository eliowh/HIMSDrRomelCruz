@extends('layouts.doctor')

@section('title', 'Chat - ' . $chatRoom->name)

@section('content')
<link rel="stylesheet" href="{{ asset('css/chat/chat.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="chat-room-container">
    <!-- Chat Header -->
    <div class="chat-room-header">
        <div class="header-left">
            <a href="{{ route('chat.index') }}" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="room-info">
                <h2>{{ $chatRoom->name }}</h2>
                <div class="room-details">
                    @if($chatRoom->patient)
                        <span class="patient-info">
                            <i class="fas fa-user"></i>
                            Patient #{{ $chatRoom->patient_no }} - {{ $chatRoom->patient->display_name }}
                        </span>
                    @endif
                    <span class="participant-count">
                        <i class="fas fa-users"></i>
                        {{ count($chatRoom->participants ?? []) }} participants
                    </span>
                </div>
            </div>
        </div>
        <div class="header-actions">
            <button class="btn btn-secondary btn-sm" onclick="toggleParticipants()">
                <i class="fas fa-users-cog"></i> Manage Participants
            </button>
        </div>
    </div>

    <div class="chat-room-body">
        <!-- Participants Sidebar -->
        <div class="participants-sidebar" id="participantsSidebar">
            <div class="participants-header">
                <h3>Participants</h3>
                <button class="close-sidebar" onclick="toggleParticipants()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="participants-list">
                @foreach($chatRoom->getParticipantUsers() as $participant)
                    <div class="participant-item">
                        <div class="participant-info">
                            <strong>{{ $participant->name }}</strong>
                            <span class="participant-role">{{ ucfirst($participant->role) }}</span>
                            @if($participant->id === $chatRoom->created_by)
                                <span class="creator-badge">Creator</span>
                            @endif
                        </div>
                        @if($chatRoom->created_by === auth()->id() && $participant->id !== $chatRoom->created_by)
                            <button class="btn btn-danger btn-xs" onclick="removeParticipant({{ $participant->id }})">
                                <i class="fas fa-user-minus"></i>
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>

            @if($doctors->count() > 0)
                <div class="add-participant-section">
                    <h4>Add Doctor</h4>
                    <select id="doctorSelect" class="form-control">
                        <option value="">Select a doctor to add...</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor['id'] }}">{{ $doctor['name'] }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-primary btn-sm" onclick="addSelectedDoctor()">
                        <i class="fas fa-user-plus"></i> Add Doctor
                    </button>
                </div>
            @endif
        </div>

        <!-- Messages Area -->
        <div class="messages-container">
            <div class="messages-list" id="messagesList">
                @foreach($chatRoom->messages as $message)
                    <div class="message {{ $message->user_id === auth()->id() ? 'own-message' : 'other-message' }} {{ $message->isSystemMessage() ? 'system-message' : '' }}">
                        @if(!$message->isSystemMessage())
                            <div class="message-header">
                                <strong class="sender-name">{{ $message->sender_name }}</strong>
                                <span class="sender-role">{{ $message->sender_role }}</span>
                                <span class="message-time">{{ $message->short_time }}</span>
                            </div>
                        @endif
                        <div class="message-content">
                            {{ $message->message }}
                        </div>
                        @if($message->isSystemMessage())
                            <div class="message-time">{{ $message->short_time }}</div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Message Input -->
            <div class="message-input-container">
                <form id="messageForm" onsubmit="sendMessage(event)">
                    <div class="input-group">
                        <input type="text" id="messageInput" class="form-control" placeholder="Type your message..." maxlength="1000" required>
                        <button type="submit" class="btn btn-primary" id="sendBtn">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const chatRoomId = {{ $chatRoom->id }};
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Auto-scroll to bottom on page load
document.addEventListener('DOMContentLoaded', function() {
    scrollToBottom();
    document.getElementById('messageInput').focus();
});

function scrollToBottom() {
    const messagesList = document.getElementById('messagesList');
    messagesList.scrollTop = messagesList.scrollHeight;
}

function toggleParticipants() {
    const sidebar = document.getElementById('participantsSidebar');
    sidebar.classList.toggle('open');
}

function sendMessage(event) {
    event.preventDefault();
    
    const messageInput = document.getElementById('messageInput');
    const sendBtn = document.getElementById('sendBtn');
    const message = messageInput.value.trim();
    
    if (!message) return;
    
    // Disable input while sending
    messageInput.disabled = true;
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    fetch(`/doctor/chat/${chatRoomId}/message`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ message: message })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Add message to UI
            addMessageToUI(data.message, data.formatted_time, true);
            messageInput.value = '';
            scrollToBottom();
        } else {
            alert('Failed to send message');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to send message');
    })
    .finally(() => {
        // Re-enable input
        messageInput.disabled = false;
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
        messageInput.focus();
    });
}

function addMessageToUI(message, formattedTime, isOwnMessage) {
    const messagesList = document.getElementById('messagesList');
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${isOwnMessage ? 'own-message' : 'other-message'}`;
    
    messageDiv.innerHTML = `
        <div class="message-header">
            <strong class="sender-name">${message.user.name}</strong>
            <span class="sender-role">${message.user.role.charAt(0).toUpperCase() + message.user.role.slice(1)}</span>
            <span class="message-time">now</span>
        </div>
        <div class="message-content">${message.message}</div>
    `;
    
    messagesList.appendChild(messageDiv);
}

function addSelectedDoctor() {
    const doctorSelect = document.getElementById('doctorSelect');
    const doctorId = doctorSelect.value;
    
    if (!doctorId) {
        alert('Please select a doctor to add');
        return;
    }
    
    fetch(`/doctor/chat/${chatRoomId}/add-participant`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ user_id: doctorId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Reload to update participants list
        } else {
            alert('Failed to add participant');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to add participant');
    });
}

function removeParticipant(userId) {
    if (confirm('Are you sure you want to remove this participant?')) {
        fetch(`/doctor/chat/${chatRoomId}/remove-participant`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ user_id: userId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Reload to update participants list
            } else {
                alert(data.message || 'Failed to remove participant');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to remove participant');
        });
    }
}

// Auto-refresh messages every 10 seconds
setInterval(function() {
    refreshMessages();
}, 10000);

function refreshMessages() {
    fetch(`/doctor/chat/${chatRoomId}/messages`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateMessagesUI(data.messages);
        }
    })
    .catch(error => {
        console.error('Error refreshing messages:', error);
    });
}

function updateMessagesUI(messages) {
    const messagesList = document.getElementById('messagesList');
    const currentMessages = messagesList.children.length;
    
    // Only update if there are new messages
    if (messages.length > currentMessages) {
        const wasAtBottom = messagesList.scrollTop + messagesList.clientHeight >= messagesList.scrollHeight - 10;
        
        // Clear and rebuild messages
        messagesList.innerHTML = '';
        
        messages.forEach(message => {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${message.is_own_message ? 'own-message' : 'other-message'} ${message.message_type === 'system' ? 'system-message' : ''}`;
            
            if (message.message_type !== 'system') {
                messageDiv.innerHTML = `
                    <div class="message-header">
                        <strong class="sender-name">${message.user_name}</strong>
                        <span class="sender-role">${message.user_role}</span>
                        <span class="message-time">${message.short_time}</span>
                    </div>
                    <div class="message-content">${message.message}</div>
                `;
            } else {
                messageDiv.innerHTML = `
                    <div class="message-content">${message.message}</div>
                    <div class="message-time">${message.short_time}</div>
                `;
            }
            
            messagesList.appendChild(messageDiv);
        });
        
        // Scroll to bottom if user was at bottom before
        if (wasAtBottom) {
            scrollToBottom();
        }
    }
}

// Handle Enter key for sending messages
document.getElementById('messageInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage(e);
    }
});
</script>

@endsection