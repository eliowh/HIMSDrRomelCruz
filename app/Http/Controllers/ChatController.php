<?php

namespace App\Http\Controllers;

use App\Models\ChatRoom;
use App\Models\ChatMessage;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    /**
     * Display a listing of chat rooms for the current user.
     */
    public function index()
    {
        $user = Auth::user();
        
        $chatRooms = ChatRoom::accessibleByUser($user->id)
            ->with(['patient', 'lastMessage.user'])
            ->orderBy('last_activity', 'desc')
            ->get();

        return view('chat.index', compact('chatRooms'));
    }

    /**
     * Show a specific chat room.
     */
    public function show($id)
    {
        $user = Auth::user();
        
        $chatRoom = ChatRoom::with(['patient', 'messages.user'])
            ->findOrFail($id);

        // Check if user has access to this chat room
        if (!$chatRoom->hasParticipant($user->id) && $chatRoom->created_by !== $user->id) {
            abort(403, 'You do not have access to this chat room.');
        }

        // Mark messages as read for current user
        $chatRoom->messages()
            ->where('user_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Get only doctors for the add participant dropdown (excluding current participants)
        // Since this is a doctor-exclusive chat system, only doctors can be added
        $allUsers = User::whereNotIn('id', $chatRoom->participants ?? [])
            ->where('id', '!=', $user->id) // Don't include current user
            ->where('role', 'doctor') // Only doctors
            ->select('id', 'name', 'role')
            ->orderBy('name')
            ->get()
            ->map(function ($userData) {
                return [
                    'id' => $userData->id,
                    'name' => $userData->name,
                    'role' => ucfirst($userData->role)
                ];
            });

        // Get doctors specifically for backwards compatibility
        $doctors = $allUsers; // Same as allUsers since we're only fetching doctors

        return view('chat.show', compact('chatRoom', 'doctors', 'allUsers'));
    }

    /**
     * Create or get existing chat room for a patient.
     */
    public function createOrGetForPatient(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id'
        ]);

        $user = Auth::user();
        $patient = Patient::findOrFail($request->patient_id);

        // Check if a chat room already exists for this patient
        $existingRoom = ChatRoom::where('patient_id', $patient->id)
            ->where('is_active', true)
            ->first();

        if ($existingRoom) {
            // Add current user as participant if not already
            if (!$existingRoom->hasParticipant($user->id)) {
                $existingRoom->addParticipant($user->id);
            }
            $chatRoom = $existingRoom;
        } else {
            // Create new chat room
            $chatRoom = ChatRoom::createForPatient($patient, $patient->doctor_name);
        }

        return response()->json([
            'success' => true,
            'chat_room_id' => $chatRoom->id,
            'redirect_url' => route('chat.show', $chatRoom->id)
        ]);
    }

    /**
     * Send a message to a chat room.
     */
    public function sendMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $user = Auth::user();
        $chatRoom = ChatRoom::findOrFail($id);

        // Check if user has access to this chat room
        if (!$chatRoom->hasParticipant($user->id) && $chatRoom->created_by !== $user->id) {
            abort(403, 'You do not have access to this chat room.');
        }

        $message = ChatMessage::create([
            'chat_room_id' => $chatRoom->id,
            'user_id' => $user->id,
            'message' => $request->message,
            'message_type' => 'text',
        ]);

        // Update chat room activity
        $chatRoom->updateActivity();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message->load('user'),
                'formatted_time' => $message->formatted_time
            ]);
        }

        return redirect()->back();
    }

    /**
     * Add a participant to a chat room.
     */
    public function addParticipant(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = Auth::user();
        $chatRoom = ChatRoom::findOrFail($id);
        $newParticipant = User::findOrFail($request->user_id);

        // Check if current user has access to this chat room
        if (!$chatRoom->hasParticipant($user->id) && $chatRoom->created_by !== $user->id) {
            abort(403, 'You do not have access to this chat room.');
        }

        // Check if participant is already in the chat room
        if ($chatRoom->hasParticipant($newParticipant->id)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This user is already a member of this chat room'
                ], 409);
            }
            return redirect()->back()->with('error', 'This user is already a member of this chat room');
        }

        // Add the new participant
        $chatRoom->addParticipant($newParticipant->id);

        // Send a system message
        ChatMessage::create([
            'chat_room_id' => $chatRoom->id,
            'user_id' => $user->id,
            'message' => "{$newParticipant->name} was added to the conversation by {$user->name}",
            'message_type' => 'system',
        ]);

        // Update chat room activity
        $chatRoom->updateActivity();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Participant added successfully'
            ]);
        }

        return redirect()->back()->with('success', 'Participant added successfully');
    }

    /**
     * Remove a participant from a chat room.
     */
    public function removeParticipant(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = Auth::user();
        $chatRoom = ChatRoom::findOrFail($id);
        $participantToRemove = User::findOrFail($request->user_id);

        // Check if current user has access to this chat room
        if (!$chatRoom->hasParticipant($user->id) && $chatRoom->created_by !== $user->id) {
            abort(403, 'You do not have access to this chat room.');
        }

        // Don't allow removing the creator
        if ($participantToRemove->id === $chatRoom->created_by) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot remove the chat room creator'
            ], 400);
        }

        // Remove the participant
        $chatRoom->removeParticipant($participantToRemove->id);

        // Send a system message
        ChatMessage::create([
            'chat_room_id' => $chatRoom->id,
            'user_id' => $user->id,
            'message' => "{$participantToRemove->name} was removed from the conversation by {$user->name}",
            'message_type' => 'system',
        ]);

        // Update chat room activity
        $chatRoom->updateActivity();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Participant removed successfully'
            ]);
        }

        return redirect()->back()->with('success', 'Participant removed successfully');
    }

    /**
     * Get recent messages for a chat room (AJAX endpoint).
     */
    public function getMessages($id, Request $request)
    {
        $user = Auth::user();
        $chatRoom = ChatRoom::findOrFail($id);

        // Check if user has access to this chat room
        if (!$chatRoom->hasParticipant($user->id) && $chatRoom->created_by !== $user->id) {
            abort(403, 'You do not have access to this chat room.');
        }

        $messages = $chatRoom->messages()
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'messages' => $messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'user_name' => $message->sender_name,
                    'user_role' => $message->sender_role,
                    'message_type' => $message->message_type,
                    'formatted_time' => $message->formatted_time,
                    'short_time' => $message->short_time,
                    'is_own_message' => $message->user_id === auth()->id(),
                ];
            })
        ]);
    }

    /**
     * Archive a chat room.
     */
    public function archive($id)
    {
        $user = Auth::user();
        $chatRoom = ChatRoom::findOrFail($id);

        // Only creator can archive
        if ($chatRoom->created_by !== $user->id) {
            abort(403, 'Only the chat room creator can archive this conversation.');
        }

        $chatRoom->update(['is_active' => false]);

        return redirect()->route('chat.index')->with('success', 'Chat room archived successfully');
    }
}