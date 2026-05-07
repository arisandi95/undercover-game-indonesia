# Undercover Web Game - Technical Instructions

## 1. Game Overview

**Undercover** is a social deduction web-based game where players work together to identify hidden "undercover" players among them. One player is the "Mr. White" (knows nothing), one is the "Undercover" (knows a different word), and the rest are "Civilians" (know the correct word).

### Core Mechanics
- **Word-based gameplay**: Civilians know a secret word, Undercover knows a different word, Mr. White knows nothing
- **Discussion phase**: Players discuss clues about their word without revealing it
- **Voting phase**: Players vote to eliminate someone they suspect is undercover
- **Win conditions**:
  - Civilians win if they eliminate both Undercover and Mr. White
  - Undercover wins if they survive until the end or eliminate all Civilians
  - Mr. White wins if they survive until the end

---

## 2. System Architecture

### 2.1 Tech Stack
- **Frontend**: Blade Templates with Alpine.js or Livewire
- **Backend**: Laravel 11+ with PHP 8.2+
- **Real-time Communication**: Laravel WebSockets or Pusher
- **Database**: MySQL/PostgreSQL for persistence
- **Authentication**: Laravel Sanctum (API tokens) or Session-based
- **Deployment**: Traditional hosting (Shared/VPS/Dedicated)

### 2.2 Architecture Diagram
```
┌─────────────────────────────────────────────────────────────┐
│              Client Layer (Blade + Alpine.js)                │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐       │
│  │ Game Lobby   │  │ Game Room    │  │ Game Board   │       │
│  └──────────────┘  └──────────────┘  └──────────────┘       │
└─────────────────────────────────────────────────────────────┘
                      ↕ WebSocket/AJAX
┌─────────────────────────────────────────────────────────────┐
│                    Laravel Application                       │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐       │
│  │ Auth         │  │ Game Logic   │  │ Room Manager │       │
│  └──────────────┘  └──────────────┘  └──────────────┘       │
└─────────────────────────────────────────────────────────────┘
                            ↕ SQL
┌─────────────────────────────────────────────────────────────┐
│                  MySQL/PostgreSQL Database                   │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐       │
│  │ Users        │  │ Games        │  │ Rooms        │       │
│  └──────────────┘  └──────────────┘  └──────────────┘       │
└─────────────────────────────────────────────────────────────┘
```

---

## 3. Database Schema

### 3.1 Users Table
```sql
CREATE TABLE users (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  avatar_url VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  is_active BOOLEAN DEFAULT true
);
```

