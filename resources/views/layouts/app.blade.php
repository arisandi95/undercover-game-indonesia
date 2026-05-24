<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Undercover' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --neon-cyan: #00f0ff;
            --neon-pink: #ff006e;
            --purple-600: #6b46c1;
            --purple-900: #1a0b2e;
            --purple-950: #0f0520;
        }
        
        body {
            background: radial-gradient(ellipse at top, var(--purple-900) 0%, var(--purple-950) 50%, #000 100%);
            color: #fff;
            font-family: 'Inter', 'Rajdhani', sans-serif;
            min-height: 100vh;
        }
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(139, 92, 246, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(0, 240, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(255, 0, 110, 0.1) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }
        .container-gaming {
            position: relative;
            z-index: 1;
        }
        
        /* Game Form Styles */
        .form-game-group {
            margin-bottom: 1.5rem;
        }
        .form-game-label {
            display: block;
            font-weight: 700;
            color: var(--neon-cyan);
            margin-bottom: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-size: 0.875rem;
        }
        .form-game-input {
            width: 100%;
            padding: 1rem 1.5rem;
            background: rgba(45, 27, 78, 0.5);
            border: 2px solid var(--purple-600);
            border-radius: 0.5rem;
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        .form-game-input:focus {
            outline: none;
            border-color: var(--neon-cyan);
            background: rgba(45, 27, 78, 0.8);
            box-shadow: 0 0 20px rgba(0, 240, 255, 0.3);
        }
        
        /* Game Buttons */
        .btn-game {
            padding: 1rem 2.5rem;
            font-size: 1.125rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            border: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            clip-path: polygon(10% 0%, 100% 0%, 90% 100%, 0% 100%);
        }
        .btn-primary-game {
            background: linear-gradient(135deg, var(--purple-600) 0%, var(--neon-pink) 100%);
            color: white;
            box-shadow: 0 0 20px rgba(255, 0, 110, 0.5);
        }
        .btn-primary-game:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 0 30px rgba(255, 0, 110, 0.8), 0 10px 40px rgba(139, 92, 246, 0.4);
        }
        
        /* Secondary Game Button */
        .btn-secondary-game {
            background: transparent;
            border: 2px solid var(--neon-cyan);
            color: var(--neon-cyan);
            clip-path: polygon(10% 0%, 100% 0%, 90% 100%, 0% 100%);
            box-shadow: 0 0 15px rgba(0, 240, 255, 0.3);
        }
        .btn-secondary-game:hover {
            background: rgba(0, 240, 255, 0.1);
            box-shadow: 0 0 25px rgba(0, 240, 255, 0.6);
            transform: translateY(-3px);
        }
        
        /* Player Card */
        .player-card {
            background: linear-gradient(135deg, rgba(45, 27, 78, 0.8) 0%, rgba(15, 5, 32, 0.9) 100%);
            border: 2px solid var(--purple-600);
            border-radius: 1rem;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .player-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--neon-cyan), var(--neon-pink));
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        .player-card:hover {
            transform: translateY(-10px) scale(1.02);
            border-color: var(--neon-cyan);
            box-shadow: 0 20px 50px rgba(0, 240, 255, 0.3), 0 0 30px rgba(139, 92, 246, 0.4);
        }
        .player-card:hover::before {
            transform: scaleX(1);
        }
        .player-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--purple-600), var(--neon-pink));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 1rem;
            border: 2px solid var(--neon-cyan);
            box-shadow: 0 0 15px rgba(0, 240, 255, 0.5);
        }
        .player-name {
            font-size: 1.125rem;
            font-weight: 700;
            text-align: center;
            color: var(--neon-cyan);
            margin-bottom: 0.5rem;
        }
        .player-status {
            text-align: center;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 0.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
        }
        .status-alive {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
            border: 1px solid #10b981;
        }
        .status-eliminated, .status-undercover {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border: 1px solid #ef4444;
        }
        
        /* Section Title */
        .section-title {
            font-size: 2.5rem;
            font-weight: 900;
            color: var(--neon-cyan);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 2rem;
            text-shadow: 0 0 20px rgba(0, 240, 255, 0.5);
        }
    </style>
</head>
<body class="min-h-screen text-white">
    <header class="border-b border-purple-600/30 bg-purple-900/80 backdrop-blur">
        <nav class="container-gaming mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
            <a href="{{ route('rooms.index') }}" class="text-xl font-bold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-400">Undercover</a>
            <div class="flex items-center gap-4 text-sm">
                @auth
                    <span class="text-purple-300">{{ auth()->user()->username }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="rounded-lg border border-purple-600 px-3 py-2 text-purple-300 hover:bg-purple-800/50 hover:text-cyan-300 transition-colors">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-purple-300 hover:text-cyan-300 transition-colors">Login</a>
                    <a href="{{ route('register') }}" class="rounded-lg bg-gradient-to-r from-purple-600 to-pink-500 px-3 py-2 font-semibold text-white hover:from-pink-500 hover:to-purple-600 transition-all">Register</a>
                @endauth
            </div>
        </nav>
    </header>

    <main class="container-gaming mx-auto max-w-6xl px-4 py-8">
        @if (session('status'))
            <div class="mb-6 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-emerald-200">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-red-200">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        @yield('content')
    </main>
</body>
</html>