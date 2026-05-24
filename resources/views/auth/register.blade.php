@extends('layouts.app')

@section('content')
<section class="mx-auto max-w-md rounded-3xl border border-purple-600/30 bg-gradient-to-br from-purple-900/40 to-purple-950/40 p-8 backdrop-blur-sm relative overflow-hidden">
    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-cyan-400 via-pink-500 to-cyan-400 animate-pulse"></div>
    <div class="absolute -top-20 -right-20 w-40 h-40 bg-purple-600/20 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-20 -left-20 w-40 h-40 bg-cyan-400/10 rounded-full blur-3xl"></div>
    
    <div class="relative z-10">
        <div class="mb-6 text-center">
            <h1 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-400 tracking-wider">Buat Akun</h1>
            <p class="mt-2 text-purple-300">Pilih nama yang akan diingat pemain lain.</p>
        </div>
        
        <form method="POST" action="{{ route('register.store') }}" class="space-y-5 p-5 rounded-2xl border border-purple-600/20 bg-purple-900/30 backdrop-blur-sm">
            @csrf
            <div class="form-game-group relative">
                <label class="form-game-label flex items-center gap-2">
                    <span class="text-cyan-400">👤</span> Nama Tampilan
                </label>
                <input name="name" value="{{ old('name') }}" class="form-game-input" placeholder="Nama tampilan Anda">
            </div>
            <div class="form-game-group relative">
                <label class="form-game-label flex items-center gap-2">
                    <span class="text-cyan-400">🔤</span> Nama Pengguna
                </label>
                <input name="username" value="{{ old('username') }}" required class="form-game-input" placeholder="Nama pengguna unik">
            </div>
            <div class="form-game-group relative">
                <label class="form-game-label flex items-center gap-2">
                    <span class="text-cyan-400">📧</span> Email
                </label>
                <input name="email" type="email" value="{{ old('email') }}" required class="form-game-input" placeholder="anda@contoh.com">
            </div>
            <div class="form-game-group relative">
                <label class="form-game-label flex items-center gap-2">
                    <span class="text-cyan-400">🔒</span> Kata Sandi
                </label>
                <input name="password" type="password" required class="form-game-input" placeholder="Kata sandi kuat">
            </div>
            <div class="form-game-group relative">
                <label class="form-game-label flex items-center gap-2">
                    <span class="text-cyan-400">🔐</span> Konfirmasi Kata Sandi
                </label>
                <input name="password_confirmation" type="password" required class="form-game-input" placeholder="Ulangi kata sandi">
            </div>
            <button class="btn-game btn-primary-game w-full relative overflow-hidden group">
                <span class="relative z-10">Register</span>
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700"></div>
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <p class="text-purple-300">Sudah punya akun? <a href="{{ route('login') }}" class="text-cyan-400 font-bold hover:text-pink-400 transition-colors">Masuk di sini</a></p>
        </div>
    </div>
</section>
@endsection