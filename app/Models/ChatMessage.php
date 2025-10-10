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
}