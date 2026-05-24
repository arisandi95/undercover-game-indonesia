@extends('layouts.app')

@section('content')
<section class="space-y-12">
    <div class="rounded-3xl border border-purple-600/30 bg-gradient-to-br from-purple-900/30 to-cyan-900/20 p-8 text-center backdrop-blur-sm">
        <h1 class="text-5xl font-black tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 via-purple-400 to-pink-400 md:text-6xl">
            🎮 UNDERCOVER By Sands
        </h1>
        <p class="mt-4 text-xl text-purple-300"></p>
        <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
            <a href="{{ route('login') }}" class="btn-game btn-primary-game">
                Main Sekarang
            </a>
            <a href="{{ route('register') }}" class="btn-game btn-secondary-game">
                Buat Akun
            </a>
        </div>
    </div>

    <div class="rounded-3xl border border-purple-600/30 bg-purple-900/20 p-8 backdrop-blur-sm">
        <h2 class="section-title mb-8">🎯 Cara Bermain</h2>
        <div class="grid gap-6 md:grid-cols-3">
            <div class="rounded-2xl border border-purple-600/40 bg-gradient-to-br from-purple-800/40 to-cyan-900/20 p-6 text-center transition-all hover:scale-105">
                <div class="mb-4 text-5xl">1️⃣</div>
                <h3 class="mb-2 text-xl font-bold text-cyan-300">Dapatkan Kata</h3>
                <p class="text-purple-300">Warga negara punya satu kata, Undercover punya kata berbeda</p>
            </div>
            <div class="rounded-2xl border border-purple-600/40 bg-gradient-to-br from-purple-800/40 to-cyan-900/20 p-6 text-center transition-all hover:scale-105">
                <div class="mb-4 text-5xl">💬</div>
                <h3 class="mb-2 text-xl font-bold text-cyan-300">Berikan Petunjuk</h3>
                <p class="text-purple-300">Jelaskan kata Anda secara tidak langsung tanpa mengatakannya</p>
            </div>
            <div class="rounded-2xl border border-purple-600/40 bg-gradient-to-br from-purple-800/40 to-cyan-900/20 p-6 text-center transition-all hover:scale-105">
                <div class="mb-4 text-5xl">🗳️</div>
                <h3 class="mb-2 text-xl font-bold text-cyan-300">Vote Mereka</h3>
                <p class="text-purple-300">Identifikasi dan buang Undercover sebelum mereka menemukanmu</p>
            </div>
        </div>
    </div>

    <div class="rounded-3xl border border-purple-600/30 bg-purple-900/20 p-8 backdrop-blur-sm">
        <h2 class="section-title mb-8">🏆 Peran Game</h2>
        <div class="grid gap-4 md:grid-cols-3">
            <div class="player-card">
                <div class="player-avatar">👤</div>
                <div class="player-name">Warga Negara</div>
                <div class="player-status status-alive">Menang dengan menemukan Undercover</div>
            </div>
            <div class="player-card">
                <div class="player-avatar">🕵️</div>
                <div class="player-name">Undercover</div>
                <div class="player-status status-undercover">Infiltrasi dan tipu daya</div>
            </div>
            <div class="player-card">
                <div class="player-avatar">👑</div>
                <div class="player-name">Mr. White</div>
                <div class="player-status status-undercover">Kata tak diketahui - pure chaos</div>
            </div>
        </div>
    </div>
</section>
@endsection