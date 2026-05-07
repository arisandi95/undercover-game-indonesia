# Undercover Game - Laravel Implementation Guide

This document provides a quick reference for implementing the Undercover game using Laravel.

## Project Structure

```
undercover-yuvitegame/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── RoomController.php
│   │   │   ├── GameController.php
│   │   │   └── WordController.php
│   │   ├── Requests/
│   │   ├── Resources/
│   │   └── Middleware/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Room.php
│   │   ├── Game.php
│   │   ├── GamePlayer.php
│   │   ├── Vote.php
│   │   ├── ChatMessage.php
│   │   └── WordPair.php
│   ├── Services/
│   │   ├── GameService.php
│   │   ├── RoomService.php
│   │   └── VoteService.php
│   ├── Events/
│   │   ├── GameStarted.php
│   │   ├── PlayerVoted.php
│   │   ├── PlayerEliminated.php
│   │   └── GameFinished.php
│   └── Listeners/
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   ├── auth/
│   │   ├── lobby/
│   │   ├── game/
│   │   └── components/
│   ├── css/
│   └── js/
├── routes/
│   ├── web.php
│   ├── api.php
│   └── channels.php
├── tests/
│   ├── Unit/
│   └── Feature/
├── .env.example
├── composer.json
└── package.json
```

## Implementation Phases

### Phase 1: Setup & Authentication (Week 1)
- [ ] Initialize Laravel project
- [ ] Setup database and migrations
- [ ] Implement user authentication (Sanctum)
- [ ] Create User model and auth routes
- [ ] Setup basic Blade templates

### Phase 2: Room Management (Week 2)
- [ ] Create Room model and migrations
- [ ] Implement room CRUD operations
- [ ] Create room listing and joining logic
- [ ] Build room UI components
- [ ] Setup room broadcasting events

### Phase 3: Game Logic (Week 3)
- [ ] Create Game and GamePlayer models
- [ ] Implement game initialization and role assignment
- [ ] Create game phase management (setup, discussion, voting, results)
- [ ] Implement voting system
- [ ] Create win condition logic

### Phase 4: Real-time Features (Week 4)
- [ ] Setup Laravel WebSockets or Pusher
- [ ] Implement game event broadcasting
- [ ] Create chat system with real-time updates
- [ ] Build voting UI with live updates
- [ ] Implement phase timer and transitions

### Phase 5: Frontend & Polish (Week 5)
- [ ] Build game board UI with Alpine.js
- [ ] Create player list and voting interface
- [ ] Implement chat panel
- [ ] Add game results and statistics
- [ ] Polish UI/UX and animations

### Phase 6: Testing & Deployment (Week 6)
- [ ] Write unit tests for game logic
- [ ] Write feature tests for API endpoints
- [ ] Setup error handling and logging
- [ ] Configure production environment
- [ ] Deploy to hosting

## Key Implementation Details

### Database Relationships
```
User
  ├── hasMany Room (as host)
  ├── hasMany GamePlayer
  ├── hasMany Vote (as voter)
  ├── hasMany Vote (as votedFor)
  └── hasMany ChatMessage

Room
  ├── belongsTo User (host)
  ├── hasMany Game
  └── hasMany GamePlayer (through Game)

Game
  ├── belongsTo Room
  ├── hasMany GamePlayer
  ├── hasMany Vote
  └── hasMany ChatMessage

GamePlayer
  ├── belongsTo Game
  ├── belongsTo User
  └── hasMany Vote (as voter)

Vote
  ├── belongsTo Game
  ├── belongsTo User (voter)
  └── belongsTo User (votedFor)

ChatMessage
  ├── belongsTo Game
  └── belongsTo User

WordPair
  (standalone model for word management)
```

### Game State Machine
```
SETUP (30s)
  ↓
DISCUSSION (120s)
  ↓
VOTING (60s)
  ↓
RESULTS (30s)
  ↓
FINISHED or back to SETUP for next round
```

### Role Assignment Logic
- Total players: N
- Civilians: N - 2
- Undercover: 1
- Mr. White: 1

### Vote Elimination Logic
- Count votes for each player
- Player with most votes is eliminated
- If tie, use tiebreaker (e.g., first to reach majority)
- Mark player as not alive
- Check win conditions

### Win Conditions
```
Civilians Win:
  - Both Undercover and Mr. White are eliminated

Undercover Wins:
  - All Civilians are eliminated
  - OR Undercover survives to end with Mr. White

Mr. White Wins:
  - Survives to end with Undercover
  - OR All Civilians are eliminated
```

## Important Notes

1. **Server-side Role Assignment**: Never send roles to client before game starts. Only send to specific user after game initialization.

2. **Vote Validation**: Always validate votes server-side. Check:
   - User is in the game
   - User is alive
   - Voted player is alive
   - User hasn't already voted this round

3. **Chat Sanitization**: Sanitize all chat messages to prevent XSS attacks.

4. **Concurrent Games**: Use database transactions to prevent race conditions when processing votes.

5. **Broadcasting**: Use private channels for game-specific events to prevent unauthorized access.

6. **Error Handling**: Implement comprehensive error handling with meaningful error codes and messages.

7. **Logging**: Log all game actions for debugging and analytics.

## Testing Checklist

- [ ] User registration and login
- [ ] Room creation and joining
- [ ] Game initialization with correct role assignment
- [ ] Discussion phase with chat
- [ ] Voting phase with vote counting
- [ ] Player elimination logic
- [ ] Win condition detection
- [ ] Game results display
- [ ] Concurrent game handling
- [ ] WebSocket reconnection
- [ ] Error scenarios

## Performance Considerations

1. Use eager loading (with()) to prevent N+1 queries
2. Cache word pairs in Redis
3. Implement pagination for chat history
4. Use database indexes on frequently queried columns
5. Batch vote processing in transactions
6. Implement rate limiting on API endpoints
7. Use view caching for static Blade components

## Security Checklist

- [ ] Validate all user inputs
- [ ] Use CSRF protection on forms
- [ ] Implement rate limiting
- [ ] Hash passwords with bcrypt
- [ ] Use HTTPS in production
- [ ] Sanitize chat messages
- [ ] Validate game access permissions
- [ ] Log security events
- [ ] Use environment variables for secrets
- [ ] Implement proper error handling (no stack traces to users)

---

**Version**: 1.0.0
**Last Updated**: 2026-05-07