### 3.2 Rooms Table
```sql
CREATE TABLE rooms (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  host_id BIGINT UNSIGNED NOT NULL REFERENCES users(id),
  status ENUM('waiting', 'in_progress', 'finished') DEFAULT 'waiting',
  max_players INT DEFAULT 8,
  current_players INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 3.3 Games Table
```sql
CREATE TABLE games (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  room_id BIGINT UNSIGNED NOT NULL REFERENCES rooms(id),
  round INT DEFAULT 1,
  status ENUM('setup', 'discussion', 'voting', 'finished') DEFAULT 'setup',
  civilian_word VARCHAR(100) NOT NULL,
  undercover_word VARCHAR(100) NOT NULL,
  current_phase_end_time TIMESTAMP NULL,
  winner ENUM('civilians', 'undercover', 'mr_white') NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 3.4 Game Players Table
```sql
CREATE TABLE game_players (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  game_id BIGINT UNSIGNED NOT NULL REFERENCES games(id),
  user_id BIGINT UNSIGNED NOT NULL REFERENCES users(id),
  role ENUM('civilian', 'undercover', 'mr_white') NOT NULL,
  is_alive BOOLEAN DEFAULT true,
  votes_received INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_game_player (game_id, user_id)
);
```

### 3.5 Votes Table
```sql
CREATE TABLE votes (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  game_id BIGINT UNSIGNED NOT NULL REFERENCES games(id),
  voter_id BIGINT UNSIGNED NOT NULL REFERENCES users(id),
  voted_for_id BIGINT UNSIGNED NOT NULL REFERENCES users(id),
  round INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_vote (game_id, voter_id, round)
);
```

### 3.6 Chat Messages Table
```sql
CREATE TABLE chat_messages (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  game_id BIGINT UNSIGNED NOT NULL REFERENCES games(id),
  user_id BIGINT UNSIGNED NOT NULL REFERENCES users(id),
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 4. Routes & Controllers

### 4.1 Authentication Routes
```
POST   /register                   - Register new user
POST   /login                      - Login user
POST   /logout                     - Logout user
GET    /profile                    - Get current user profile
PUT    /profile                    - Update user profile
```

### 4.2 Room Routes
```
GET    /rooms                      - List all available rooms
POST   /rooms                      - Create new room
GET    /rooms/{id}                 - Get room details
PUT    /rooms/{id}                 - Update room settings
DELETE /rooms/{id}                 - Delete room (host only)
POST   /rooms/{id}/join            - Join room
POST   /rooms/{id}/leave           - Leave room
GET    /rooms/{id}/players         - Get room players
```

### 4.3 Game Routes
```
POST   /games                      - Start new game
GET    /games/{id}                 - Get game state
PUT    /games/{id}/status          - Update game status
POST   /games/{id}/vote            - Submit vote
GET    /games/{id}/results         - Get game results
POST   /games/{id}/chat            - Send chat message
GET    /games/{id}/chat            - Get chat history
```

### 4.4 Word Management Routes
```
GET    /admin/words                - List word pairs (admin)
POST   /admin/words                - Add new word pair (admin)
DELETE /admin/words/{id}           - Delete word pair (admin)
```

---

## 5. WebSocket Events (Laravel WebSockets)

### 5.1 Room Events
```javascript
// Client → Server (Pusher Channel)
Pusher.subscribe('room.' + roomId).trigger('client-room:join', { userId, username })
Pusher.subscribe('room.' + roomId).trigger('client-room:leave', { userId })
Pusher.subscribe('room.' + roomId).trigger('client-room:ready', { userId })

// Server → Client (Broadcast)
// Listen on channel: room.{roomId}
'room:player_joined' → { player, totalPlayers }
'room:player_left' → { player, totalPlayers }
'room:game_starting' → { gameId, players }
'room:updated' → { room }
```

### 5.2 Game Events
```javascript
// Client → Server (Pusher Channel)
Pusher.subscribe('game.' + gameId).trigger('client-game:vote', { votedForId })
Pusher.subscribe('game.' + gameId).trigger('client-game:chat', { message })
Pusher.subscribe('game.' + gameId).trigger('client-game:ready_next_round', {})

// Server → Client (Broadcast)
// Listen on channel: game.{gameId}
'game:phase_changed' → { phase, timeRemaining }
'game:player_eliminated' → { player, role }
'game:vote_received' → { voter, votedFor }
'game:round_results' → { eliminated, votes }
'game:finished' → { winner, results }
'game:chat_message' → { user, message, timestamp }
```

---

## 6. Game Flow & State Machine

### 6.1 Game Phases
```
1. SETUP (30s)
   - Assign roles to players
   - Send role-specific words to clients
   - Display countdown

2. DISCUSSION (120s)
   - Players discuss their word
   - Chat enabled
   - No voting

3. VOTING (60s)
   - Players vote for elimination
   - Chat disabled
   - Real-time vote count

4. RESULTS (30s)
   - Show eliminated player and their role
   - Check win conditions
   - Prepare for next round or end game

5. FINISHED
   - Display final results
   - Show game statistics
   - Option to play again
```

### 6.2 Win Conditions
```
Civilians Win:
  - Both Undercover and Mr. White eliminated

Undercover Wins:
  - All Civilians eliminated
  - Survives to end with Mr. White

Mr. White Wins:
  - Survives to end with Undercover
  - All Civilians eliminated
```

---

## 7. Frontend Components (Blade Templates)

### 7.1 Page Structure
```
layouts/
├── app.blade.php              - Main layout with navigation
├── auth.blade.php             - Auth layout (login/register)
└── game.blade.php             - Game layout

pages/
├── auth/
│   ├── login.blade.php
│   ├── register.blade.php
│   └── profile.blade.php
├── lobby/
│   ├── index.blade.php        - Room list
│   ├── create.blade.php       - Create room form
│   └── show.blade.php         - Room details
├── game/
│   ├── index.blade.php        - Main game board
│   ├── chat.blade.php         - Chat panel
│   ├── voting.blade.php       - Voting interface
│   └── results.blade.php      - Game results
└── admin/
    └── words.blade.php        - Word management

components/
├── player-list.blade.php
├── game-board.blade.php
├── chat-panel.blade.php
├── voting-panel.blade.php
├── phase-timer.blade.php
└── game-stats.blade.php
```

### 7.2 Key Blade Components
```blade
<!-- PlayerList Component -->
@component('components.player-list', [
    'players' => $players,
    'currentUserId' => auth()->id(),
    'gameId' => $game->id
])
@endcomponent

<!-- ChatPanel Component -->
@component('components.chat-panel', [
    'gameId' => $game->id,
    'messages' => $messages,
    'isEnabled' => $game->status === 'discussion'
])
@endcomponent

<!-- VotingPanel Component -->
@component('components.voting-panel', [
    'players' => $alivePlayers,
    'gameId' => $game->id,
    'hasVoted' => $userHasVoted
])
@endcomponent

<!-- PhaseTimer Component -->
@component('components.phase-timer', [
    'phase' => $game->status,
    'endTime' => $game->current_phase_end_time
])
@endcomponent
```

---

## 8. Security Considerations

### 8.1 Authentication & Authorization
- Use Laravel Sanctum for API token authentication
- Session-based auth for web routes with CSRF protection
- Validate user permissions before game actions
- Rate limit API endpoints using Laravel throttle middleware (100 req/min per user)
- Implement middleware for role-based access control

### 8.2 Data Protection
- Hash passwords with Laravel's Hash facade (bcrypt, 12 rounds default)
- Encrypt sensitive data using Laravel's encryption
- Use HTTPS/TLS for all communications
- Validate all user inputs using Laravel validation rules
- Use prepared statements to prevent SQL injection

### 8.3 Game Integrity
- Server-side role assignment in GameService (never trust client)
- Prevent vote manipulation with server validation
- Log all game actions using Laravel logging
- Implement anti-cheat measures (timing validation, duplicate vote prevention)
- Use database transactions for atomic operations

### 8.4 WebSocket Security (Pusher/Laravel WebSockets)
- Authenticate all WebSocket connections using Laravel auth
- Validate room/game access before broadcasting events
- Implement connection rate limiting
- Sanitize all chat messages using Laravel's sanitization helpers
- Use private channels for sensitive game data

---

## 9. Error Handling

### 9.1 Error Categories
```typescript
enum ErrorCode {
  INVALID_CREDENTIALS = 'AUTH_001',
  ROOM_FULL = 'ROOM_001',
  GAME_NOT_FOUND = 'GAME_001',
  INVALID_VOTE = 'VOTE_001',
  UNAUTHORIZED = 'AUTH_002',
  SERVER_ERROR = 'SERVER_001'
}

interface ErrorResponse {
  code: ErrorCode;
  message: string;
  details?: Record<string, any>;
}
```

### 9.2 Error Handling Strategy
- Log all errors with context (user, action, timestamp)
- Return user-friendly error messages
- Never expose sensitive system information
- Implement retry logic for transient failures
- Monitor error rates and alert on anomalies

---

## 10. Performance Optimization

### 10.1 Frontend (Blade + Alpine.js)
- Use Alpine.js for lightweight interactivity
- Implement lazy loading for images
- Cache game state in browser localStorage
- Debounce chat input (300ms)
- Use Blade caching for static components
- Minimize JavaScript bundle size

### 10.2 Backend (Laravel)
- Database query optimization with eager loading (with())
- Use database indexes on frequently queried columns
- Implement query caching with Redis
- Use Laravel's query builder for efficient queries
- Batch vote processing in transactions
- Implement pagination for chat history (50 messages per page)
- Use Laravel's view caching in production

### 10.3 Real-time Communication
- Use Pusher or Laravel WebSockets for efficient broadcasting
- Compress large payloads using gzip
- Implement heartbeat/ping-pong (30s interval)
- Graceful reconnection with exponential backoff
- Use private channels to reduce broadcast overhead

---

## 11. Testing Strategy

### 11.1 Unit Tests (PHPUnit)
- Test game logic (role assignment, win conditions)
- Test vote calculation and elimination logic
- Test authentication flows
- Test model relationships and scopes
- Target: 80% code coverage

### 11.2 Integration Tests (PHPUnit)
- Test API routes with database
- Test WebSocket event broadcasting
- Test game state transitions
- Test error scenarios and validation
- Test concurrent game operations

### 11.3 Feature Tests (Laravel Dusk - Optional)
- Test complete game flow (4-8 players)
- Test disconnection/reconnection
- Test concurrent games
- Test UI interactions with Alpine.js
- Test form submissions and validations

---

## 12. Deployment & Infrastructure

### 12.1 Server Requirements
- PHP 8.2+ with extensions: OpenSSL, PDO, Mbstring, Tokenizer, JSON, Ctype, BCMath
- MySQL 8.0+ or PostgreSQL 12+
- Composer for dependency management
- Node.js 18+ (for frontend build tools)
- Redis (optional, for caching and sessions)

### 12.2 Environment Variables (.env)
```
APP_NAME=Undercover
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=undercover
DB_USERNAME=root
DB_PASSWORD=<strong-password>

CACHE_DRIVER=redis
SESSION_DRIVER=cookie
QUEUE_CONNECTION=sync

PUSHER_APP_ID=<pusher-id>
PUSHER_APP_KEY=<pusher-key>
PUSHER_APP_SECRET=<pusher-secret>
PUSHER_APP_CLUSTER=mt1

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=<username>
MAIL_PASSWORD=<password>
```

### 12.3 Deployment Steps
1. Clone repository to server
2. Run `composer install --no-dev`
3. Copy `.env.example` to `.env` and configure
4. Run `php artisan key:generate`
5. Run `php artisan migrate --force`
6. Run `php artisan db:seed` (optional)
7. Set proper file permissions (storage, bootstrap/cache)
8. Configure web server (Apache/Nginx)
9. Set up SSL certificate (Let's Encrypt)
10. Configure cron job for Laravel scheduler
11. Set up queue worker (if using async jobs)

---

## 13. Monitoring & Analytics

### 13.1 Metrics to Track
- Active players/games (real-time)
- Average game duration
- Player win rates by role
- API response times
- WebSocket connection stability
- Error rates by type
- Database query performance
- Server resource usage (CPU, memory, disk)

### 13.2 Logging
- Use Laravel's logging system (stack driver)
- Log levels: debug, info, notice, warning, error, critical, alert, emergency
- Include request IDs for tracing
- Store logs in `/storage/logs` directory
- Implement log rotation (daily, keep 14 days)
- Monitor logs using tools like Papertrail or Sentry

### 13.3 Tools & Services
- Laravel Telescope (development debugging)
- Sentry (error tracking)
- New Relic or DataDog (APM)
- Google Analytics (user behavior)
- Uptime monitoring (Pingdom, UptimeRobot)

---

## 14. Development Roadmap

### Phase 1: MVP (Weeks 1-4)
- [ ] Basic authentication
- [ ] Room creation and joining
- [ ] Game mechanics (roles, voting)
- [ ] Simple UI
- [ ] Local testing

### Phase 2: Enhancement (Weeks 5-8)
- [ ] Chat system
- [ ] Game statistics
- [ ] User profiles
- [ ] Word management
- [ ] UI polish

### Phase 3: Production (Weeks 9-12)
- [ ] Performance optimization
- [ ] Security hardening
- [ ] Deployment setup
- [ ] Monitoring/analytics
- [ ] Public launch

---

## 15. Code Standards & Guidelines

### 15.1 Naming Conventions
- **Variables/Functions**: camelCase
- **Classes/Models**: PascalCase
- **Constants**: UPPER_SNAKE_CASE
- **Database tables**: snake_case (plural)
- **Database columns**: snake_case
- **Routes**: kebab-case
- **Blade files**: kebab-case

### 15.2 Error Handling
- Always use try/catch for database operations
- Use Laravel's exception handling
- Log errors with full context using Log facade
- Return meaningful error messages to users
- Never expose stack traces to clients in production
- Use custom exceptions for domain-specific errors

### 15.3 Code Organization
```
app/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   ├── Resources/
│   └── Middleware/
├── Models/
├── Services/
├── Events/
├── Listeners/
├── Jobs/
└── Exceptions/

database/
├── migrations/
├── seeders/
└── factories/

resources/
├── views/
│   ├── layouts/
│   ├── pages/
│   ├── components/
│   └── emails/
├── css/
└── js/

routes/
├── web.php
├── api.php
└── channels.php

tests/
├── Unit/
├── Feature/
└── Dusk/
```

### 15.4 Commit Message Format
```
<type>(<scope>): <subject>

<body>

<footer>

Types: feat, fix, docs, style, refactor, test, chore
Example: feat(game): implement voting system
```

### 15.5 Laravel Best Practices
- Use Models with relationships instead of raw queries
- Use Service classes for business logic
- Use Form Requests for validation
- Use API Resources for response formatting
- Use Events and Listeners for decoupled code
- Use Jobs for long-running tasks
- Use Middleware for cross-cutting concerns
- Use Scopes for reusable query logic
- Use Factories and Seeders for testing data
- Use Migrations for database schema changes

---

## 16. Change Control & Review Process

After any implementation or update:

1. **Review Changes**: Display summary of modified files
2. **User Decision**:
   - ✅ **Accept** → Approve and save changes
   - ↩️ **Revert** → Cancel changes and restore previous state
3. **Audit Trail**: Record all decisions in log

---

## 17. Getting Started

### Prerequisites
- PHP 8.2+
- MySQL 8.0+ or PostgreSQL 12+
- Composer
- Node.js 18+ (for frontend build tools)
- Redis (optional, for caching)

### Quick Start
```bash
# Clone repository
git clone <repo-url>
cd undercover-yuvitegame

# Install PHP dependencies
composer install

# Install Node dependencies (for frontend build)
npm install

# Setup environment
cp .env.example .env

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Seed database with word pairs
php artisan db:seed

# Build frontend assets
npm run build

# Start development server
php artisan serve

# In another terminal, start queue worker (if using jobs)
php artisan queue:work

# Run tests
php artisan test
```

### Development Commands
```bash
# Create new migration
php artisan make:migration create_table_name

# Create new model with migration
php artisan make:model ModelName -m

# Create new controller
php artisan make:controller ControllerName

# Create new service class
php artisan make:class Services/ServiceName

# Create new event
php artisan make:event EventName

# Create new listener
php artisan make:listener ListenerName

# Run specific test
php artisan test tests/Feature/GameTest.php

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## 18. Key Laravel Packages

### Required Packages
```json
{
  "laravel/framework": "^11.0",
  "laravel/sanctum": "^4.0",
  "pusher/pusher-php-server": "^7.0",
  "laravel-websockets": "^2.0"
}
```

### Development Packages
```json
{
  "laravel/telescope": "^4.0",
  "laravel/dusk": "^8.0",
  "phpunit/phpunit": "^10.0",
  "fakerphp/faker": "^1.0"
}
```

### Installation
```bash
# Install Sanctum for API authentication
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Install WebSockets for real-time features
composer require beyondcode/laravel-websockets
php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider"

# Or use Pusher (requires account)
composer require pusher/pusher-php-server
```

---

## 19. Database Seeding

### Word Pairs Seeder
```php
// database/seeders/WordPairSeeder.php
public function run(): void
{
    $wordPairs = [
        ['civilian' => 'apple', 'undercover' => 'orange', 'difficulty' => 'easy'],
        ['civilian' => 'cat', 'undercover' => 'dog', 'difficulty' => 'easy'],
        ['civilian' => 'summer', 'undercover' => 'winter', 'difficulty' => 'medium'],
        ['civilian' => 'doctor', 'undercover' => 'nurse', 'difficulty' => 'medium'],
        ['civilian' => 'piano', 'undercover' => 'guitar', 'difficulty' => 'hard'],
        ['civilian' => 'democracy', 'undercover' => 'dictatorship', 'difficulty' => 'hard'],
    ];

    foreach ($wordPairs as $pair) {
        WordPair::create($pair);
    }
}
```

---

## 20. API Response Format

### Success Response
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Game Room",
    "status": "waiting"
  },
  "message": "Operation successful"
}
```

### Error Response
```json
{
  "success": false,
  "error": {
    "code": "ROOM_001",
    "message": "Room not found",
    "details": {}
  }
}
```

### Validation Error Response
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Validation failed",
    "details": {
      "name": ["The name field is required"],
      "max_players": ["The max players must be between 2 and 20"]
    }
  }
}
```

