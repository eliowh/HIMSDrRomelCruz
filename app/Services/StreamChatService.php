<?php

namespace App\Services;

use App\Models\User;
use GuzzleHttp\Client;

class StreamChatService
{
    private $apiKey;
    private $apiSecret;
    private $baseUrl;
    private $client;

    public function __construct()
    {
        $this->apiKey = config('stream.api_key');
        $this->apiSecret = config('stream.api_secret');
        $this->baseUrl = 'https://chat.stream-io-api.com';
        $this->client = new Client();
    }

    /**
     * Generate JWT token for user authentication
     */
    public function generateUserToken($userId)
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode(['user_id' => $userId]);
        
        $headerEncoded = $this->base64UrlEncode($header);
        $payloadEncoded = $this->base64UrlEncode($payload);
        
        $signature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, $this->apiSecret, true);
        $signatureEncoded = $this->base64UrlEncode($signature);
        
        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }

    /**
     * Create or update Stream user
     */
    public function upsertUser(User $user)
    {
        $userData = [
            'id' => (string)$user->id,
            'name' => $user->name,
            'role' => $user->role,
            'image' => $this->getAvatarUrl($user),
        ];

        return $this->makeRequest('POST', '/users', [
            'users' => [(string)$user->id => $userData]
        ]);
    }

    /**
     * Create a channel for patient consultation
     */
    public function createChannel($patientId, $createdBy, $channelName)
    {
        $channelId = 'patient_' . $patientId . '_' . time();
        
        $channelData = [
            'type' => 'patient_consultation',
            'id' => $channelId,
            'created_by_id' => (string)$createdBy,
            'name' => $channelName,
            'custom' => [
                'patient_id' => $patientId,
                'created_at' => now()->toISOString(),
            ]
        ];

        $response = $this->makeRequest('POST', "/channels/patient_consultation/{$channelId}", $channelData);
        
        return [
            'channel_id' => $channelId,
            'channel_type' => 'patient_consultation',
            'response' => $response
        ];
    }

    /**
     * Add members to a channel
     */
    public function addMembersToChannel($channelType, $channelId, array $userIds)
    {
        return $this->makeRequest('POST', "/channels/{$channelType}/{$channelId}", [
            'add_members' => array_map('strval', $userIds)
        ]);
    }

    /**
     * Remove members from a channel
     */
    public function removeMembersFromChannel($channelType, $channelId, array $userIds)
    {
        return $this->makeRequest('POST', "/channels/{$channelType}/{$channelId}", [
            'remove_members' => array_map('strval', $userIds)
        ]);
    }

    /**
     * Get user's channels
     */
    public function getUserChannels($userId)
    {
        return $this->makeRequest('GET', '/channels', [
            'filter_conditions' => [
                'members' => ['$in' => [(string)$userId]]
            ],
            'sort' => [['last_message_at' => -1]]
        ]);
    }

    /**
     * Make HTTP request to Stream API
     */
    private function makeRequest($method, $endpoint, $data = [])
    {
        $url = $this->baseUrl . $endpoint;
        $timestamp = time();
        
        // Create auth signature
        $authString = $method . $endpoint . json_encode($data) . $timestamp;
        $signature = hash_hmac('sha256', $authString, $this->apiSecret);
        
        $headers = [
            'Content-Type' => 'application/json',
            'X-Stream-Client' => 'stream-laravel-' . app()->version(),
            'Authorization' => $this->apiKey . ' ' . $signature,
            'Stream-Auth-Type' => 'jwt',
            'X-Stream-Date' => $timestamp,
        ];

        try {
            $response = $this->client->request($method, $url, [
                'headers' => $headers,
                'json' => $data,
                'timeout' => config('stream.timeout', 6.0)
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            \Log::error('Stream API Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Base64 URL encode
     */
    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Get avatar URL for user
     */
    private function getAvatarUrl(User $user)
    {
        // You can customize this to return actual avatar URLs
        return 'https://getstream.io/random_svg/?id=' . $user->id . '&name=' . urlencode($user->name);
    }
}