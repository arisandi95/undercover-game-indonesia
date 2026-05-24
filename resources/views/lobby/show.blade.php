@extends('layouts.app')

@section('content')
@php
    $game = $room->latestGame;
    $initialPlayers = collect($game?->players ?? [])->map(function ($player) use ($room) {
        return [
            'user_id' => $player->user_id,
            'user' => ['username' => $player->user->username],
            'is_host' => $player->user_id === $room->host_id,
        ];
    })->values();

    if (!$initialPlayers->contains('user_id', $room->host_id)) {
        $initialPlayers->prepend([
        'user_id' => $room->host_id,
        'user' => ['username' => $room->host->username],
        'is_host' => true,
        ]);
    }
@endphp

<script>
window.__ROOM_PLAYERS = @json($initialPlayers);
window.__ROOM_HOST_ID = {{ $room->host_id }};
window.__ROOM_ID = {{ $room->id }};
</script>
<section class="grid gap-6 lg:grid-cols-[1fr_320px]">
    <div class="rounded-3xl border border-purple-600/30 bg-purple-900/20 p-6 backdrop-blur-sm">
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
            <div>
                <h1 class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-400">{{ $room->name }}</h1>
                <p class="mt-2 text-purple-300">Hosted by {{ $room->host->username }} · {{ str_replace('_', ' ', $room->status) }}</p>
            </div>
            @if ($game && $room->status !== 'waiting')
                <a href="{{ route('games.show', $game) }}" class="btn-game btn-primary-game">Enter Game</a>
            @endif
        </div>

        <div id="room-players" class="mt-8 grid gap-3 sm:grid-cols-2">
            @foreach ($initialPlayers as $player)
                <div class="player-card" data-player-id="{{ $player['user_id'] }}">
                    <p class="font-semibold text-cyan-300">{{ $player['user']['username'] }}</p>
                    <p class="text-sm text-purple-400">{{ $player['is_host'] ? 'Host' : 'Pemain' }}</p>
                </div>
            @endforeach
        </div>

        <div id="room-players-empty" class="mt-8 hidden text-center text-purple-400">
            <p>Menunggu pemain bergabung...</p>
        </div>
    </div>

    <aside class="rounded-3xl border border-purple-600/30 bg-purple-900/20 p-6 backdrop-blur-sm">
        <h2 class="text-xl font-bold text-cyan-300">Tindakan</h2>
        <div class="mt-5 space-y-3">
            @if ($room->status === 'waiting')
                @if (!$game?->players?->contains('user_id', auth()->id()))
                    <form method="POST" action="{{ route('rooms.join', $room) }}">@csrf<button class="btn-game btn-primary-game w-full">Gabung Ruangan</button></form>
                @endif
                @if ($room->host_id === auth()->id())
                    <form method="POST" action="{{ route('games.store', $room) }}">@csrf<button class="btn-game btn-secondary-game w-full">Mulai Game</button></form>
                @endif
            @endif
            <form method="POST" action="{{ route('rooms.leave', $room) }}">@csrf<button class="rounded-xl border border-purple-600/50 px-4 py-3 font-bold text-purple-300 w-full">Keluar Ruangan</button></form>
            @if ($room->host_id === auth()->id())
                <form method="POST" action="{{ route('rooms.destroy', $room) }}">@csrf @method('DELETE')<button class="rounded-xl border border-red-500/50 px-4 py-3 font-bold text-red-300 w-full">Hapus Ruangan</button></form>
            @endif
        </div>
    </aside>
</section>

