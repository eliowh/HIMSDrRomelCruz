@extends('layouts.doctor')

@section('title', 'Chat - Stream Chat')

@section('content')
<link rel="stylesheet" href="{{ asset('css/chat/chat.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="chat-container">
    <!-- Chat Header -->
    <div class="chat-header">
        <div class="chat-title">
            <a href="{{ route('stream-chat.index') }}" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="title-info">
                <h2 id="channelName">Loading...</h2>
                <span class="patient-info" id="patientInfo">Patient consultation</span>
            </div>
        </div>
        <div class="chat-actions">
            <button class="btn btn-outline" onclick="toggleParticipants()">
                <i class="fas fa-users"></i> 
                <span class="participants-count" id="participantCount">0</span>
            </button>
        </div>
    </div>

    <div class="chat-layout">
        <!-- Main Chat Area -->
        <div class="chat-main">
            <!-- Stream Chat UI will render here -->
            <div id="stream-chat-ui" style="height: 500px;">
                <div class="loading-state">
                    <i class="fas fa-spinner fa-spin"></i> Loading chat...
                </div>
            </div>
        </div>

        <!-- Participants Sidebar -->
        <div class="participants-sidebar" id="participantsSidebar">
            <div class="participants-header">
                <h3>Participants</h3>
                <button class="close-sidebar" type="button" id="closeSidebarBtn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="participants-list" id="participantsList">
                <!-- Participants will be loaded here -->
            </div>

            <div class="add-participant-section">
                <h4><i class="fas fa-user-md"></i> Add Doctor</h4>
                <select id="doctorSelect" class="form-control">
                    <option value="">Select a doctor to add...</option>
                </select>
                <button class="btn btn-success btn-sm" onclick="addSelectedDoctor()">
                    <i class="fas fa-user-plus"></i> Add Doctor
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Close Sidebar Button -->
<button class="close-sidebar-btn" id="closeSidebarBtn" onclick="toggleParticipants()">
    <i class="fas fa-times"></i>
</button>

<!-- Stream Chat SDK -->
<script src="https://cdn.jsdelivr.net/npm/stream-chat@8.40.0/dist/browser/index.umd.js"></script>

<script>
// Stream Chat Configuration
const apiKey = '{{ $apiKey }}';
const userId = '{{ $userId }}';
const userName = '{{ $userName }}';
const userToken = '{{ $userToken }}';
const channelType = '{{ $channelType }}';
const channelId = '{{ $channelId }}';

// Initialize Stream Chat
let chatClient = null;
let channel = null;

async function initializeStreamChat() {
    try {
        // Connect to Stream Chat
        chatClient = StreamChat.getInstance(apiKey);
        
        const user = {
            id: userId,
            name: userName,
            role: 'doctor'
        };

        await chatClient.connectUser(user, userToken);
        
        // Get the specific channel
        channel = chatClient.channel(channelType, channelId);
        await channel.watch();
        
        console.log('Stream Chat connected and watching channel');
        
        // Update UI with channel info
        updateChannelInfo();
        updateParticipantsList();
        
        // Render the chat UI
        renderChatUI();
        
        // Load available doctors
        loadAvailableDoctors();
        
        // Listen for member changes
        channel.on('member.added', updateParticipantsList);
        channel.on('member.removed', updateParticipantsList);
        
    } catch (error) {
        console.error('Stream Chat initialization error:', error);
        document.getElementById('stream-chat-ui').innerHTML = 
            '<div class="error-state"><i class="fas fa-exclamation-triangle"></i> Failed to load chat. Please refresh the page.</div>';
    }
}

