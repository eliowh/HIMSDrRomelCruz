<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatRoom extends Model
{
    protected $fillable = [
        'name',
        'description',
        'patient_id',
        'patient_no',
        'room_type',
        'participants',
        'created_by',
        'is_active',
        'last_activity',
    ];

    protected $casts = [
        'participants' => 'array',
        'is_active' => 'boolean',
        'last_activity' => 'datetime',
    ];

    /**
     * Get the patient that owns this chat room.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the user who created this chat room.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all messages for this chat room.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('created_at', 'asc');
    }

    /**
     * Get recent messages for this chat room.
     */
    public function recentMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('created_at', 'desc')->limit(50);
    }

    /**
     * Get the last message in this chat room.
     */
    public function lastMessage(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->latest()->limit(1);
    }

    /**
     * Check if a user is a participant in this chat room.
     */
    public function hasParticipant($userId): bool
    {
        $participants = $this->participants ?? [];
        return in_array($userId, $participants);
    }

    /**
     * Add a participant to the chat room.
     */
    public function addParticipant($userId): void
    {
        $participants = $this->participants ?? [];
        if (!in_array($userId, $participants)) {
            $participants[] = $userId;
            $this->update(['participants' => $participants]);
        }
    }

    /**
     * Remove a participant from the chat room.
     */
    public function removeParticipant($userId): void
    {
        $participants = $this->participants ?? [];
        $participants = array_filter($participants, fn($id) => $id != $userId);
        $this->update(['participants' => array_values($participants)]);
    }

    /**
     * Get all participants as User models.
     */
    public function getParticipantUsers()
    {
        $participantIds = $this->participants ?? [];
        $users = User::whereIn('id', $participantIds)->get();
        
        // Clean up participants list if there are invalid IDs
        $validIds = $users->pluck('id')->toArray();
        $invalidIds = array_diff($participantIds, $validIds);
        
        if (!empty($invalidIds)) {
            Log::warning('Found invalid participant IDs in chat room ' . $this->id, [
                'invalid_ids' => $invalidIds,
                'valid_ids' => $validIds,
                'original_participants' => $participantIds
            ]);
            
            // Update to only keep valid participant IDs
            $this->update(['participants' => array_values($validIds)]);
        }
        
        return $users;
    }

    /**
     * Update last activity timestamp.
     */
    public function updateActivity(): void
    {
        $this->update(['last_activity' => now()]);
    }

    /**
     * Get chat rooms that the current user can access.
     */
    public static function accessibleByUser($userId)
    {
        return static::where(function ($query) use ($userId) {
            $query->where('created_by', $userId)
                  ->orWhereJsonContains('participants', $userId);
        })->where('is_active', true);
    }

    /**
     * Create a new patient consultation chat room.
     */
    public static function createForPatient($patient, $assignedDoctorName = null)
    {
        $currentUser = Auth::user();
        
        // Find the assigned doctor by name if provided
        $assignedDoctor = null;
        if ($assignedDoctorName) {
            $assignedDoctor = User::where('name', 'LIKE', "%{$assignedDoctorName}%")
                                  ->where('role', 'doctor')
                                  ->first();
        }

        $participants = [$currentUser->id];
        if ($assignedDoctor && $assignedDoctor->id !== $currentUser->id) {
            $participants[] = $assignedDoctor->id;
        }

        $chatRoom = static::create([
            'name' => "Patient {$patient->patient_no} - {$patient->display_name}",
            'description' => "Medical consultation for patient {$patient->patient_no}",
            'patient_id' => $patient->id,
            'patient_no' => $patient->patient_no,
            'room_type' => 'patient_consultation',
            'participants' => $participants,
            'created_by' => $currentUser->id,
            'is_active' => true,
            'last_activity' => now(),
        ]);

        // Send a system message to welcome participants
        $welcomeMessage = "Chat room created for patient {$patient->display_name} (#{$patient->patient_no}).";
        if ($assignedDoctor) {
            $welcomeMessage .= " Assigned doctor: {$assignedDoctor->name}";
        }

        ChatMessage::create([
            'chat_room_id' => $chatRoom->id,
            'user_id' => $currentUser->id,
            'message' => $welcomeMessage,
            'message_type' => 'system',
        ]);

        return $chatRoom;
    }
}