<script>
(() => {
    const playersContainer = document.getElementById('room-players');
    const emptyState = document.getElementById('room-players-empty');
    let currentPlayers = (window.__ROOM_PLAYERS || []).map(p => ({
        user_id: Number(p.user_id),
        user: { username: p.user?.username ?? '' },
        is_host: Boolean(p.is_host),
    }));
    let pollingInterval = null;

    function escapeHtml(value) {
        return String(value).replace(/[&<>'"]/g, (char) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            "'": '&#039;',
            '"': '&quot;'
        }[char]));
    }

    function renderPlayers() {
        playersContainer.innerHTML = currentPlayers.map((player) => `
            <div class="player-card" data-player-id="${player.user_id}">
                <p class="font-semibold text-cyan-300">${escapeHtml(player.user.username)}</p>
                <p class="text-sm text-purple-400">${player.is_host ? 'Host' : 'Pemain'}</p>
            </div>
        `).join('');

        emptyState.classList.toggle('hidden', currentPlayers.length > 0);
    }

    function findPlayer(userId) {
        return currentPlayers.find(p => Number(p.user_id) === Number(userId));
    }

    function addPlayer(player) {
        const userId = Number(player?.id ?? player?.user_id ?? player?.user?.id);
        if (!Number.isFinite(userId)) return;
        if (findPlayer(userId)) return;

        currentPlayers.push({
            user_id: userId,
            user: { username: player?.username ?? player?.user?.username ?? '' },
            is_host: Boolean(player?.is_host ?? player?.user?.is_host ?? userId === window.__ROOM_HOST_ID),
        });
        renderPlayers();
    }

    function removePlayer(player) {
        const userId = Number(player?.id ?? player?.user_id ?? player?.user?.id);
        if (!Number.isFinite(userId)) return;
        currentPlayers = currentPlayers.filter(p => Number(p.user_id) !== userId);
        renderPlayers();
    }

    function checkRoomStatus() {
        const roomId = window.__ROOM_ID;
        if (!roomId) return;

        fetch(`/rooms/${roomId}/status?_=${Date.now()}`, {
            credentials: 'include',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.ok ? r.json() : null)
        .then(data => {
            if (data && data.status === 'in_progress' && data.game_id) {
                window.location.href = `/games/${data.game_id}`;
            }
        })
        .catch(() => {});
    }

    function startPolling() {
        const roomId = window.__ROOM_ID;
        if (!roomId) return;

        const poll = () => {
            fetch(`/rooms/${roomId}/players?_=${Date.now()}`, {
                credentials: 'include',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.ok ? r.json() : null)
            .then(players => {
                if (!players || !Array.isArray(players)) return;

                const serverIds = new Set(players.map(p => Number(p.user_id ?? p.id)));
                const currentIds = new Set(currentPlayers.map(p => Number(p.user_id)));
                const merged = players.map(p => ({
                    user_id: Number(p.user_id ?? p.id),
                    user: { username: p.user?.username ?? p.username ?? '' },
                    is_host: Boolean(p.is_host ?? Number(p.user_id ?? p.id) === window.__ROOM_HOST_ID),
                }));

                const hasChanges =
                    merged.length !== currentPlayers.length ||
                    merged.some(p => !currentIds.has(p.user_id)) ||
                    currentPlayers.some(p => !serverIds.has(p.user_id));

                if (hasChanges) {
                    currentPlayers = merged;
                    renderPlayers();
                }
            })
            .catch(() => {});
            
            checkRoomStatus();
        };

        poll();
        pollingInterval = setInterval(poll, 5000);
    }

    function initRealtime() {
        const echo = window.Echo;
        if (!echo || !echo.connector) {
            console.log('Echo not available, using polling only');
            startPolling();
            return;
        }

        const channelName = 'room.' + window.__ROOM_ID;
        const channel = echo.private(channelName);
        
        channel.listen('PlayerJoinedRoom', (e) => {
            console.log('PlayerJoinedRoom event:', e);
            addPlayer(e.player ?? e);
        })
        .listen('PlayerLeftRoom', (e) => {
            console.log('PlayerLeftRoom event:', e);
            removePlayer(e.player ?? e);
        })
        .listen('.game.started', (e) => {
            console.log('Game started event received:', e);
            if (e.game_url) {
                window.location.href = e.game_url;
            }
        });

        const conn = echo.connector;
        conn.pusher?.connection?.bind('connected', () => {
            console.log('WebSocket connected');
        });
        
        conn.pusher?.connection?.bind('disconnected', () => {
            console.log('WebSocket disconnected, starting polling fallback');
            if (!pollingInterval) {
                startPolling();
            }
        });
        
        conn.pusher?.connection?.bind('error', (err) => {
            console.error('WebSocket error:', err);
        });
        
        startPolling();
    }

    renderPlayers();
    initRealtime();
})();
</script>
@endsection