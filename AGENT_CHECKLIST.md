# Undercover Game - Agent Implementation Checklist

This document serves as a comprehensive checklist for AI agents implementing the Undercover game in Laravel.

## Phase 1: Project Setup & Database

### 1.1 Laravel Project Initialization
- [ ] Create new Laravel 11 project: `laravel new undercover-yuvitegame`
- [ ] Setup `.env` file with database credentials
- [ ] Configure database connection (MySQL/PostgreSQL)
- [ ] Run `php artisan key:generate`
- [ ] Setup Redis for caching (optional but recommended)

### 1.2 Database Migrations
- [ ] Create users table migration
- [ ] Create rooms table migration
- [ ] Create games table migration
- [ ] Create game_players table migration
- [ ] Create votes table migration
- [ ] Create chat_messages table migration
- [ ] Create word_pairs table migration
- [ ] Add indexes on foreign keys and frequently queried columns
- [ ] Run migrations: `php artisan migrate`

### 1.3 Database Seeders
- [ ] Create WordPairSeeder with 20+ word pairs
- [ ] Create test user seeder (optional)
- [ ] Run seeders: `php artisan db:seed`

---

## Phase 2: Authentication & User Management

### 2.1 User Model & Authentication
- [ ] Create User model with relationships
- [ ] Setup Laravel Sanctum: `composer require laravel/sanctum`
- [ ] Publish Sanctum config: `php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"`
- [ ] Create AuthController with register, login, logout methods
- [ ] Create Form Requests for validation (RegisterRequest, LoginRequest)
- [ ] Create API Resources for user responses
- [ ] Setup authentication routes in `routes/api.php`

### 2.2 User Routes
- [ ] POST /register - Register new user
- [ ] POST /login - Login user
- [ ] POST /logout - Logout user
- [ ] GET /profile - Get current user profile
- [ ] PUT /profile - Update user profile

### 2.3 Authentication Middleware
- [ ] Create custom middleware for game authorization
- [ ] Setup rate limiting middleware
- [ ] Test authentication flows

---

## Phase 3: Room Management

### 3.1 Room Model & Relationships
- [ ] Create Room model with relationships to User and Game
- [ ] Create RoomController with CRUD operations
- [ ] Create Form Requests for room validation
- [ ] Create API Resources for room responses

### 3.2 Room Routes
- [ ] GET /rooms - List available rooms
- [ ] POST /rooms - Create new room
- [ ] GET /rooms/{id} - Get room details
- [ ] PUT /rooms/{id} - Update room settings
- [ ] DELETE /rooms/{id} - Delete room (host only)
- [ ] POST /rooms/{id}/join - Join room
- [ ] POST /rooms/{id}/leave - Leave room
- [ ] GET /rooms/{id}/players - Get room players

### 3.3 Room Logic
- [ ] Implement room creation with host assignment
- [ ] Implement player joining with capacity check
- [ ] Implement player leaving with cleanup
- [ ] Implement room deletion (host only)
- [ ] Add validation for room operations

---

## Phase 4: Game Logic & Models

### 4.1 Game Models
- [ ] Create Game model with relationships
- [ ] Create GamePlayer model (pivot with extra attributes)
- [ ] Create Vote model with relationships
- [ ] Create ChatMessage model
- [ ] Create WordPair model

### 4.2 Game Service
- [ ] Create GameService class with methods:
  - [ ] startGame(Room $room): Game
  - [ ] assignRoles(Game $game): void
  - [ ] selectWordPair(): WordPair
  - [ ] processVote(Game $game, User $voter, User $votedFor): void
  - [ ] eliminatePlayer(Game $game, User $player): void
  - [ ] checkWinConditions(Game $game): ?string
  - [ ] transitionPhase(Game $game): void
  - [ ] finishGame(Game $game, string $winner): void

### 4.3 Vote Service
- [ ] Create VoteService class with methods:
  - [ ] recordVote(Game $game, User $voter, User $votedFor): void
  - [ ] getVoteCount(Game $game, User $player): int
  - [ ] getEliminatedPlayer(Game $game): ?User
  - [ ] hasPlayerVoted(Game $game, User $player): bool

### 4.4 Game Controller
- [ ] Create GameController with methods:
  - [ ] store() - Start new game
  - [ ] show() - Get game state
  - [ ] vote() - Submit vote
  - [ ] chat() - Send chat message
  - [ ] results() - Get game results

---

## Phase 5: Real-time Communication

