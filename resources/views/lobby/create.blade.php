@extends('layouts.app')

@section('content')
<section class="mx-auto max-w-lg rounded-3xl border border-purple-600/30 bg-purple-900/20 p-8 backdrop-blur-sm">
    <h1 class="mb-6 text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-400">Buat Ruangan</h1>
    <form method="POST" action="{{ route('rooms.store') }}" class="space-y-4">
        @csrf
        <div class="form-game-group">
            <label class="form-game-label">Nama Ruangan</label>
            <input name="name" value="{{ old('name') }}" required class="form-game-input" placeholder="Masukkan nama ruangan...">
        </div>
        <div class="form-game-group">
            <label class="form-game-label">Maks Pemain</label>
            <input name="max_players" type="number" min="3" max="12" value="{{ old('max_players', 8) }}" required class="form-game-input" placeholder="4-12 pemain">
        </div>
        <button class="btn-game btn-primary-game w-full">Buat Ruangan</button>
    </form>
</section>
@endsection