function renderChatUI() {
    // Create a simple message list and input
    const chatContainer = document.getElementById('stream-chat-ui');
    
    chatContainer.innerHTML = `
        <div class="messages-container" id="messagesContainer" style="height: 400px; overflow-y: auto; border: 1px solid #e0e0e0; padding: 16px; margin-bottom: 16px;">
            <!-- Messages will be rendered here -->
        </div>
        <div class="message-input-container">
            <div class="input-group">
                <input type="text" id="messageInput" class="form-control" placeholder="Type your message..." maxlength="1000">
                <button type="button" class="btn btn-primary" onclick="sendMessage()">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    `;
    
    // Load existing messages
    loadMessages();
    
    // Listen for new messages
    channel.on('message.new', (event) => {
        appendMessage(event.message);
        scrollToBottom();
    });
    
    // Handle Enter key
    document.getElementById('messageInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
}

async function loadMessages() {
    try {
        const response = await channel.query({
            messages: { limit: 50 }
        });
        
        const messagesContainer = document.getElementById('messagesContainer');
        messagesContainer.innerHTML = '';
        
        response.messages.forEach(message => {
            appendMessage(message);
        });
        
        scrollToBottom();
    } catch (error) {
        console.error('Error loading messages:', error);
    }
}

function appendMessage(message) {
    const messagesContainer = document.getElementById('messagesContainer');
    const isOwnMessage = message.user.id === userId;
    const messageTime = new Date(message.created_at).toLocaleTimeString();
    
    const messageHTML = `
        <div class="message ${isOwnMessage ? 'own-message' : 'other-message'}">
            <div class="message-header">
                <span class="sender-name">${message.user.name}</span>
                <span class="message-time">${messageTime}</span>
            </div>
            <div class="message-content">
                ${message.text || ''}
            </div>
        </div>
    `;
    
    messagesContainer.insertAdjacentHTML('beforeend', messageHTML);
}

async function sendMessage() {
    const messageInput = document.getElementById('messageInput');
    const messageText = messageInput.value.trim();
    
    if (!messageText) return;
    
    try {
        await channel.sendMessage({
            text: messageText,
            user_id: userId
        });
        
        messageInput.value = '';
    } catch (error) {
        console.error('Error sending message:', error);
        alert('Failed to send message. Please try again.');
    }
}

function updateChannelInfo() {
    const channelName = channel.data.name || `Channel ${channelId}`;
    const patientId = channel.data.custom?.patient_id || 'Unknown';
    
    document.getElementById('channelName').textContent = channelName;
    document.getElementById('patientInfo').textContent = `Patient #${patientId}`;
}

function updateParticipantsList() {
    const members = Object.values(channel.state.members);
    const participantsList = document.getElementById('participantsList');
    const participantCount = document.getElementById('participantCount');
    
    participantCount.textContent = members.length;
    
    let participantsHTML = '';
    members.forEach(member => {
        const isCreator = member.user_id === channel.data.created_by_id;
        const isCurrentUser = member.user_id === userId;
        
        participantsHTML += `
            <div class="participant-item">
                <div class="participant-info">
                    <strong>${member.user.name}</strong>
                    <span class="participant-role">${member.user.role || 'Doctor'}</span>
                    ${isCreator ? '<span class="creator-badge">Creator</span>' : ''}
                </div>
                ${!isCreator && !isCurrentUser ? `
                    <button class="btn btn-danger btn-xs" onclick="removeParticipant('${member.user_id}')">
                        <i class="fas fa-user-minus"></i>
                    </button>
                ` : ''}
            </div>
        `;
    });
    
    participantsList.innerHTML = participantsHTML;
}

async function loadAvailableDoctors() {
    try {
        const response = await fetch(`/doctor/stream-chat/${channelType}/${channelId}/available-doctors`);
        const data = await response.json();
        
        const doctorSelect = document.getElementById('doctorSelect');
        doctorSelect.innerHTML = '<option value="">Select a doctor to add...</option>';
        
        data.doctors.forEach(doctor => {
            doctorSelect.innerHTML += `<option value="${doctor.id}">${doctor.name}</option>`;
        });
    } catch (error) {
        console.error('Error loading doctors:', error);
    }
}

async function addSelectedDoctor() {
    const doctorSelect = document.getElementById('doctorSelect');
    const doctorId = doctorSelect.value;
    
    if (!doctorId) {
        alert('Please select a doctor to add');
        return;
    }
    
    try {
        const response = await fetch(`/doctor/stream-chat/${channelType}/${channelId}/add-participant`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ user_id: doctorId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Refresh the channel to get updated members
            await channel.query();
            updateParticipantsList();
            loadAvailableDoctors();
            
            // Reset selection
            doctorSelect.value = '';
        } else {
            alert('Error: ' + (data.message || 'Failed to add participant'));
        }
    } catch (error) {
        console.error('Error adding participant:', error);
        alert('Network error occurred. Please try again.');
    }
}

async function removeParticipant(userId) {
    if (!confirm('Are you sure you want to remove this participant?')) {
        return;
    }
    
    try {
        const response = await fetch(`/doctor/stream-chat/${channelType}/${channelId}/remove-participant`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ user_id: userId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Refresh the channel to get updated members
            await channel.query();
            updateParticipantsList();
            loadAvailableDoctors();
        } else {
            alert('Error: ' + (data.message || 'Failed to remove participant'));
        }
    } catch (error) {
        console.error('Error removing participant:', error);
        alert('Network error occurred. Please try again.');
    }
}

function scrollToBottom() {
    const container = document.getElementById('messagesContainer');
    container.scrollTop = container.scrollHeight;
}

function toggleParticipants() {
    const sidebar = document.getElementById('participantsSidebar');
    const closeBtn = document.getElementById('closeSidebarBtn');
    const isVisible = sidebar.classList.contains('visible');
    
    if (isVisible) {
        sidebar.classList.remove('visible');
        closeBtn.style.display = 'none';
    } else {
        sidebar.classList.add('visible');
        closeBtn.style.display = 'block';
    }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeStreamChat();
});

