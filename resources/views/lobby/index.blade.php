@extends('layouts.app')

@section('content')
<div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-center">
    <div>
        <h1 class="text-4xl font-black tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-400">Lobby Game</h1>
        <p class="mt-2 text-purple-300">Buat ruangan atau bergabung dengan meja yang menunggu.</p>
    </div>
    <a href="{{ route('rooms.create') }}" class="btn-game btn-primary-game">Buat Ruangan</a>
</div>

<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
    @forelse ($rooms as $room)
        <article class="player-card transition-transform hover:scale-[1.02]">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-cyan-300">{{ $room->name }}</h2>
                    <p class="text-sm text-purple-400">Host: {{ $room->host->username }}</p>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $room->status === 'waiting' ? 'status-alive' : 'status-undercover' }}">{{ str_replace('_', ' ', $room->status) }}</span>
            </div>
            <p class="mt-4 text-sm text-purple-300">Players: {{ $room->latestGame?->players?->count() ?? 1 }} / {{ $room->max_players }}</p>
            <a href="{{ route('rooms.show', $room) }}" class="mt-5 inline-flex rounded-xl border border-purple-600/50 px-4 py-2 text-sm font-semibold text-cyan-300 hover:bg-purple-800/30">Buka Ruangan</a>
        </article>
    @empty
        <div class="player-card text-center">
            <p class="text-purple-400">Belum ada ruangan. Buat yang pertama.</p>
        </div>
    @endforelse
</div>

<div class="mt-8">{{ $rooms->links() }}</div>
@endsection