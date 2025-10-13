@extends(auth()->user()->role === 'nurse' ? 'layouts.nurse' : 'layouts.doctor')

@section('title', 'Chat - ' . $chatRoom->name)

@section('content')
<link rel="stylesheet" href="{{ asset('css/chat/chat.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="chat-room-container">
    <!-- Chat Header -->
    <div class="chat-room-header">
        <div class="header-left">
            <a href="{{ auth()->user()->role === 'nurse' ? '/nurse/chat' : '/doctor/chat' }}" class="back-btn">
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
                <!-- Debug: Total participants: {{ count($chatRoom->getParticipantUsers()) }} -->
                <!-- Debug: Participant IDs in room: {{ json_encode($chatRoom->participants ?? []) }} -->
                @foreach($chatRoom->getParticipantUsers() as $participant)
                    <div class="participant-item">
                        <div class="participant-info">
                            <strong>{{ $participant->name ?? 'Unknown User' }}</strong>
                            <span class="participant-role">{{ ucfirst($participant->role ?? 'unknown') }}</span>
                            @if($participant->id === $chatRoom->created_by)
                                <span class="creator-badge">Creator</span>
                            @endif
                            <!-- Debug info: ID={{ $participant->id ?? 'NULL' }} -->
                        </div>
                        @if($chatRoom->created_by === auth()->id() && $participant->id && $participant->id !== $chatRoom->created_by)
                            <button type="button" class="btn btn-danger btn-xs btn-remove-participant" data-user-id="{{ $participant->id }}" title="Remove {{ $participant->name }} (ID: {{ $participant->id }})">
                                <i class="fas fa-user-minus"></i>
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>

            @php
                // Get all users that can be added (excluding current user and existing participants)
                $currentParticipantIds = $chatRoom->participants ?? [];
                
                // Check chat room type to determine which roles can be added
                if ($chatRoom->room_type === 'doctor_group_consultation') {
                    // For doctor group chats, only show other doctors
                    $availableUsers = \App\Models\User::whereNotIn('id', $currentParticipantIds)
                        ->where('id', '!=', auth()->user()->id)
                        ->where('role', 'doctor') // Only doctors
                        ->select('id', 'name', 'role')
                        ->orderBy('name')
                        ->get();
                } else {
                    // For regular patient chats, show all healthcare roles
                    $availableUsers = \App\Models\User::whereNotIn('id', $currentParticipantIds)
                        ->where('id', '!=', auth()->user()->id)
                        ->whereIn('role', ['admin', 'doctor', 'nurse', 'lab_technician', 'pharmacy', 'cashier', 'inventory', 'billing'])
                        ->select('id', 'name', 'role')
                        ->orderBy('role')
                        ->orderBy('name')
                        ->get();
                }
                    
                // Group them by role for categorized display
                $groupedUsers = $availableUsers->groupBy('role');
                $roleOrder = ['admin', 'doctor', 'nurse', 'lab_technician', 'pharmacy', 'cashier', 'inventory', 'billing'];
                $roleLabels = [
                    'admin' => '👑 Administrators',
                    'doctor' => '👨‍⚕️ Doctors',
                    'nurse' => '👩‍⚕️ Nurses', 
                    'lab_technician' => '🔬 Lab Technicians',
                    'pharmacy' => '💊 Pharmacy Staff',
                    'cashier' => '💰 Cashiers',
                    'inventory' => '📦 Inventory Staff',
                    'billing' => '🧾 Billing Staff'
                ];
            @endphp
            
            @if($availableUsers->count() > 0)
                <div class="add-participant-section">
                    <h4><i class="fas fa-user-plus"></i> Add Team Member</h4>
                    <p class="available-count">{{ $availableUsers->count() }} team members available to add</p>
                    
                    <select id="memberSelect" class="form-control categorized-select">
                        <option value="">Choose someone to add to this conversation...</option>
                        @foreach($roleOrder as $role)
                            @if(isset($groupedUsers[$role]) && $groupedUsers[$role]->count() > 0)
                                <optgroup label="{{ $roleLabels[$role] ?? ucfirst($role) }}">
                                    @foreach($groupedUsers[$role] as $user)
                                        <option value="{{ $user->id }}" data-role="{{ $user->role }}">
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        @endforeach
                    </select>
                    
                    <button class="btn btn-success btn-sm" onclick="addSelectedMember()">
                        <i class="fas fa-user-plus"></i> Add to Conversation
                    </button>
                </div>
            @else
                <div class="no-users-available" style="padding: 10px; color: #666;">
                    <em>No other team members available to add</em>
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
                            @if($message->hasAttachment())
                                <div class="message-attachment">
                                    <i class="fas {{ $message->attachment_icon }}"></i>
                                    @if($message->attachment_mime_type === 'application/pdf')
                                        <button type="button" class="attachment-link" onclick="viewChatAttachmentPdf({{ $message->id }})">
                                            {{ $message->attachment_original_name }}
                                        </button>
                                    @else
                                        <a href="{{ auth()->user()->role === 'nurse' ? '/nurse/chat/attachment/' . $message->id . '/download' : '/doctor/chat/attachment/' . $message->id . '/download' }}" 
                                           class="attachment-link" 
                                           target="_blank">
                                            {{ $message->attachment_original_name }}
                                        </a>
                                    @endif
                                    <span class="attachment-size">({{ number_format($message->attachment_size / 1024, 1) }} KB)</span>
                                </div>
                            @endif
                        </div>
                        @if($message->isSystemMessage())
                            <div class="message-time">{{ $message->short_time }}</div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Message Input -->
            <div class="message-input-container">
                <div id="uploadProgress" class="upload-progress" style="display: none;">
                    <div class="upload-progress-bar">
                        <div class="upload-progress-fill"></div>
                    </div>
                    <span class="upload-progress-text">Uploading file...</span>
                </div>
                <form id="messageForm" onsubmit="sendMessage(event)" enctype="multipart/form-data">
                    <div class="input-group">
                        <input type="text" id="messageInput" class="form-control" placeholder="Type your message..." maxlength="1000">
                        <input type="file" id="fileInput" name="attachment" style="display: none;" 
                               accept=".jpeg,.jpg,.png,.gif,.pdf,.doc,.docx,.txt,.xlsx,.xls"
                               onchange="handleFileSelect(this)">
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('fileInput').click()">
                            <i class="fas fa-paperclip"></i>
                        </button>
                        <button type="submit" class="btn btn-primary" id="sendBtn">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                    <div id="filePreview" class="file-preview" style="display: none;">
                        <div class="file-preview-content">
                            <span class="file-preview-name"></span>
                            <span class="file-preview-size"></span>
                            <button type="button" class="file-preview-remove" onclick="removeSelectedFile()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Remove Participant Confirmation Modal -->
