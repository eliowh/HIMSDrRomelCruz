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
                <button class="close-sidebar" type="button" id="closeSidebarBtn">
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

            @if($allUsers->count() > 0)
                <div class="add-participant-section">
                    <h4><i class="fas fa-user-md"></i> Add Doctor</h4>
                    <select id="memberSelect" class="form-control">
                        <option value="">Select a doctor to add...</option>
                        @foreach($allUsers as $userData)
                            <option value="{{ $userData['id'] }}" data-role="{{ strtolower($userData['role']) }}">
                                {{ $userData['name'] }}
                            </option>
                        @endforeach
                    </select>
                    <button class="btn btn-success btn-sm" onclick="addSelectedMember()">
                        <i class="fas fa-user-plus"></i> Add Doctor
                    </button>
                </div>
            @else
                <div class="no-users-available" style="padding: 10px; color: #666;">
                    <em>No other doctors available to add</em>
                </div>
            @endif
        </div>

        <!-- Messages Area -->
        <div class="messages-container">
            <div class="messages-list" id="messagesList">
                @foreach($chatRoom->messages as $message)
                    <div class="message {{ $message->user_id === auth()->id() ? 'own-message' : 'other-message' }} {{ $message->isSystemMessage() ? 'system-message' : '' }}" data-message-id="{{ $message->id }}">
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
let lastMessageId = 0;
let isRefreshing = false;

// Auto-scroll to bottom on page load
document.addEventListener('DOMContentLoaded', function() {
    scrollToBottom();
    document.getElementById('messageInput').focus();
    
    // Set initial last message ID
    const messages = document.querySelectorAll('.message');
    if (messages.length > 0) {
        const lastMessage = messages[messages.length - 1];
        const messageId = lastMessage.dataset.messageId;
        if (messageId) {
            lastMessageId = parseInt(messageId);
        }
    }
    
    // Add event listener for close button
    const closeBtn = document.getElementById('closeSidebarBtn');
    if (closeBtn) {
        console.log('Close button found, adding event listener');
        closeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Close button clicked!');
            toggleParticipants();
        });
    } else {
        console.error('Close button not found!');
    }
    
    // Also add event listener to the icon inside the button as backup
    const closeIcon = closeBtn?.querySelector('i');
    if (closeIcon) {
        closeIcon.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Close icon clicked!');
            toggleParticipants();
        });
    }
    
    // Add click outside to close functionality
    document.addEventListener('click', function(e) {
        const sidebar = document.getElementById('participantsSidebar');
        const manageBtn = document.querySelector('[onclick="toggleParticipants()"]');
        
        if (sidebar && sidebar.classList.contains('open')) {
            // Check if click is outside sidebar and not on the manage button
            if (!sidebar.contains(e.target) && !manageBtn?.contains(e.target)) {
                console.log('Clicked outside sidebar, closing...');
                sidebar.classList.remove('open');
            }
        }
    });
});

function scrollToBottom() {
    const messagesList = document.getElementById('messagesList');
    messagesList.scrollTop = messagesList.scrollHeight;
}

function toggleParticipants() {
    console.log('toggleParticipants called');
    const sidebar = document.getElementById('participantsSidebar');
    if (sidebar) {
        const isOpen = sidebar.classList.contains('open');
        console.log('Sidebar is currently open:', isOpen);
        
        if (isOpen) {
            sidebar.classList.remove('open');
            console.log('Sidebar closed');
        } else {
            sidebar.classList.add('open');
            console.log('Sidebar opened');
        }
    } else {
        console.error('Sidebar element not found!');
    }
}

