# Undercover Game - Laravel Implementation

A real-time multiplayer web game built with Laravel, featuring room management, game phases, voting system, and live chat.

## Features / Fitur

### English
- **User Authentication**: Register and login with Laravel Sanctum
- **Room Management**: Create and join game rooms with unique codes
- **Role Assignment**: Automatic assignment of Civilians, Undercover, and Mr. White roles
- **Game Phases**: SETUP → DISCUSSION → VOTING → RESULTS cycle
- **Live Chat**: Real-time messaging during discussion phase
- **Voting System**: Eliminate players through democratic voting
- **Win Conditions**: Multiple winning scenarios based on roles
- **Real-time Updates**: WebSocket integration via Laravel Reverb

### Indonesia
- **Autentikasi Pengguna**: Daftar dan masuk dengan Laravel Sanctum
- **Manajemen Room**: Buat dan ikuti room game dengan kode unik
- **Penugasan Role**: Penugasan otomatis role Civilian, Undercover, dan Mr. White
- **Fase Game**: Siklus SETUP → DISKUSI → VOTING → HASIL
- **Obrolan Langsung**: Pesan real-time selama fase diskusi
- **Sistem Voting**: Mengeliminasi pemain melalui voting demokratis
- **Kondisi Menang**: Berbagai skenario kemenangan berdasarkan role
- **Update Real-time**: Integrasi WebSocket via Laravel Reverb

## Requirements / Persyaratan

- PHP 8.2+
- Composer
- Node.js & NPM
- SQLite/MySQL/PostgreSQL
- Laravel 11+

## Installation / Instalasi

```bash
# Clone repository
git clone <repository-url>
cd undercover-yuvitegame

# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Seed word pairs (optional)
php artisan db:seed
```

## How to Play / Cara Bermain

### English
1. **Register/Login**: Create an account or login to your existing account
2. **Create Room**: Click "Create Room" to start a new game lobby
3. **Invite Players**: Share the room code with friends (3-10 players recommended)
4. **Start Game**: Host clicks "Start Game" when enough players have joined
5. **Game Phases**:
   - **SETUP (30s)**: Roles are assigned secretly; each player sees their word
   - **DISCUSSION (120s)**: Players discuss to identify the undercover; use chat to share clues
   - **VOTING (60s)**: Vote for who you think is undercover
   - **RESULTS (30s)**: Eliminated player is revealed; check win conditions
6. **Win**: Be on the winning team based on your role!

### Indonesia
1. **Daftar/Masuk**: Buat akun atau masuk dengan akun yang sudah ada
2. **Buat Room**: Klik "Buat Room" untuk memulai lobby game baru
3. **Undang Pemain**: Bagikan kode room ke teman (disarankan 3-10 pemain)
4. **Mulai Game**: Host klik "Mulai Game" ketika cukup pemain telah bergabung
5. **Fase Game**:
   - **SETUP (30d)**: Role ditugaskan secara rahasia; setiap pemain melihat katanya
   - **DISKUSI (120d)**: Pemain berdiskusi untuk mengidentifikasi undercover; gunakan chat untuk beri petunjuk
   - **VOTING (60d)**: Vote untuk pemain yang menurutmu undercover
   - **HASIL (30d)**: Pemain yang tereliminasi diungkapkan; cek kondisi menang
6. **Menang**: Jadilah tim pemenang berdasarkan role kamu!

## Roles / Role

| Role | Description |
|------|-------------|
| Civilian | Must find and eliminate Undercover and Mr. White |
| Undercover | Knows a similar word to Civilians; must blend in |
| Mr. White | Doesn't know the word; guesses one word from eliminated player to win |

## Win Conditions / Kondisi Menang

| Team | Win Condition |
|------|--------------|
| Civilians | Eliminate both Undercover and Mr. White |
| Undercover | All Civilians eliminated OR survive with Mr. White |
| Mr. White | Survive with Undercover OR eliminate all Civilians |

## Development / Pengembangan

### English
Start the development servers:

```bash
# Terminal 1: Laravel development server
php artisan serve

# Terminal 2: Reverb WebSocket server (required for real-time features)
php artisan reverb:start

# Terminal 3: Queue worker (for broadcasting)
php artisan queue:work

# Terminal 4: Vite development server (asset compilation)
npm run dev
```

### Indonesia
Jalankan server pengembangan:

```bash
# Terminal 1: Server development Laravel
php artisan serve

# Terminal 2: Server WebSocket Reverb (diperlukan untuk fitur real-time)
php artisan reverb:start

# Terminal 3: Queue worker (untuk broadcasting)
php artisan queue:work

# Terminal 4: Server development Vite (kompilasi asset)
npm run dev
```

## Deployment / Deployment

### English
1. Set `APP_ENV=production` in `.env`
2. Run `composer install --optimize-autoloader --no-dev`
3. Run `npm run build`
4. Set up a process manager (Supervisor/Systemd) for:
   - `php artisan reverb:start --host=0.0.0.0 --port=8080`
   - `php artisan queue:work --daemon`
5. Configure SSL and reverse proxy (Nginx/Apache)
6. Set up database backups

### Indonesia
1. Set `APP_ENV=production` di `.env`
2. Jalankan `composer install --optimize-autoloader --no-dev`
3. Jalankan `npm run build`
4. Setup process manager (Supervisor/Systemd) untuk:
   - `php artisan reverb:start --host=0.0.0.0 --port=8080`
   - `php artisan queue:work --daemon`
5. Konfigurasi SSL dan reverse proxy (Nginx/Apache)
6. Setup backup database

## Project Structure / Struktur Proyek

```
undercover-yuvitegame/
├── app/
│   ├── Http/Controllers/     # Auth, Room, Game, Word controllers
│   ├── Models/               # User, Room, Game, GamePlayer, Vote, ChatMessage, WordPair
│   ├── Services/             # GameService, RoomService, VoteService
│   ├── Events/               # GameStarted, PlayerVoted, PlayerEliminated, GameFinished
│   └── Listeners/            # Event handlers
├── database/
│   ├── migrations/           # Database schema
│   └── seeders/              # WordPair seeder
├── resources/
│   ├── views/                # Blade templates
│   └── js/                   # Frontend JavaScript
└── routes/
    ├── web.php               # Web routes
    ├── api.php               # API routes
    └── channels.php          # Broadcasting channels
```

## API Endpoints / Endpoint API

### Authentication
- `POST /api/register` - Register new user
- `POST /api/login` - Login user
- `POST /api/logout` - Logout user

### Rooms
- `GET /api/rooms` - List available rooms
- `POST /api/rooms` - Create new room
- `POST /api/rooms/{code}/join` - Join room
- `POST /api/rooms/{code}/start` - Start game (host only)

### Game
- `GET /api/game/{id}` - Get game state
- `POST /api/game/{id}/vote` - Submit vote
- `POST /api/game/{id}/chat` - Send chat message

## Default Credentials / Kredensial Default

After seeding, you can use these credentials:
- Email: `admin@example.com`
- Password: `password`

Or register a new account at `/register`.

## Troubleshooting / Pemecahan Masalah

**WebSocket not connecting?**
- Ensure `reverb:start` is running
- Check `BROADCAST_DRIVER=reverb` in `.env`
- Verify `REVERB_APP_KEY` and `REVERB_APP_SECRET` match

**Game not starting?**
- Ensure minimum 3 players in room
- Only host can start the game
- Check browser console for errors

---

**Version**: 1.0.0  
**Last Updated**: 2026-05-26