<div id="removeParticipantModal" class="remove-modal-overlay" style="display: none;">
    <div class="remove-modal">
        <div class="remove-modal-header">
            <div class="remove-icon">
                <i class="fas fa-user-minus"></i>
            </div>
            <h3>Remove Participant</h3>
        </div>
        <div class="remove-modal-body">
            <p>Are you sure you want to remove this participant?</p>
            <p class="remove-warning">This action cannot be undone.</p>
        </div>
        <div class="remove-modal-footer">
            <button id="confirmRemoveBtn" class="btn-remove-confirm">
                <i class="fas fa-check"></i> OK
            </button>
            <button id="cancelRemoveBtn" class="btn-remove-cancel">
                <i class="fas fa-times"></i> Cancel
            </button>
        </div>
    </div>
</div>

<style>
/* Add Participant Section Styling */
.add-participant-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    margin-top: 20px;
}

.add-participant-section h4 {
    margin: 0 0 10px 0;
    color: #495057;
    font-size: 16px;
}

.available-count {
    font-size: 14px;
    color: #6c757d;
    margin: 0 0 15px 0;
}

/* Categorized Dropdown Styles */
.categorized-select {
    font-family: inherit;
    margin-bottom: 15px;
    font-size: 14px;
}

.categorized-select optgroup {
    background-color: #e9ecef;
    color: #495057;
    font-weight: 600;
    font-size: 13px;
    padding: 8px 12px;
    border-bottom: 1px solid #dee2e6;
    font-style: normal;
}

