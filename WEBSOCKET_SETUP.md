# WebSocket Setup Guide

## Overview
The chat system now uses **Laravel Reverb** (WebSocket) for real-time messaging instead of aggressive polling. This eliminates the lag caused by fetching messages every 1 second.

## What Changed

### Before:
- ❌ Polling every **1 second** for chat messages (caused lag)
- ❌ Duplicate `chatComponent()` and `gameComponent()` handling chat separately
- ❌ High server load from constant HTTP requests

### After:
- ✅ **Real-time WebSocket** updates via Laravel Reverb
- ✅ Messages appear instantly when sent
- ✅ Light fallback polling every **30 seconds** (only to catch missed messages)
- ✅ Unified chat handling in `gameComponent()`
- ✅ Much lower server load

## How to Run

### 1. Start Laravel Reverb Server
In a **separate terminal**, run:
```bash
php artisan reverb:start
```

You should see:
```
Starting Reverb server on 0.0.0.0:8080
```

**Keep this terminal running** while you use the application.

### 2. Start Your Laravel Application
In another terminal:
```bash
php artisan serve
```

### 3. Build Frontend Assets (if needed)
If you haven't already:
```bash
npm install
npm run dev
```

## How It Works

### Backend (Already Configured)
1. **Event Broadcasting**: When a user sends a chat message, the `ChatMessageSent` event is broadcast to all users in the game
2. **Private Channel**: Uses `game.{gameId}` private channel for security
3. **Channel Authorization**: Only players in the game can listen to messages

### Frontend (Updated)
1. **Laravel Echo**: Connects to Reverb WebSocket server
2. **Real-time Listener**: Listens for `.chat.message` events
3. **Instant Updates**: New messages appear immediately without polling
4. **Fallback**: Light polling every 30 seconds as backup

## Testing

1. Open the game in **two different browsers** (or incognito mode)
2. Log in as different users in each browser
3. Join the same game room
4. Start the game and wait for discussion phase
5. Send a message from one browser
6. **The message should appear instantly** in the other browser without delay

## Troubleshooting

### Phase changes not updating automatically?
**IMPORTANT**: After making code changes, you must:
1. **Restart Reverb server**: Stop it (Ctrl+C) and run `php artisan reverb:start` again
2. **Clear browser cache** or do a hard refresh (Ctrl+F5)
3. **Check browser console** (F12) for WebSocket connection status

### Messages not appearing in real-time?
1. Check if Reverb server is running: `php artisan reverb:start`
2. Open browser console (F12) and look for:
   - `Chat message received via WebSocket` - means it's working!
   - `Phase changed event received` - means phase changes are working!
   - `Laravel Echo is not initialized` - means Echo failed to load
3. Check `.env` file has correct Reverb configuration:
   ```
   BROADCAST_CONNECTION=reverb
   REVERB_APP_ID=my-app-id
   REVERB_APP_KEY=my-app-key
   REVERB_APP_SECRET=my-app-secret
   REVERB_HOST="localhost"
   REVERB_PORT=8080
   ```

### Verify WebSocket Connection
Open browser console and type:
```javascript
// Check if Echo is loaded
console.log(window.Echo);

// Check active connections
console.log(window.Echo.connector.pusher.connection.state);
// Should show: "connected"
```

### Still using polling?
If WebSocket fails to connect, the system automatically falls back to polling every 3 seconds (much better than 1 second). Check browser console for:
```
Using polling fallback for real-time updates
```

### Vote buttons not appearing after phase change?
This means the WebSocket event is not being received. Check:
1. Is Reverb server running?
2. Open browser console - do you see "Phase changed event received"?
3. Try manually refreshing the page (F5) - vote buttons should appear
4. If manual refresh works but auto-reload doesn't, restart Reverb server

## Performance Comparison

### Before (Polling):
- **1 HTTP request per second** per user
- 10 users = 10 requests/second
- 60 requests/minute per user
- High server CPU usage

### After (WebSocket):
- **1 WebSocket connection** per user (persistent)
- Messages pushed instantly when sent
- 1 fallback request every 30 seconds
- Minimal server CPU usage

## Additional Notes

- The WebSocket connection is **automatically established** when you open the game page
- The connection is **automatically closed** when you leave the page
- Messages are **deduplicated** to prevent showing the same message twice
- The system gracefully handles **connection drops** and reconnects automatically

## Production Deployment

For production, you'll need to:
1. Run Reverb as a background service (using Supervisor or similar)
2. Configure SSL/TLS for secure WebSocket connections (wss://)
3. Update `.env` with production Reverb host and ports
4. Consider using Laravel Horizon for queue management

---

**Enjoy lag-free real-time chat! 🚀**