// Show error message in a non-blocking way
function showMessageError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'message-error-notification';
    errorDiv.innerHTML = `
        <div class="error-content">
            <i class="fas fa-exclamation-triangle"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="error-close">×</button>
        </div>
    `;
    
    // Add some basic styling
    errorDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #ef4444;
        color: white;
        padding: 12px 16px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        z-index: 1000;
        max-width: 300px;
    `;
    
    errorDiv.querySelector('.error-content').style.cssText = `
        display: flex;
        align-items: center;
        gap: 8px;
    `;
    
    errorDiv.querySelector('.error-close').style.cssText = `
        background: none;
        border: none;
        color: white;
        font-size: 18px;
        cursor: pointer;
        margin-left: auto;
    `;
    
    document.body.appendChild(errorDiv);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (errorDiv.parentElement) {
            errorDiv.remove();
        }
    }, 5000);
}

function sendMessage(event) {
    event.preventDefault();
    
    const messageInput = document.getElementById('messageInput');
    const sendBtn = document.getElementById('sendBtn');
    const message = messageInput.value.trim();
    
    if (!message) return;
    
    // Disable input while sending and prevent auto-refresh conflicts
    messageInput.disabled = true;
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    isRefreshing = true;
    
    fetch(`/doctor/chat/${chatRoomId}/message`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ message: message })
    })
    .then(async response => {
        const contentType = response.headers.get('content-type');
        
        if (!response.ok) {
            // Log the response for debugging
            const errorText = await response.text();
            console.error('Server error:', response.status, errorText);
            throw new Error(`Server returned ${response.status}: ${errorText.substring(0, 100)}`);
        }
        
        if (!contentType || !contentType.includes('application/json')) {
            const responseText = await response.text();
            console.error('Non-JSON response:', responseText);
            throw new Error('Server returned non-JSON response');
        }
        
        return response.json();
    })
    .then(data => {
        console.log('Message sent successfully:', data);
        
        if (data.success) {
            // Add message to UI immediately
            addMessageToUI(data.message, data.formatted_time, true);
            messageInput.value = '';
            scrollToBottom();
        } else {
            console.error('Server returned success=false:', data);
            throw new Error(data.message || 'Server returned success=false');
        }
    })
    .catch(error => {
        console.error('Error sending message:', error);
        
        // Show a more user-friendly error message
        const errorMsg = error.message.includes('fetch') 
            ? 'Network error. Please check your connection.' 
            : 'Failed to send message. Please try again.';
            
        // Use a non-blocking notification instead of alert
        showMessageError(errorMsg);
        
        // Don't clear the message input on error so user can retry
    })
    .finally(() => {
        // Re-enable input and allow auto-refresh
        messageInput.disabled = false;
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
        messageInput.focus();
        isRefreshing = false;
    });
}

function addMessageToUI(message, formattedTime, isOwnMessage) {
    const messagesList = document.getElementById('messagesList');
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${isOwnMessage ? 'own-message' : 'other-message'}`;
    messageDiv.setAttribute('data-message-id', message.id);
    
    messageDiv.innerHTML = `
        <div class="message-header">
            <strong class="sender-name">${message.user.name}</strong>
            <span class="sender-role">${message.user.role.charAt(0).toUpperCase() + message.user.role.slice(1)}</span>
            <span class="message-time">now</span>
        </div>
        <div class="message-content">${message.message}</div>
    `;
    
    messagesList.appendChild(messageDiv);
    
    // Update last message ID
    lastMessageId = Math.max(lastMessageId, message.id);
}

function addSelectedMember() {
    const memberSelect = document.getElementById('memberSelect');
    const memberId = memberSelect.value;
    
    if (!memberId) {
        alert('Please select a doctor to add');
        return;
    }
    
    const selectedOption = memberSelect.options[memberSelect.selectedIndex];
    const memberRole = selectedOption.getAttribute('data-role');
    
    // Disable the button and show loading state
    const addButton = document.querySelector('button[onclick="addSelectedMember()"]');
    const originalText = addButton.innerHTML;
    addButton.disabled = true;
    addButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    
    addParticipant(memberId, memberRole, function() {
        // Re-enable button on completion
        addButton.disabled = false;
        addButton.innerHTML = originalText;
    });
}

function addParticipant(userId, userRole, callback) {
    fetch(`/doctor/chat/${chatRoomId}/add-participant`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ user_id: userId })
    })
    .then(response => {
        return response.json().then(data => {
            if (callback) callback();
            if (response.ok && data.success) {
                location.reload(); // Reload to update participants list
            } else {
                // Show appropriate error message
                const errorMessage = data.message || 'Unknown error occurred';
                alert('Error: ' + errorMessage);
            }
        }).catch(jsonError => {
            if (callback) callback();
            // Handle JSON parsing errors - might still be successful
            if (response.ok) {
                // If response is OK but JSON parsing failed, assume success
                location.reload();
            } else {
                console.error('JSON parsing error:', jsonError);
                alert('Error processing server response');
            }
        });
    })
    .catch(error => {
        if (callback) callback();
        console.error('Network Error:', error);
        alert('Network error occurred. Please try again.');
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

// Auto-refresh messages every 10 seconds, but not while sending
setInterval(function() {
    if (!isRefreshing) {
        refreshMessages();
    }
}, 10000);

function refreshMessages() {
    if (isRefreshing) return;
    
    isRefreshing = true;
    
    fetch(`/doctor/chat/${chatRoomId}/messages`)
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            updateMessagesUI(data.messages);
        }
    })
    .catch(error => {
        console.error('Error refreshing messages:', error);
        // Don't show error for background refresh failures
    })
    .finally(() => {
        isRefreshing = false;
    });
}

function updateMessagesUI(messages) {
    const messagesList = document.getElementById('messagesList');
    const wasAtBottom = messagesList.scrollTop + messagesList.clientHeight >= messagesList.scrollHeight - 50;
    
    // Only add new messages that we don't have yet
    const newMessages = messages.filter(msg => msg.id > lastMessageId);
    
    if (newMessages.length > 0) {
        newMessages.forEach(message => {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${message.is_own_message ? 'own-message' : 'other-message'} ${message.message_type === 'system' ? 'system-message' : ''}`;
            messageDiv.setAttribute('data-message-id', message.id);
            
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
            lastMessageId = Math.max(lastMessageId, message.id);
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