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
        $user = auth()->user();
        
        // Get chat rooms that this user can access (as creator or participant)
        $chatRooms = ChatRoom::accessibleByUser($user->id)
            ->with(['patient', 'lastMessage'])
            ->orderBy('last_activity', 'desc')
            ->get();

        // Set the appropriate layout based on user role
        $layout = $user->role === 'nurse' ? 'layouts.nurse' : 'layouts.doctor';
        
        return view('chat.index', compact('chatRooms'))->with('layout', $layout);
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

        // Additional check for doctor-only group chats
        if ($chatRoom->room_type === 'doctor_group_consultation' && $user->role !== 'doctor') {
            abort(403, 'Only doctors can access doctor group consultations.');
        }

        // Mark messages as read for current user
        $chatRoom->messages()
            ->where('user_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Get users for the add participant dropdown based on chat room type and current user role
        if ($user->role === 'doctor') {
            // If current user is a doctor, only show other doctors regardless of chat type
            $allUsers = User::whereNotIn('id', $chatRoom->participants ?? [])
                ->where('id', '!=', $user->id) // Don't include current user
                ->where('role', 'doctor') // Only doctors
                ->select('id', 'name', 'role')
                ->orderBy('name')
                ->get();
            $doctors = $allUsers; // For doctors, all users are doctors
        } else {
            // For non-doctors, show all healthcare roles except doctors (since doctors manage their own chats)
            $allUsers = User::whereNotIn('id', $chatRoom->participants ?? [])
                ->where('id', '!=', $user->id) // Don't include current user
                ->whereIn('role', ['nurse', 'admin', 'lab_technician', 'cashier', 'inventory', 'pharmacy', 'billing']) // Healthcare roles except doctor
                ->select('id', 'name', 'role')
                ->orderBy('role')
                ->orderBy('name')
                ->get();
            $doctors = collect([]); // Non-doctors can't add doctors
        }

        Log::info('Chat show data', [
            'chat_room_id' => $chatRoom->id,
            'room_type' => $chatRoom->room_type,
            'current_user_role' => $user->role,
            'allUsers_count' => $allUsers->count(),
            'doctors_count' => $doctors->count(),
            'available_roles' => $allUsers->pluck('role')->unique()->values()->toArray()
        ]);

        return view('chat.show', compact('chatRoom', 'doctors', 'allUsers'));
    }

    /**
     * Create or get existing chat room for a patient.
     */
    public function createOrGetForPatient(Request $request)
    {
        try {
            Log::info('Chat creation request received', [
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);

            $request->validate([
                'patient_id' => 'required|exists:patients,id'
            ]);

            $user = Auth::user();
            $patient = Patient::findOrFail($request->patient_id);

            Log::info('Patient found for chat creation', [
                'patient_id' => $patient->id,
                'patient_no' => $patient->patient_no,
                'user_id' => $user->id,
                'user_role' => $user->role
            ]);

            // Check if a chat room already exists for this patient
            $existingRoom = ChatRoom::where('patient_id', $patient->id)
                ->where('is_active', true)
                ->first();

            if ($existingRoom) {
                Log::info('Existing chat room found', ['room_id' => $existingRoom->id]);
                // For doctors: allow access to any patient chat
                // For non-doctors: only allow access if they're already a participant or if it's a simple patient chat
                if ($user->role === 'doctor' || $existingRoom->hasParticipant($user->id) || $existingRoom->room_type !== 'doctor_group_consultation') {
                    if (!$existingRoom->hasParticipant($user->id)) {
                        $existingRoom->addParticipant($user->id);
                    }
                    $chatRoom = $existingRoom;
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have access to this patient chat. Only doctors can access doctor group chats.'
                    ], 403);
                }
            } else {
                Log::info('Creating new chat room for patient', [
                    'patient_id' => $patient->id,
                    'user_role' => $user->role
                ]);
                
                // Create appropriate chat room based on user role
                if ($user->role === 'doctor') {
                    // Doctors get doctor-only group chats
                    $chatRoom = $this->createDoctorGroupChatForPatient($patient, $user);
                } else {
                    // Non-doctors get simple patient consultation chats
                    $chatRoom = ChatRoom::createForPatient($patient, $patient->doctor_name);
                }
                
                Log::info('Chat room created successfully', ['room_id' => $chatRoom->id]);
            }

            // Determine the correct route based on user role
            $routeName = $user->role === 'nurse' ? 'nurse.chat.show' : 'chat.show';

            return response()->json([
                'success' => true,
                'chat_room_id' => $chatRoom->id,
                'redirect_url' => route($routeName, $chatRoom->id)
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in chat creation', [
                'errors' => $e->validator->errors()->toArray()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating chat room for patient', [
                'user_id' => auth()->id(),
                'patient_id' => $request->patient_id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create chat room: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a doctor-only group chat for a patient
     */
    private function createDoctorGroupChatForPatient($patient, $creatingDoctor)
    {
        try {
            Log::info('Creating doctor group chat', [
                'patient_id' => $patient->id,
                'creating_doctor_id' => $creatingDoctor->id,
                'creating_doctor_name' => $creatingDoctor->name
            ]);
            
            // Only add the creating doctor initially - other doctors can be added manually
            $participants = [$creatingDoctor->id];

            $chatRoom = ChatRoom::create([
                'name' => "Patient {$patient->patient_no} - {$patient->display_name} (Doctors Only)",
                'description' => "Doctor consultation for patient {$patient->patient_no}",
                'patient_id' => $patient->id,
                'patient_no' => $patient->patient_no,
                'room_type' => 'doctor_group_consultation',
                'participants' => $participants,
                'created_by' => $creatingDoctor->id,
                'is_active' => true,
                'last_activity' => now(),
            ]);

            Log::info('Chat room created, creating system message', [
                'chat_room_id' => $chatRoom->id,
                'doctor_id' => $creatingDoctor->id
            ]);

            // Validate the doctor object before creating message
            if (!$creatingDoctor || !$creatingDoctor->id) {
                Log::error('Invalid creating doctor object', [
                    'doctor_object' => $creatingDoctor,
                    'doctor_id' => $creatingDoctor ? $creatingDoctor->id : 'null'
                ]);
                throw new \Exception('Invalid creating doctor object');
            }

            // Send a system message
            ChatMessage::create([
                'chat_room_id' => $chatRoom->id,
                'user_id' => $creatingDoctor->id, // Use creating doctor's ID instead of null
                'message' => "Dr. {$creatingDoctor->name} created a doctor consultation for patient {$patient->display_name} (#{$patient->patient_no}). Other doctors can be added as needed.",
                'message_type' => 'system',
            ]);

            return $chatRoom;
            
        } catch (\Exception $e) {
            Log::error('Error creating doctor group chat for patient', [
                'patient_id' => $patient->id,
                'doctor_id' => $creatingDoctor->id,
                'error' => $e->getMessage()
            ]);
            throw $e; // Re-throw to be caught by parent method
        }
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
    /**
     * Create a group chat with doctors only (Exclusive for doctors)
     */
    public function createRoleGroup(Request $request)
    {
        $user = auth()->user();
        
        // Only doctors can create group chats
        if ($user->role !== 'doctor') {
            return response()->json([
                'success' => false, 
                'message' => 'Only doctors can create group chats'
            ], 403);
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'roles' => 'required|array|min:1',
            'roles.*' => 'in:doctor', // Only allow doctor role
            'patient_no' => 'nullable|string|max:50'
        ]);

        // Get all doctors (excluding current user)
        $targetUsers = User::where('role', 'doctor')
            ->where('id', '!=', $user->id) // Don't include current user as they'll be added as creator
            ->pluck('id')
            ->toArray();

        if (empty($targetUsers)) {
            return response()->json([
                'success' => false, 
                'message' => 'No other doctors found to add to the group chat'
            ]);
        }

        // Add current user to participants
        $participants = array_merge([$user->id], $targetUsers);

        $chatRoom = ChatRoom::create([
            'name' => $request->name,
            'patient_no' => $request->patient_no,
            'created_by' => $user->id,
            'participants' => $participants
        ]);

        // Send system message about group creation
        ChatMessage::create([
            'chat_room_id' => $chatRoom->id,
            'user_id' => $user->id, // Use creating user's ID instead of null
            'message' => "Dr. {$user->name} created a doctors group chat",
            'message_type' => 'system'
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'Group chat created successfully',
            'chat_room_id' => $chatRoom->id
        ]);
    }

    public function archive(Request $request, $id)
    {
        $chatRoom = ChatRoom::findOrFail($id);
        $user = auth()->user();

        // Check if user is creator or has permission to archive
        if ($chatRoom->created_by !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Only the creator can archive this chat room'], 403);
        }

        $chatRoom->update(['archived' => true]);

        return response()->json(['success' => true, 'message' => 'Chat room archived successfully']);
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