.categorized-select option {
    padding: 8px 20px;
    color: #212529;
    font-weight: normal;
    background-color: #ffffff;
}

.categorized-select option:hover,
.categorized-select option:focus {
    background-color: #e9ecef;
}

/* Remove Participant Modal Styles */
.remove-modal-overlay {
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

.remove-modal {
    background: white;
    border-radius: 12px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
    max-width: 420px;
    width: 90%;
    overflow: hidden;
    animation: slideIn 0.3s ease-out;
}

.remove-modal-header {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
    padding: 25px;
    text-align: center;
}

.remove-icon {
    font-size: 48px;
    margin-bottom: 15px;
    opacity: 0.9;
}

.remove-modal-header h3 {
    margin: 0;
    font-size: 22px;
    font-weight: 600;
}

.remove-modal-body {
    padding: 30px 25px;
    text-align: center;
}

.remove-modal-body p {
    margin: 0 0 15px 0;
    font-size: 16px;
    color: #495057;
    line-height: 1.5;
}

.remove-warning {
    font-size: 14px !important;
    color: #6c757d !important;
    font-style: italic;
}

.remove-modal-footer {
    padding: 20px 25px;
    background: #f8f9fa;
    display: flex;
    gap: 15px;
    justify-content: center;
}

.btn-remove-confirm, .btn-remove-cancel {
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 120px;
    justify-content: center;
}

.btn-remove-confirm {
    background: #dc3545;
    color: white;
}

.btn-remove-confirm:hover {
    background: #c82333;
    transform: translateY(-1px);
}

.btn-remove-cancel {
    background: #6c757d;
    color: white;
}

.btn-remove-cancel:hover {
    background: #545b62;
    transform: translateY(-1px);
}

/* Ensure icons display correctly */
.remove-modal-footer .fas {
    display: inline-block !important;
    font-family: "Font Awesome 6 Free" !important;
    font-weight: 900 !important;
    font-style: normal !important;
}

.btn-remove-confirm i.fas.fa-check, 
.btn-remove-cancel i.fas.fa-times {
    font-size: 14px;
    line-height: 1;
    width: 14px;
    height: 14px;
    text-align: center;
    vertical-align: middle;
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
const chatRoomId = {{ $chatRoom->id }};
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const userRole = '{{ auth()->user()->role }}';
const chatBaseUrl = userRole === 'nurse' ? '/nurse/chat' : '/doctor/chat';
let lastMessageId = 0;
let isRefreshing = false;
let pendingRemoveUserId = null;

// Remove Participant Modal System
window.RemoveParticipantModal = {
    show: function(userId) {
        pendingRemoveUserId = userId;
        const modal = document.getElementById('removeParticipantModal');
        modal.style.display = 'flex';
        
        // Focus the confirm button immediately
        document.getElementById('confirmRemoveBtn').focus();
    },
    
    hide: function() {
        const modal = document.getElementById('removeParticipantModal');
        modal.style.display = 'none';
        pendingRemoveUserId = null;
    },
    
    confirm: function() {
        if (!pendingRemoveUserId) return;

        // capture id before hiding (hide() resets pendingRemoveUserId)
        const idToRemove = pendingRemoveUserId;
        this.hide();
        performRemoveParticipant(idToRemove);
    }
};

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
    
    // Remove Participant Modal Event Listeners
    const confirmRemoveBtn = document.getElementById('confirmRemoveBtn');
    const cancelRemoveBtn = document.getElementById('cancelRemoveBtn');
    
    if (confirmRemoveBtn) {
        confirmRemoveBtn.addEventListener('click', function() {
            RemoveParticipantModal.confirm();
        });
    }
    
    if (cancelRemoveBtn) {
        cancelRemoveBtn.addEventListener('click', function() {
            RemoveParticipantModal.hide();
        });
    }
    
    // Close remove modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('removeParticipantModal');
            if (modal && modal.style.display === 'flex') {
                RemoveParticipantModal.hide();
            }
        }
    });
    
    // Close remove modal on overlay click
    const removeModal = document.getElementById('removeParticipantModal');
    if (removeModal) {
        removeModal.addEventListener('click', function(e) {
            if (e.target === this) {
                RemoveParticipantModal.hide();
            }
        });
    }

    // Delegated listener for remove participant buttons (handles dynamically added buttons too)
    document.addEventListener('click', function(e) {
        const btn = e.target.closest && e.target.closest('.btn-remove-participant');
        if (btn) {
            e.preventDefault();
            const userId = btn.getAttribute('data-user-id');
            console.log('Delegated remove button clicked, userId=', userId);
            if (!userId) {
                alert('Error: No user ID available for this participant');
                return;
            }
            RemoveParticipantModal.show(userId);
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
    const fileInput = document.getElementById('fileInput');
    const sendBtn = document.getElementById('sendBtn');
    const message = messageInput.value.trim();
    const hasFile = fileInput.files.length > 0;
    
    // Check if we have either a message or a file
    if (!message && !hasFile) {
        showMessageError('Please enter a message or select a file to send');
        return;
    }
    
    // Disable input while sending and prevent auto-refresh conflicts
    messageInput.disabled = true;
    fileInput.disabled = true;
    sendBtn.disabled = true;
    
    // Show upload progress for file uploads
    const uploadProgress = document.getElementById('uploadProgress');
    if (hasFile) {
        sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
        uploadProgress.style.display = 'block';
    } else {
        sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    }
    
    isRefreshing = true;
    
    // Create FormData for file upload
    const formData = new FormData();
    if (message) {
        formData.append('message', message);
    }
    if (hasFile) {
        formData.append('attachment', fileInput.files[0]);
    }
    
    // Create an AbortController for timeout
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 second timeout
    
    fetch(`${chatBaseUrl}/${chatRoomId}/message`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: formData,
        signal: controller.signal
    })
    .then(async response => {
        const contentType = response.headers.get('content-type');
        
        if (!response.ok) {
            // Log the response for debugging
            const errorText = await response.text();
            console.error('Server error:', response.status, errorText);
            
            let errorMessage = `Server returned ${response.status}`;
            try {
                const errorData = JSON.parse(errorText);
                if (errorData.error) {
                    errorMessage = errorData.error;
                } else if (errorData.errors) {
                    errorMessage = Object.values(errorData.errors).flat().join(', ');
                }
            } catch (e) {
                // If we can't parse JSON, use the text response
                errorMessage = errorText.substring(0, 100);
            }
            
            throw new Error(errorMessage);
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
        
        // Clear the timeout since we got a response
        clearTimeout(timeoutId);
        
        if (data.success) {
            // Add message to UI immediately
            addMessageToUI(data.message, data.formatted_time, true);
            messageInput.value = '';
            
            // Clear file input and preview
            fileInput.value = '';
            removeSelectedFile();
            
            scrollToBottom();
        } else {
            console.error('Server returned success=false:', data);
            throw new Error(data.message || 'Server returned success=false');
        }
    })
    .catch(error => {
        console.error('Error sending message:', error);
        
        // Clear the timeout
        clearTimeout(timeoutId);
        
        // Show a more user-friendly error message
        let errorMsg = 'Failed to send message. Please try again.';
        
        if (error.name === 'AbortError') {
            errorMsg = 'Upload timed out. Please try with a smaller file or check your connection.';
        } else if (error.message.includes('fetch')) {
            errorMsg = 'Network error. Please check your connection.';
        } else if (error.message.includes('422')) {
            errorMsg = 'Validation error. Please check your file type and size.';
        } else if (error.message.includes('413')) {
            errorMsg = 'File too large. Please choose a smaller file.';
        }
            
        // Use a non-blocking notification instead of alert
        showMessageError(errorMsg);
        
        // Don't clear the message input on error so user can retry
    })
    .finally(() => {
        // Re-enable input and allow auto-refresh
        messageInput.disabled = false;
        fileInput.disabled = false;
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
        
        // Hide upload progress
        const uploadProgress = document.getElementById('uploadProgress');
        uploadProgress.style.display = 'none';
        
        messageInput.focus();
        isRefreshing = false;
    });
}

function addMessageToUI(message, formattedTime, isOwnMessage) {
    const messagesList = document.getElementById('messagesList');
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${isOwnMessage ? 'own-message' : 'other-message'}`;
    messageDiv.setAttribute('data-message-id', message.id);
    
    let attachmentHtml = '';
    if (message.has_attachment) {
        const fileIcon = getFileIcon(message.attachment_mime_type);
        const fileSize = Math.round(message.attachment_size / 1024);
        
        // Use different handling for PDFs vs other files
        let linkHtml;
        if (message.attachment_mime_type === 'application/pdf') {
            linkHtml = `
                <button type="button" class="attachment-link" onclick="viewChatAttachmentPdf(${message.id})">
                    ${message.attachment_original_name}
                </button>
            `;
        } else {
            linkHtml = `
                <a href="${chatBaseUrl}/attachment/${message.id}/download" 
                   class="attachment-link" 
                   target="_blank">
                    ${message.attachment_original_name}
                </a>
            `;
        }
        
        attachmentHtml = `
            <div class="message-attachment">
                <i class="fas ${fileIcon}"></i>
                ${linkHtml}
                <span class="attachment-size">(${fileSize} KB)</span>
            </div>
        `;
    }
    
    messageDiv.innerHTML = `
        <div class="message-header">
            <strong class="sender-name">${message.user.name}</strong>
            <span class="sender-role">${message.user.role.charAt(0).toUpperCase() + message.user.role.slice(1)}</span>
            <span class="message-time">now</span>
        </div>
        <div class="message-content">
            ${message.message || ''}
            ${attachmentHtml}
        </div>
    `;
    
    messagesList.appendChild(messageDiv);
    
    // Update last message ID
    lastMessageId = Math.max(lastMessageId, message.id);
}

function handleFileSelect(input) {
    const file = input.files[0];
    if (!file) return;
    
    // Check file size (10MB = 10 * 1024 * 1024 bytes)
    const maxSize = 10 * 1024 * 1024;
    if (file.size > maxSize) {
        showMessageError('File size must be less than 10MB');
        input.value = '';
        return;
    }
    
    // Show file preview
    const preview = document.getElementById('filePreview');
    const fileName = preview.querySelector('.file-preview-name');
    const fileSize = preview.querySelector('.file-preview-size');
    
    fileName.textContent = file.name;
    fileSize.textContent = `(${Math.round(file.size / 1024)} KB)`;
    preview.style.display = 'block';
}

function removeSelectedFile() {
    const fileInput = document.getElementById('fileInput');
    const preview = document.getElementById('filePreview');
    
    fileInput.value = '';
    preview.style.display = 'none';
}

function getFileIcon(mimeType) {
    if (mimeType.startsWith('image/')) return 'fa-image';
    if (mimeType === 'application/pdf') return 'fa-file-pdf';
    if (mimeType.includes('word') || mimeType.includes('document')) return 'fa-file-word';
    if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return 'fa-file-excel';
    if (mimeType.includes('text')) return 'fa-file-text';
    return 'fa-file';
}

// Function to view chat attachment PDF (similar to lab result PDF viewer)
function viewChatAttachmentPdf(messageId) {
    // Open the PDF in a new window/tab using the download route
    window.open(`${chatBaseUrl}/attachment/${messageId}/download`, '_blank');
}

// Make the function available globally
window.viewChatAttachmentPdf = viewChatAttachmentPdf;

function addSelectedMember() {
    const memberSelect = document.getElementById('memberSelect');
    const memberId = memberSelect.value;
    
    if (!memberId) {
        alert('Please select a team member to add');
        return;
    }
    
    const selectedOption = memberSelect.options[memberSelect.selectedIndex];
    const memberRole = selectedOption.getAttribute('data-role');
    const memberName = selectedOption.textContent.trim();
    
    // Disable the button and show loading state
    const addButton = document.querySelector('button[onclick="addSelectedMember()"]');
    const originalText = addButton.innerHTML;
    addButton.disabled = true;
    addButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    
    // Show confirmation with member name and role
    if (confirm(`Add ${memberName} (${memberRole.charAt(0).toUpperCase() + memberRole.slice(1)}) to this conversation?`)) {
        addParticipant(memberId, memberRole, function() {
            // Re-enable button on completion
            addButton.disabled = false;
            addButton.innerHTML = originalText;
        });
    } else {
        // Re-enable button if cancelled
        addButton.disabled = false;
        addButton.innerHTML = originalText;
    }
}

function addParticipant(userId, userRole, callback) {
    fetch(`${chatBaseUrl}/${chatRoomId}/add-participant`, {
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
    console.log('removeParticipant called with userId:', userId, 'type:', typeof userId);
    
    if (!userId) {
        alert('Error: No user ID provided');
        return;
    }
    
    RemoveParticipantModal.show(userId);
}

function performRemoveParticipant(userId) {
    console.log('Attempting to remove user ID:', userId); // Debug log
    
    fetch(`${chatBaseUrl}/${chatRoomId}/remove-participant`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ user_id: parseInt(userId) }) // Ensure it's an integer
    })
    .then(response => {
        console.log('Response status:', response.status); // Debug log
        if (!response.ok) {
            return response.json().then(errorData => {
                console.error('Error response:', errorData); // Debug log
                throw new Error(errorData.message || `Server error: ${response.status}`);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Success response:', data); // Debug log
        if (data.success) {
            location.reload(); // Reload to update participants list
        } else {
            alert(data.message || 'Failed to remove participant');
        }
    })
    .catch(error => {
        console.error('Error removing participant:', error);
        alert(error.message || 'Failed to remove participant');
    });
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
    
    fetch(`${chatBaseUrl}/${chatRoomId}/messages`)
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
                let attachmentHtml = '';
                if (message.has_attachment) {
                    const fileIcon = getFileIcon(message.attachment_mime_type);
                    const fileSize = Math.round(message.attachment_size / 1024);
                    
                    // Use different handling for PDFs vs other files
                    let linkHtml;
                    if (message.attachment_mime_type === 'application/pdf') {
                        linkHtml = `
                            <button type="button" class="attachment-link" onclick="viewChatAttachmentPdf(${message.id})">
                                ${message.attachment_original_name}
                            </button>
                        `;
                    } else {
                        linkHtml = `
                            <a href="${chatBaseUrl}/attachment/${message.id}/download" 
                               class="attachment-link" 
                               target="_blank">
                                ${message.attachment_original_name}
                            </a>
                        `;
                    }
                    
                    attachmentHtml = `
                        <div class="message-attachment">
                            <i class="fas ${fileIcon}"></i>
                            ${linkHtml}
                            <span class="attachment-size">(${fileSize} KB)</span>
                        </div>
                    `;
                }
                
                messageDiv.innerHTML = `
                    <div class="message-header">
                        <strong class="sender-name">${message.user_name}</strong>
                        <span class="sender-role">${message.user_role}</span>
                        <span class="message-time">${message.short_time}</span>
                    </div>
                    <div class="message-content">
                        ${message.message || ''}
                        ${attachmentHtml}
                    </div>
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