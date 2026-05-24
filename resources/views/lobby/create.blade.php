@extends('layouts.app')

@section('content')
<section class="mx-auto max-w-lg rounded-2xl border border-slate-800 bg-slate-900 p-6">
    <h1 class="mb-6 text-3xl font-bold">Create Room</h1>
    <form method="POST" action="{{ route('rooms.store') }}" class="space-y-4">
        @csrf
        <label class="block">
            <span class="text-sm text-slate-300">Room Name</span>
            <input name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white outline-none focus:border-cyan-400">
        </label>
        <label class="block">
            <span class="text-sm text-slate-300">Max Players</span>
            <input name="max_players" type="number" min="3" max="12" value="{{ old('max_players', 8) }}" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white outline-none focus:border-cyan-400">
        </label>
        <button class="rounded-xl bg-cyan-400 px-5 py-3 font-bold text-slate-950 hover:bg-cyan-300">Create</button>
    </form>
</section>
@endsection
