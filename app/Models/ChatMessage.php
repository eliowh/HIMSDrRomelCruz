<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    protected $fillable = [
        'chat_room_id',
        'user_id',
        'message',
        'message_type',
        'metadata',
        'read_at',
        'is_edited',
        'edited_at',
        'attachment_filename',
        'attachment_original_name',
        'attachment_mime_type',
        'attachment_size',
        'attachment_path',
    ];

    protected $casts = [
        'metadata' => 'array',
        'read_at' => 'datetime',
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
    ];

    /**
     * Get the chat room that owns this message.
     */
    public function chatRoom(): BelongsTo
    {
        return $this->belongsTo(ChatRoom::class);
    }

    /**
     * Get the user who sent this message.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark the message as read.
     */
    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Check if the message has been read.
     */
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    /**
     * Edit the message content.
     */
    public function editMessage($newContent): void
    {
        $this->update([
            'message' => $newContent,
            'is_edited' => true,
            'edited_at' => now(),
        ]);
    }

    /**
     * Get formatted timestamp for display.
     */
    public function getFormattedTimeAttribute(): string
    {
        return $this->created_at->format('M j, Y g:i A');
    }

    /**
     * Get short timestamp for recent messages.
     */
    public function getShortTimeAttribute(): string
    {
        $now = now();
        $messageTime = $this->created_at;
        
        if ($messageTime->isToday()) {
            return $messageTime->format('g:i A');
        } elseif ($messageTime->isYesterday()) {
            return 'Yesterday';
        } elseif ($messageTime->diffInDays($now) < 7) {
            return $messageTime->format('D');
        } else {
            return $messageTime->format('M j');
        }
    }

    /**
     * Check if this is a system message.
     */
    public function isSystemMessage(): bool
    {
        return $this->message_type === 'system';
    }

    /**
     * Get the sender's display name.
     */
    public function getSenderNameAttribute(): string
    {
        return $this->user ? $this->user->name : 'Unknown User';
    }

    /**
     * Get the sender's role.
     */
    public function getSenderRoleAttribute(): string
    {
        return $this->user ? ucfirst($this->user->role) : 'Unknown';
    }

    /**
     * Check if this message has an attachment.
     */
    public function hasAttachment(): bool
    {
        return !empty($this->attachment_filename);
    }

    /**
     * Get the URL for downloading the attachment.
     */
    public function getAttachmentUrlAttribute(): string
    {
        if (!$this->hasAttachment()) {
            return '';
        }
        return route('chat.downloadAttachment', $this->id);
    }

    /**
     * Get formatted file size for display.
     */
    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->attachment_size) {
            return '';
        }

        $bytes = $this->attachment_size;
        if ($bytes < 1024) {
            return $bytes . ' B';
        } elseif ($bytes < 1048576) {
            return round($bytes / 1024, 1) . ' KB';
        } else {
            return round($bytes / 1048576, 1) . ' MB';
        }
    }

    /**
     * Check if attachment is an image.
     */
    public function isImageAttachment(): bool
    {
        if (!$this->hasAttachment()) {
            return false;
        }
        $imageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        return in_array($this->attachment_mime_type, $imageTypes);
    }

    /**
     * Get attachment icon based on file type.
     */
    public function getAttachmentIconAttribute(): string
    {
        if (!$this->hasAttachment()) {
            return 'fas fa-file';
        }

        $mimeType = $this->attachment_mime_type;
        
        if (str_starts_with($mimeType, 'image/')) {
            return 'fas fa-image';
        } elseif ($mimeType === 'application/pdf') {
            return 'fas fa-file-pdf';
        } elseif (str_contains($mimeType, 'word') || str_contains($mimeType, 'document')) {
            return 'fas fa-file-word';
        } elseif (str_contains($mimeType, 'excel') || str_contains($mimeType, 'spreadsheet')) {
            return 'fas fa-file-excel';
        } elseif (str_contains($mimeType, 'text/')) {
            return 'fas fa-file-alt';
        } else {
            return 'fas fa-file';
        }
    }
}