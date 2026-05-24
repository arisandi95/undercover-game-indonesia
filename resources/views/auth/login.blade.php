@extends('layouts.app')

@section('content')
<section class="mx-auto max-w-md rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-2xl shadow-cyan-950/30">
    <h1 class="mb-2 text-3xl font-bold">Login</h1>
    <p class="mb-6 text-slate-400">Enter the lobby and find the undercover player.</p>
    <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
        @csrf
        <label class="block">
            <span class="text-sm text-slate-300">Email</span>
            <input name="email" type="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white outline-none focus:border-cyan-400">
        </label>
        <label class="block">
            <span class="text-sm text-slate-300">Password</span>
            <input name="password" type="password" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white outline-none focus:border-cyan-400">
        </label>
        <button class="w-full rounded-xl bg-cyan-400 px-4 py-3 font-bold text-slate-950 hover:bg-cyan-300">Login</button>
    </form>
    <p class="mt-4 text-sm text-slate-400">No account? <a href="{{ route('register') }}" class="text-cyan-300">Register</a></p>
</section>
@endsection
