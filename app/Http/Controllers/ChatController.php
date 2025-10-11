<?php

namespace App\Http\Controllers;

use App\Models\ChatRoom;
use App\Models\ChatMessage;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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
        try {
            // Log the request for debugging
            Log::info('Processing message request', [
                'chat_room_id' => $id,
                'has_message' => !empty($request->message),
                'has_file' => $request->hasFile('attachment'),
                'file_size' => $request->hasFile('attachment') ? $request->file('attachment')->getSize() : 0
            ]);

            $request->validate([
                'message' => 'nullable|string|max:1000',
                'attachment' => 'nullable|file|max:10240|mimes:jpeg,png,gif,pdf,doc,docx,txt,xlsx,xls'
            ]);

            // Ensure either message or attachment is provided
            if (!$request->message && !$request->hasFile('attachment')) {
                return response()->json([
                    'success' => false,
                    'error' => 'Either message text or file attachment is required.'
                ], 400);
            }

            $user = Auth::user();
            $chatRoom = ChatRoom::findOrFail($id);

            // Check if user has access to this chat room
            if (!$chatRoom->hasParticipant($user->id) && $chatRoom->created_by !== $user->id) {
                abort(403, 'You do not have access to this chat room.');
            }

            $messageData = [
                'chat_room_id' => $chatRoom->id,
                'user_id' => $user->id,
                'message' => $request->message ?? '',
                'message_type' => 'text',
            ];

            // Handle file attachment if present
            if ($request->hasFile('attachment')) {
                Log::info('Processing file attachment');
                
                $file = $request->file('attachment');
                
                // Generate unique filename
                $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                
                // Store the file using Laravel's storage system
                $filePath = $file->storeAs('chat-attachments', $filename);

                // Add attachment data to message
                $messageData['attachment_filename'] = $filename;
                $messageData['attachment_original_name'] = $file->getClientOriginalName();
                $messageData['attachment_mime_type'] = $file->getMimeType();
                $messageData['attachment_size'] = $file->getSize();
                $messageData['attachment_path'] = $filePath;
                $messageData['message_type'] = 'attachment';
                
                Log::info('File attachment processed successfully', ['filename' => $filename]);
            }

            $message = ChatMessage::create($messageData);
            Log::info('Message created successfully', ['message_id' => $message->id]);

            // Update chat room activity
            $chatRoom->updateActivity();

            if ($request->expectsJson()) {
                $response = [
                    'success' => true,
                    'message' => [
                        'id' => $message->id,
                        'message' => $message->message,
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'role' => $user->role,
                        ],
                        'created_at' => $message->created_at->toISOString(),
                        'has_attachment' => $message->hasAttachment(),
                        'attachment_original_name' => $message->attachment_original_name,
                        'attachment_mime_type' => $message->attachment_mime_type,
                        'attachment_size' => $message->attachment_size,
                    ],
                    'formatted_time' => $message->formatted_time,
                ];
                
                Log::info('Returning JSON response', ['response_keys' => array_keys($response)]);
                return response()->json($response);
            }

            return redirect()->back();
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in sendMessage', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error sending message', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while sending the message: ' . $e->getMessage()
            ], 500);
        }
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
        try {
            Log::info('Remove participant request', [
                'user_id' => $request->user_id,
                'user_id_type' => gettype($request->user_id),
                'chat_room_id' => $id,
                'request_data' => $request->all(),
                'raw_input' => $request->getContent()
            ]);

            // Basic validation
            $userId = $request->user_id;
            if (!$userId || $userId === '' || $userId === 'null' || $userId === 'undefined') {
                return response()->json([
                    'success' => false,
                    'message' => 'User ID is required and cannot be empty'
                ], 422);
            }

            $userId = intval($userId);
            if ($userId <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid user ID format: ' . $request->user_id
                ], 422);
            }

            $user = Auth::user();
            $chatRoom = ChatRoom::findOrFail($id);
            
            // Check if the user to remove exists
            $participantToRemove = User::find($userId);
            if (!$participantToRemove) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found in database'
                ], 404);
            }

            Log::info('Participant removal attempt', [
                'current_user' => $user->id,
                'participant_to_remove' => $participantToRemove->id,
                'chat_room_creator' => $chatRoom->created_by,
                'chat_room_participants' => $chatRoom->participants
            ]);

            // Check if current user has access to this chat room
            if (!$chatRoom->hasParticipant($user->id) && $chatRoom->created_by !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this chat room.'
                ], 403);
            }

            // Don't allow removing the creator
            if ($participantToRemove->id === $chatRoom->created_by) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot remove the chat room creator'
                ], 400);
            }

            // Check if participant is actually in the chat room
            if (!$chatRoom->hasParticipant($participantToRemove->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is not a participant in this chat room'
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

            return response()->json([
                'success' => true,
                'message' => 'Participant removed successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error removing participant: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while removing the participant: ' . $e->getMessage()
            ], 500);
        }
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
                    'has_attachment' => $message->hasAttachment(),
                    'attachment_original_name' => $message->attachment_original_name,
                    'attachment_mime_type' => $message->attachment_mime_type,
                    'attachment_size' => $message->attachment_size,
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

    /**
     * Download a chat message attachment.
     */
    public function downloadAttachment($messageId)
    {
        try {
            $message = ChatMessage::findOrFail($messageId);
            
            // Check if message has attachment
            if (!$message->hasAttachment()) {
                abort(404, 'No attachment found');
            }
            
            // Check if file exists in storage
            if (!Storage::exists($message->attachment_path)) {
                abort(404, 'File not found in storage');
            }
            
            $fileContent = Storage::get($message->attachment_path);
            
            return response($fileContent)
                ->header('Content-Type', $message->attachment_mime_type)
                ->header('Content-Disposition', 'inline; filename="' . $message->attachment_original_name . '"');
                
        } catch (\Exception $e) {
            Log::error('Error downloading attachment: ' . $e->getMessage());
            abort(500, 'Error loading file attachment');
        }
    }
}