// Clean up on page unload
window.addEventListener('beforeunload', function() {
    if (chatClient) {
        chatClient.disconnectUser();
    }
});
</script>

<style>
.loading-state, .error-state {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 400px;
    font-size: 18px;
    color: #666;
}

.error-state {
    color: #dc3545;
}

.message {
    margin-bottom: 16px;
    padding: 12px;
    border-radius: 8px;
    max-width: 70%;
}

.own-message {
    background: #007bff;
    color: white;
    margin-left: auto;
    border-bottom-right-radius: 4px;
}

.other-message {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-bottom-left-radius: 4px;
}

.message-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
    font-size: 12px;
}

.own-message .message-header {
    color: rgba(255,255,255,0.8);
}

.other-message .message-header {
    color: #666;
}

.sender-name {
    font-weight: 600;
}

.message-content {
    line-height: 1.4;
}

.participants-sidebar {
    width: 300px;
    background: white;
    border-left: 1px solid #e0e0e0;
    transform: translateX(100%);
    transition: transform 0.3s ease;
    position: fixed;
    right: 0;
    top: 0;
    height: 100vh;
    z-index: 1000;
    overflow-y: auto;
    padding: 20px;
}

.participants-sidebar.visible {
    transform: translateX(0);
}

.participants-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e0e0e0;
}

.close-sidebar {
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    color: #666;
}

.participant-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.participant-info {
    flex: 1;
}

.participant-role {
    display: block;
    font-size: 12px;
    color: #666;
}

.creator-badge {
    font-size: 10px;
    background: #28a745;
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    margin-left: 8px;
}

.add-participant-section {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e0e0e0;
}

.add-participant-section h4 {
    margin-bottom: 12px;
    font-size: 14px;
}

.add-participant-section select {
    width: 100%;
    margin-bottom: 8px;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.close-sidebar-btn {
    display: none;
    position: fixed;
    top: 20px;
    right: 320px;
    z-index: 1001;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    cursor: pointer;
}
</style>

@endsection