@extends('layouts.app')

@section('content')
<div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-center">
    <div>
        <h1 class="text-4xl font-black tracking-tight">Game Lobby</h1>
        <p class="mt-2 text-slate-400">Create a room or join a waiting table.</p>
    </div>
    <a href="{{ route('rooms.create') }}" class="rounded-xl bg-cyan-400 px-5 py-3 font-bold text-slate-950 hover:bg-cyan-300">Create Room</a>
</div>

<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
    @forelse ($rooms as $room)
        <article class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold">{{ $room->name }}</h2>
                    <p class="text-sm text-slate-400">Host: {{ $room->host->username }}</p>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $room->status === 'waiting' ? 'bg-emerald-400/10 text-emerald-300' : 'bg-amber-400/10 text-amber-300' }}">{{ str_replace('_', ' ', $room->status) }}</span>
            </div>
            <p class="mt-4 text-sm text-slate-300">Players: {{ $room->latestGame?->players?->count() ?? 1 }} / {{ $room->max_players }}</p>
            <a href="{{ route('rooms.show', $room) }}" class="mt-5 inline-flex rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold hover:bg-slate-800">Open Room</a>
        </article>
    @empty
        <p class="rounded-2xl border border-slate-800 bg-slate-900 p-6 text-slate-400">No rooms yet. Create the first one.</p>
    @endforelse
</div>

<div class="mt-8">{{ $rooms->links() }}</div>
@endsection