### 5.1 WebSocket Setup
- [ ] Install Laravel WebSockets: `composer require beyondcode/laravel-websockets`
- [ ] Publish config: `php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider"`
- [ ] Configure Pusher credentials in `.env`
- [ ] Setup broadcasting driver in `config/broadcasting.php`

### 5.2 Events & Broadcasting
- [ ] Create GameStarted event (implements ShouldBroadcast)
- [ ] Create PlayerVoted event
- [ ] Create PlayerEliminated event
- [ ] Create GameFinished event
- [ ] Create ChatMessageSent event
- [ ] Create PhaseChanged event
- [ ] Setup private channels for game-specific events
- [ ] Configure event broadcasting in `routes/channels.php`

### 5.3 Listeners
- [ ] Create listeners for game events (optional, for side effects)
- [ ] Register listeners in EventServiceProvider

---

## Phase 6: Frontend - Blade Templates

### 6.1 Layout Templates
- [ ] Create `resources/views/layouts/app.blade.php` - Main layout
- [ ] Create `resources/views/layouts/auth.blade.php` - Auth layout
- [ ] Create `resources/views/layouts/game.blade.php` - Game layout
- [ ] Setup navigation and header components
- [ ] Include Alpine.js and Tailwind CSS

### 6.2 Authentication Pages
- [ ] Create `resources/views/auth/login.blade.php`
- [ ] Create `resources/views/auth/register.blade.php`
- [ ] Create `resources/views/auth/profile.blade.php`
- [ ] Add form validation and error messages

### 6.3 Lobby Pages
- [ ] Create `resources/views/lobby/index.blade.php` - Room list
- [ ] Create `resources/views/lobby/create.blade.php` - Create room form
- [ ] Create `resources/views/lobby/show.blade.php` - Room details
- [ ] Add room filtering and search
- [ ] Add join/leave functionality

### 6.4 Game Pages
- [ ] Create `resources/views/game/index.blade.php` - Main game board
- [ ] Create `resources/views/game/chat.blade.php` - Chat panel
- [ ] Create `resources/views/game/voting.blade.php` - Voting interface
- [ ] Create `resources/views/game/results.blade.php` - Game results
- [ ] Add phase timer component
- [ ] Add player list component

### 6.5 Components
- [ ] Create `resources/views/components/player-list.blade.php`
- [ ] Create `resources/views/components/chat-panel.blade.php`
- [ ] Create `resources/views/components/voting-panel.blade.php`
- [ ] Create `resources/views/components/phase-timer.blade.php`
- [ ] Create `resources/views/components/game-stats.blade.php`

---

## Phase 7: Frontend - JavaScript & Alpine.js

### 7.1 Alpine.js Components
- [ ] Create game state management with Alpine.js
- [ ] Implement real-time chat with Pusher
- [ ] Implement voting interface with vote submission
- [ ] Implement phase timer with countdown
- [ ] Implement player list with status updates
- [ ] Add animations and transitions

### 7.2 JavaScript Utilities
- [ ] Create Pusher subscription manager
- [ ] Create API client for HTTP requests
- [ ] Create notification system
- [ ] Create error handling utilities
- [ ] Add localStorage for client-side state

### 7.3 Styling
- [ ] Setup Tailwind CSS
- [ ] Create custom CSS for game UI
- [ ] Add responsive design
- [ ] Add dark mode support (optional)

---

## Phase 8: API Routes & Endpoints

### 8.1 Authentication Routes
```php
// routes/api.php
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
});
```

### 8.2 Room Routes
```php
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('rooms', RoomController::class);
    Route::post('/rooms/{room}/join', [RoomController::class, 'join']);
    Route::post('/rooms/{room}/leave', [RoomController::class, 'leave']);
    Route::get('/rooms/{room}/players', [RoomController::class, 'players']);
});
```

### 8.3 Game Routes
```php
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('games', GameController::class);
    Route::post('/games/{game}/vote', [GameController::class, 'vote']);
    Route::post('/games/{game}/chat', [GameController::class, 'chat']);
    Route::get('/games/{game}/results', [GameController::class, 'results']);
});
```

### 8.4 Word Routes (Admin)
```php
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::apiResource('words', WordController::class);
});
```

---

## Phase 9: Testing

### 9.1 Unit Tests
- [ ] Test GameService methods
- [ ] Test VoteService methods
- [ ] Test model relationships
- [ ] Test validation rules
- [ ] Aim for 80%+ code coverage

