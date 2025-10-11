<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Patient;
use App\Services\StreamChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StreamChatController extends Controller
{
    private $streamService;

    public function __construct(StreamChatService $streamService)
    {
        $this->streamService = $streamService;
    }

    /**
     * Display chat dashboard
     */
    public function index()
    {
        $user = Auth::user();
        
        // Ensure user exists in Stream
        $this->streamService->upsertUser($user);
        
        // Get user's channels from Stream
        try {
            $channels = $this->streamService->getUserChannels($user->id);
        } catch (\Exception $e) {
            \Log::error('Error fetching channels: ' . $e->getMessage());
            $channels = ['channels' => []];
        }

        // Generate user token for frontend
        $userToken = $this->streamService->generateUserToken($user->id);

        return view('stream-chat.index', [
            'channels' => $channels['channels'] ?? [],
            'userToken' => $userToken,
            'apiKey' => config('stream.api_key'),
            'userId' => $user->id,
            'userName' => $user->name
        ]);
    }

    /**
     * Show specific chat channel
     */
    public function show($channelType, $channelId)
    {
        $user = Auth::user();
        
        // Ensure user exists in Stream
        $this->streamService->upsertUser($user);
        
        // Generate user token for frontend
        $userToken = $this->streamService->generateUserToken($user->id);

        return view('stream-chat.show', [
            'channelType' => $channelType,
            'channelId' => $channelId,
            'userToken' => $userToken,
            'apiKey' => config('stream.api_key'),
            'userId' => $user->id,
            'userName' => $user->name
        ]);
    }

    /**
     * Create chat for patient consultation
     */
    public function createForPatient(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,patient_no'
        ]);

        $user = Auth::user();
        $patient = Patient::where('patient_no', $request->patient_id)->firstOrFail();
        
        // Ensure user exists in Stream
        $this->streamService->upsertUser($user);

        try {
            // Create channel in Stream
            $channelData = $this->streamService->createChannel(
                $patient->patient_no,
                $user->id,
                "Patient {$patient->patient_no} - {$patient->display_name}"
            );

            // Add creator as member
            $this->streamService->addMembersToChannel(
                $channelData['channel_type'],
                $channelData['channel_id'],
                [$user->id]
            );

            return response()->json([
                'success' => true,
                'channel_type' => $channelData['channel_type'],
                'channel_id' => $channelData['channel_id'],
                'redirect_url' => route('stream-chat.show', [
                    'channelType' => $channelData['channel_type'],
                    'channelId' => $channelData['channel_id']
                ])
            ]);

        } catch (\Exception $e) {
            \Log::error('Error creating Stream channel: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create chat room. Please try again.'
            ], 500);
        }
    }

    /**
     * Add participant to chat
     */
    public function addParticipant(Request $request, $channelType, $channelId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $newParticipant = User::findOrFail($request->user_id);
        
        // Ensure both users exist in Stream
        $this->streamService->upsertUser(Auth::user());
        $this->streamService->upsertUser($newParticipant);

        try {
            // Add member to Stream channel
            $this->streamService->addMembersToChannel(
                $channelType,
                $channelId,
                [$newParticipant->id]
            );

            return response()->json([
                'success' => true,
                'message' => 'Participant added successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error adding participant: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to add participant. Please try again.'
            ], 500);
        }
    }

    /**
     * Remove participant from chat
     */
    public function removeParticipant(Request $request, $channelType, $channelId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        try {
            // Remove member from Stream channel
            $this->streamService->removeMembersFromChannel(
                $channelType,
                $channelId,
                [$request->user_id]
            );

            return response()->json([
                'success' => true,
                'message' => 'Participant removed successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error removing participant: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove participant. Please try again.'
            ], 500);
        }
    }

    /**
     * Get user token for frontend authentication
     */
    public function getUserToken()
    {
        $user = Auth::user();
        
        // Ensure user exists in Stream
        $this->streamService->upsertUser($user);
        
        return response()->json([
            'token' => $this->streamService->generateUserToken($user->id),
            'user' => [
                'id' => (string)$user->id,
                'name' => $user->name,
                'role' => $user->role
            ]
        ]);
    }

    /**
     * Get available doctors for adding to chat
     */
    public function getAvailableDoctors($channelType, $channelId)
    {
        // Get all doctors except current user
        $doctors = User::where('role', 'doctor')
            ->where('id', '!=', Auth::id())
            ->select('id', 'name', 'role')
            ->get()
            ->map(function ($doctor) {
                return [
                    'id' => (string)$doctor->id,
                    'name' => $doctor->name,
                    'role' => $doctor->role
                ];
            });

        return response()->json([
            'doctors' => $doctors
        ]);
    }
}