---

## 21. Game Service Architecture

### GameService Class
```php
// app/Services/GameService.php
class GameService
{
    public function startGame(Room $room): Game
    {
        // Assign roles to players
        // Select word pair
        // Create game record
        // Broadcast game started event
    }

    public function processVote(Game $game, User $voter, User $votedFor): void
    {
        // Record vote
        // Check if voting phase is complete
        // Eliminate player if majority reached
        // Check win conditions
    }

    public function checkWinConditions(Game $game): ?string
    {
        // Return 'civilians', 'undercover', 'mr_white', or null
    }

    public function eliminatePlayer(Game $game, User $player): void
    {
        // Mark player as not alive
        // Broadcast elimination event
        // Check win conditions
    }
}
```

---

## 22. Event Broadcasting

### Game Events
```php
// app/Events/GameStarted.php
class GameStarted implements ShouldBroadcast
{
    public function broadcastOn(): array
    {
        return [new PrivateChannel('game.' . $this->game->id)];
    }

    public function broadcastWith(): array
    {
        return [
            'gameId' => $this->game->id,
            'phase' => $this->game->status,
            'players' => $this->game->players,
        ];
    }
}

// app/Events/PlayerVoted.php
class PlayerVoted implements ShouldBroadcast
{
    public function broadcastOn(): array
    {
        return [new PrivateChannel('game.' . $this->game->id)];
    }
}

// app/Events/PlayerEliminated.php
class PlayerEliminated implements ShouldBroadcast
{
    public function broadcastOn(): array
    {
        return [new PrivateChannel('game.' . $this->game->id)];
    }
}
```

---

## 23. Middleware

### Game Authorization Middleware
```php
// app/Http/Middleware/GameAuthorization.php
public function handle(Request $request, Closure $next)
{
    $game = Game::findOrFail($request->route('id'));
    
    if (!$game->players()->where('user_id', auth()->id())->exists()) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    return $next($request);
}
```

### Rate Limiting
```php
// routes/api.php
Route::middleware('throttle:100,1')->group(function () {
    Route::post('/games/{id}/vote', [GameController::class, 'vote']);
    Route::post('/games/{id}/chat', [GameController::class, 'chat']);
});
```

---

**Last Updated**: 2026-05-07
**Version**: 2.0.0 (Laravel Edition)