### 9.2 Feature Tests
- [ ] Test authentication endpoints
- [ ] Test room CRUD operations
- [ ] Test game creation and initialization
- [ ] Test voting system
- [ ] Test win condition detection
- [ ] Test error scenarios

### 9.3 Test Setup
- [ ] Create test database
- [ ] Setup test factories for models
- [ ] Create test seeders
- [ ] Configure PHPUnit in `phpunit.xml`
- [ ] Run tests: `php artisan test`

---

## Phase 10: Error Handling & Logging

### 10.1 Exception Handling
- [ ] Create custom exceptions:
  - [ ] GameNotFoundException
  - [ ] RoomFullException
  - [ ] InvalidVoteException
  - [ ] UnauthorizedException
- [ ] Setup exception handler in `app/Exceptions/Handler.php`
- [ ] Return consistent error responses

### 10.2 Logging
- [ ] Configure logging in `config/logging.php`
- [ ] Log all game actions
- [ ] Log authentication events
- [ ] Log errors with context
- [ ] Setup log rotation

### 10.3 Validation
- [ ] Create Form Requests for all endpoints
- [ ] Add custom validation rules
- [ ] Return validation errors in consistent format

---

## Phase 11: Performance Optimization

### 11.1 Database Optimization
- [ ] Add indexes on foreign keys
- [ ] Add indexes on frequently queried columns
- [ ] Use eager loading (with()) to prevent N+1 queries
- [ ] Implement query caching with Redis
- [ ] Use pagination for large result sets

### 11.2 Caching
- [ ] Cache word pairs in Redis
- [ ] Cache room listings
- [ ] Cache user profiles
- [ ] Implement cache invalidation

### 11.3 Frontend Optimization
- [ ] Minify CSS and JavaScript
- [ ] Implement lazy loading for images
- [ ] Use browser caching
- [ ] Optimize Blade template rendering

---

## Phase 12: Deployment & Production

### 12.1 Environment Configuration
- [ ] Setup production `.env` file
- [ ] Configure database for production
- [ ] Setup Redis for production
- [ ] Configure mail service
- [ ] Setup error tracking (Sentry)

### 12.2 Server Setup
- [ ] Configure web server (Apache/Nginx)
- [ ] Setup SSL certificate (Let's Encrypt)
- [ ] Configure file permissions
- [ ] Setup cron job for Laravel scheduler
- [ ] Setup queue worker (if using jobs)

### 12.3 Deployment Steps
- [ ] Clone repository to server
- [ ] Run `composer install --no-dev`
- [ ] Run `php artisan migrate --force`
- [ ] Run `php artisan cache:clear`
- [ ] Setup supervisor for queue worker
- [ ] Configure monitoring and alerts

### 12.4 Post-Deployment
- [ ] Test all endpoints
- [ ] Monitor error logs
- [ ] Monitor performance metrics
- [ ] Setup automated backups
- [ ] Document deployment process

---

## Quality Assurance Checklist

### Functionality
- [ ] User registration and login work correctly
- [ ] Room creation and joining work
- [ ] Game initialization assigns roles correctly
- [ ] Discussion phase allows chat
- [ ] Voting phase counts votes correctly
- [ ] Player elimination works
- [ ] Win conditions are detected correctly
- [ ] Game results display accurately

### Security
- [ ] All inputs are validated
- [ ] CSRF protection is enabled
- [ ] Passwords are hashed
- [ ] API endpoints require authentication
- [ ] Authorization checks are in place
- [ ] Chat messages are sanitized
- [ ] No sensitive data in logs
- [ ] HTTPS is enforced in production

### Performance
- [ ] Page load time < 2 seconds
- [ ] API response time < 500ms
- [ ] WebSocket messages deliver < 100ms
- [ ] Database queries are optimized
- [ ] No N+1 query problems
- [ ] Caching is working

### User Experience
- [ ] UI is responsive on mobile
- [ ] Error messages are clear
- [ ] Game flow is intuitive
- [ ] Real-time updates work smoothly
- [ ] No console errors
- [ ] Accessibility standards met

---

## Documentation Checklist

- [ ] API documentation (routes, parameters, responses)
- [ ] Database schema documentation
- [ ] Setup and installation guide
- [ ] Deployment guide
- [ ] Code comments for complex logic
- [ ] README with project overview
- [ ] Contributing guidelines
- [ ] License file

---

**Version**: 1.0.0
**Last Updated**: 2026-05-07
