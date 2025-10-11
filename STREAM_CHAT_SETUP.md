# Stream Chat Setup Guide

## Overview
This HIMS system now includes both **Legacy Chat** and **Stream Chat** implementations. Stream Chat provides a professional, real-time messaging solution with advanced features like typing indicators, read receipts, and file sharing.

## What's Been Implemented

### 1. Backend Components
- ✅ **StreamChatService** (`app/Services/StreamChatService.php`) - JWT token generation, user management, channel operations
- ✅ **StreamChatController** (`app/Http/Controllers/StreamChatController.php`) - Full CRUD operations for Stream Chat
- ✅ **Stream Configuration** (`config/stream.php`) - API key, secret, and region settings

### 2. Frontend Components  
- ✅ **Stream Chat Views** (`resources/views/stream-chat/`) - Complete UI with JavaScript SDK integration
- ✅ **Doctor Navigation** - Both "Messages (Legacy)" and "Stream Chat" options available
- ✅ **Patient Integration** - Both "Legacy Chat" and "Stream Chat" buttons on patient details

### 3. Routing System
- ✅ **Legacy Routes** - `/doctor/chat/*` (preserved for existing functionality)
- ✅ **Stream Routes** - `/doctor/stream-chat/*` (new Stream Chat implementation)

## Setup Instructions

### Step 1: Create Stream Chat Account
1. Go to [https://getstream.io/chat/](https://getstream.io/chat/)
2. Sign up for a free account (100 MAU free tier)
3. Create a new app for your HIMS system
4. Navigate to your app dashboard

### Step 2: Get API Credentials
1. In your Stream dashboard, go to **"App Settings"**
2. Copy the following credentials:
   - **App ID** (e.g., `1234567`)
   - **API Key** (e.g., `abcdef123456`)
   - **API Secret** (e.g., `secret_key_here`)

### Step 3: Configure Environment Variables
Add these variables to your `.env` file:

```env
# Stream Chat Configuration
STREAM_APP_ID=your_app_id_here
STREAM_API_KEY=your_api_key_here
STREAM_API_SECRET=your_api_secret_here
STREAM_REGION=us-east-1
```

**Important**: Replace the placeholder values with your actual Stream Chat credentials.

### Step 4: Clear Configuration Cache
Run these commands in your terminal:

```bash
php artisan config:clear
php artisan cache:clear
```

## Testing the Implementation

### 1. Access Stream Chat
1. Login as a doctor
2. In the sidebar, click **"Stream Chat"** (not "Messages (Legacy)")
3. You should see the Stream Chat dashboard

### 2. Create Patient Chat
1. Go to **"Patients"** page
2. Select a patient from the list
3. Click **"Stream Chat"** button (blue button)
4. This should create/open a Stream Chat channel for that patient

### 3. Test Features
- ✅ **Real-time messaging** - Messages appear instantly
- ✅ **Add participants** - Add other doctors to the chat
- ✅ **Typing indicators** - See when others are typing
- ✅ **Message status** - Delivered/read receipts
- ✅ **Doctor-only access** - Only users with doctor role can participate

## Architecture Overview

### Dual Chat System
This implementation maintains **both** chat systems:

1. **Legacy Chat** - Your existing Laravel-based chat system
   - Database: `chat_rooms`, `chat_messages` tables
   - Routes: `/doctor/chat/*`
   - Models: `ChatRoom`, `ChatMessage`

2. **Stream Chat** - New professional messaging system
   - External: Stream API handles all data
   - Routes: `/doctor/stream-chat/*`
   - Service: `StreamChatService`

### Benefits of Stream Chat
- **Professional Grade**: Used by companies like Slack, Discord
- **Real-time**: WebSocket connections for instant messaging
- **Scalable**: Handles thousands of concurrent users
- **Feature Rich**: Typing indicators, read receipts, file sharing
- **Mobile Ready**: SDKs for iOS/Android when needed
- **Reliable**: 99.99% uptime SLA

## Troubleshooting

### Common Issues

#### 1. "JWT Token Error"
- Check if `STREAM_API_SECRET` is correctly set in `.env`
- Ensure no extra spaces in the secret key
- Run `php artisan config:clear`

#### 2. "App ID Not Found"
- Verify `STREAM_APP_ID` matches your Stream dashboard
- Check if the app is active in Stream dashboard

#### 3. "Failed to Create Channel"
- Ensure `STREAM_API_KEY` is correct
- Check network connectivity to Stream API
- Verify your Stream app has sufficient quota

#### 4. "User Not Found" 
- This is normal for new users - Stream creates them automatically
- Check if doctor's email is valid

### Debug Mode
To enable debug logging, add to `.env`:
```env
STREAM_DEBUG=true
```

## Security Notes

### JWT Token Security
- JWT tokens are generated server-side using your secret key
- Tokens expire after 1 hour for security
- Never expose your API secret in frontend code

### User Privacy
- Only doctors can access chat channels
- Patient data is not stored in Stream (only references)
- All messages are encrypted in transit

## Migration Considerations

### From Legacy to Stream Chat
If you want to migrate existing chat data:

1. **Export Legacy Data**: Extract messages from `chat_messages` table
2. **Import to Stream**: Use Stream's bulk import API
3. **Update References**: Redirect old chat links to new Stream channels

### Gradual Migration
You can run both systems in parallel:
- Keep legacy chat for existing conversations
- Use Stream Chat for new conversations
- Gradually migrate users over time

## Support

### Stream Chat Documentation
- [Stream Chat PHP Documentation](https://getstream.io/chat/docs/php/)
- [Stream Chat JavaScript SDK](https://getstream.io/chat/docs/javascript/)
- [Stream Chat API Reference](https://getstream.io/chat/docs/rest/)

### HIMS Integration Support
- Legacy chat code is preserved and unchanged
- Stream Chat is completely separate - no conflicts
- Both systems can run simultaneously

## Next Steps

1. **Configure API credentials** (Step 1-3 above)
2. **Test basic functionality** 
3. **Train users** on new Stream Chat features
4. **Monitor usage** and performance
5. **Consider mobile app integration** (Stream has native mobile SDKs)

---

**Note**: The legacy chat system remains fully functional. Stream Chat is an additional option that provides enhanced features and professional-grade messaging capabilities.