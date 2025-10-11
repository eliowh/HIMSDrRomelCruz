<?php $__env->startSection('title', 'Messages'); ?>

<?php $__env->startSection('content'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/chat/chat.css')); ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="chat-container">
    <div class="chat-header">
        <h2><i class="fas fa-comments"></i> Messages</h2>
        <div class="chat-stats">
            <span class="stat"><?php echo e($chatRooms->count()); ?> conversations</span>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <?php if($chatRooms->count() > 0): ?>
        <div class="chat-rooms-grid">
            <?php $__currentLoopData = $chatRooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="chat-room-card" onclick="openChatRoom(<?php echo e($room->id); ?>)">
                    <div class="room-header">
                        <div class="room-info">
                            <h3 class="room-name"><?php echo e($room->name); ?></h3>
                            <span class="patient-info">
                                Patient #<?php echo e($room->patient_no); ?>

                                <?php if($room->patient): ?>
                                    - <?php echo e($room->patient->display_name); ?>

                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="room-meta">
                            <span class="last-activity">
                                <?php echo e($room->last_activity ? $room->last_activity->diffForHumans() : 'No activity'); ?>

                            </span>
                            <span class="participant-count">
                                <i class="fas fa-users"></i> <?php echo e(count($room->participants ?? [])); ?>

                            </span>
                        </div>
                    </div>
                    
                    <?php if($room->lastMessage->first()): ?>
                        <div class="last-message">
                            <strong><?php echo e($room->lastMessage->first()->sender_name); ?>:</strong>
                            <span class="message-preview"><?php echo e(Str::limit($room->lastMessage->first()->message, 80)); ?></span>
                        </div>
                    <?php else: ?>
                        <div class="last-message">
                            <em>No messages yet</em>
                        </div>
                    <?php endif; ?>
                    
                    <div class="room-actions">
                        <button class="btn btn-primary btn-sm" onclick="event.stopPropagation(); openChatRoom(<?php echo e($room->id); ?>)">
                            <i class="fas fa-comments"></i> Open Chat
                        </button>
                        <?php if($room->created_by === auth()->id()): ?>
                            <button class="btn btn-secondary btn-sm" onclick="event.stopPropagation(); archiveRoom(<?php echo e($room->id); ?>)">
                                <i class="fas fa-archive"></i> Archive
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-comments"></i>
            </div>
            <h3>No Conversations Yet</h3>
            <p>Start a conversation by selecting a patient and clicking the "Message" button in the patient details.</p>
            <a href="<?php echo e(route('doctor.patients')); ?>" class="btn btn-primary">
                <i class="fas fa-user-md"></i> Go to Patients
            </a>
        </div>
    <?php endif; ?>
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

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.doctor', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xamppLatest\htdocs\HIMSDrRomelCruz\resources\views/chat/index.blade.php ENDPATH**/ ?>