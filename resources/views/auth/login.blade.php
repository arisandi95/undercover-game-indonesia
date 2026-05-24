@extends('layouts.app')

@section('content')
<section class="mx-auto max-w-md rounded-3xl border border-purple-600/30 bg-gradient-to-br from-purple-900/40 to-purple-950/40 p-8 shadow-2xl shadow-purple-500/30 backdrop-blur-sm relative overflow-hidden">
    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-cyan-400 via-pink-500 to-cyan-400 animate-pulse"></div>
    <div class="absolute -top-20 -right-20 w-40 h-40 bg-purple-600/20 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-20 -left-20 w-40 h-40 bg-cyan-400/10 rounded-full blur-3xl"></div>
    
    <div class="relative z-10">
        <h1 class="mb-2 text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-400 tracking-wider">LOGIN</h1>
        <p class="mb-8 text-purple-300 font-medium">Masuk ke lobby dan temukan pemain undercover.</p>
        
        <form method="POST" action="{{ route('login.store') }}" class="space-y-6 p-5 rounded-2xl border border-purple-600/20 bg-purple-900/30 backdrop-blur-sm">
            @csrf
            <div class="form-game-group relative">
                <label class="form-game-label flex items-center gap-2">
                    <span class="text-cyan-400">📧</span> Email
                </label>
                <input name="email" type="email" value="{{ old('email') }}" required 
                    class="form-game-input" placeholder="Masukkan email Anda...">
            </div>
            <div class="form-game-group relative">
                <label class="form-game-label flex items-center gap-2">
                    <span class="text-cyan-400">🔒</span> Kata Sandi
                </label>
                <input name="password" type="password" required 
                    class="form-game-input" placeholder="Masukkan kata sandi Anda...">
            </div>
            <button class="btn-game btn-primary-game w-full relative overflow-hidden group">
                <span class="relative z-10">Login to Game</span>
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700"></div>
            </button>
        </form>
        
        <div class="mt-8 text-center">
            <p class="text-purple-400">Belum punya akun? <a href="{{ route('register') }}" class="text-cyan-400 font-bold hover:text-pink-400 transition-colors">Daftar Sekarang</a></p>
        </div>
    </div>
</section>
@endsection