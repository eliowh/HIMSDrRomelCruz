@extends('layouts.doctor')

@section('title', 'Messages - Stream Chat')

@section('content')
<link rel="stylesheet" href="{{ asset('css/chat/chat.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="chat-container">
    <div class="chat-header">
        <h2><i class="fas fa-comments"></i> Messages (Stream Chat)</h2>
        <div class="chat-stats">
            <span class="stat"><span id="channelCount">{{ count($channels) }}</span> conversations</span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Stream Chat will render here -->
    <div id="stream-chat-container" style="height: 600px;">
        <div class="loading-state">
            <i class="fas fa-spinner fa-spin"></i> Loading chat...
        </div>
    </div>
</div>

<!-- Stream Chat SDK -->
<script src="https://cdn.jsdelivr.net/npm/stream-chat@8.40.0/dist/browser/index.umd.js"></script>

<script>
// Stream Chat Configuration
const apiKey = '{{ $apiKey }}';
const userId = '{{ $userId }}';
const userName = '{{ $userName }}';
const userToken = '{{ $userToken }}';

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
        
        console.log('Stream Chat connected successfully');
        
        // Create channel list
        renderChannelList();
        
        // Hide loading state
        document.querySelector('.loading-state').style.display = 'none';
        
    } catch (error) {
        console.error('Stream Chat initialization error:', error);
        document.getElementById('stream-chat-container').innerHTML = 
            '<div class="error-state"><i class="fas fa-exclamation-triangle"></i> Failed to load chat. Please refresh the page.</div>';
    }
}

async function renderChannelList() {
    try {
        // Query patient consultation channels
        const filter = { 
            type: 'patient_consultation', 
            members: { $in: [userId] } 
        };
        const sort = { last_message_at: -1 };
        const channels = await chatClient.queryChannels(filter, sort);
        
        let channelListHTML = '';
        
        if (channels.length === 0) {
            channelListHTML = `
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
            `;
        } else {
            channelListHTML = '<div class="chat-rooms-grid">';
            
            channels.forEach(channel => {
                const patientId = channel.data.custom?.patient_id || 'Unknown';
                const channelName = channel.data.name || `Patient ${patientId}`;
                const memberCount = Object.keys(channel.state.members).length;
                const lastMessage = channel.state.messages[channel.state.messages.length - 1];
                const lastActivity = channel.data.last_message_at ? 
                    new Date(channel.data.last_message_at).toLocaleString() : 'No activity';
                
                let lastMessageText = 'No messages yet';
                if (lastMessage) {
                    const sender = lastMessage.user?.name || 'Unknown';
                    const message = lastMessage.text || 'Message';
                    lastMessageText = `${sender}: ${message.length > 80 ? message.substring(0, 80) + '...' : message}`;
                }
                
                channelListHTML += `
                    <div class="chat-room-card" onclick="openStreamChannel('${channel.type}', '${channel.id}')">
                        <div class="room-header">
                            <div class="room-info">
                                <h3 class="room-name">${channelName}</h3>
                                <span class="patient-info">Patient #${patientId}</span>
                            </div>
                            <div class="room-meta">
                                <span class="last-activity">${lastActivity}</span>
                                <span class="participant-count">
                                    <i class="fas fa-users"></i> ${memberCount}
                                </span>
                            </div>
                        </div>
                        <div class="last-message">
                            ${lastMessageText}
                        </div>
                        <div class="room-actions">
                            <button class="btn btn-primary btn-sm" onclick="event.stopPropagation(); openStreamChannel('${channel.type}', '${channel.id}')">
                                <i class="fas fa-comments"></i> Open Chat
                            </button>
                        </div>
                    </div>
                `;
            });
            
            channelListHTML += '</div>';
        }
        
        document.getElementById('stream-chat-container').innerHTML = channelListHTML;
        document.getElementById('channelCount').textContent = channels.length;
        
    } catch (error) {
        console.error('Error rendering channel list:', error);
        document.getElementById('stream-chat-container').innerHTML = 
            '<div class="error-state">Failed to load conversations</div>';
    }
}

function openStreamChannel(channelType, channelId) {
    window.location.href = `/doctor/stream-chat/${channelType}/${channelId}`;
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeStreamChat();
});

// Auto-refresh channel list every 30 seconds
setInterval(renderChannelList, 30000);
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

.chat-rooms-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
    padding: 20px;
}

.chat-room-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 16px;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.chat-room-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    border-color: #007bff;
}

.room-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}

.room-name {
    margin: 0 0 4px 0;
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

.patient-info {
    font-size: 12px;
    color: #666;
    background: #f8f9fa;
    padding: 2px 6px;
    border-radius: 4px;
}

.room-meta {
    text-align: right;
    font-size: 12px;
    color: #666;
}

.participant-count {
    display: block;
    margin-top: 4px;
}

.last-message {
    font-size: 13px;
    color: #555;
    margin-bottom: 12px;
    line-height: 1.4;
}

.room-actions {
    border-top: 1px solid #f0f0f0;
    padding-top: 12px;
    text-align: right;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-icon {
    font-size: 48px;
    color: #ddd;
    margin-bottom: 20px;
}

.empty-state h3 {
    color: #333;
    margin-bottom: 10px;
}

.empty-state p {
    color: #666;
    margin-bottom: 20px;
}
</style>

@endsection