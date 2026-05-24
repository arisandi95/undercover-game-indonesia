@extends('layouts.app')

@section('content')
<section x-data="gameComponent()" class="space-y-6">
    <div class="rounded-3xl border border-purple-600/30 bg-gradient-to-br from-purple-900/30 to-cyan-900/20 p-6 backdrop-blur-sm">
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
            <div>
                <p class="text-sm uppercase tracking-[0.35em] text-cyan-300">Round {{ $game->round }} · {{ $game->status }}</p>
                <h1 class="mt-2 text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-400">{{ $game->room->name }}</h1>
            </div>
            <div class="rounded-2xl border border-cyan-400/30 bg-cyan-400/10 px-5 py-4 text-center">
                <p class="text-xs uppercase tracking-widest text-cyan-200">Timer</p>
                <p class="text-3xl font-black text-cyan-100" x-text="remaining > 0 ? remaining + 's' : 'Waiting'"></p>
            </div>
        </div>
        <div class="mt-6 rounded-2xl border border-purple-600/30 bg-purple-900/30 p-5">
            <p class="text-sm text-purple-400">Rahasia Anda</p>
            <p class="mt-1 text-2xl font-bold text-amber-200">{{ $word }}</p>
            <p class="mt-2 text-sm text-purple-300">Jangan katakan kata secara langsung. Berikan petunjuk dan kenali pemain mencurigakan.</p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
        <div class="space-y-6">
            <div class="rounded-3xl border border-purple-600/30 bg-purple-900/20 p-6 backdrop-blur-sm">
                <h2 class="mb-4 text-xl font-bold text-cyan-300">Pemain</h2>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($game->players as $gamePlayer)
                        <div class="player-card">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-cyan-200">{{ $gamePlayer->user->username }}</p>
                                    <p class="text-sm {{ $gamePlayer->is_alive ? 'text-emerald-300' : 'text-red-300' }}">{{ $gamePlayer->is_alive ? 'Hidup' : 'Tereliminasi' }}</p>
                                </div>
                                @if ($game->status === 'voting' && $gamePlayer->is_alive && $gamePlayer->user_id !== auth()->id() && !$hasVoted)
                                    <form method="POST" action="{{ route('games.vote', $game) }}">
                                        @csrf
                                        <input type="hidden" name="voted_for_id" value="{{ $gamePlayer->user_id }}">
                                        <button class="rounded-lg bg-red-400 px-3 py-2 text-sm font-bold text-slate-950">Vote</button>
                                    </form>
                                @endif
                            </div>
                            @if ($game->status === 'finished')
                                <p class="mt-2 text-xs uppercase tracking-widest text-purple-400">{{ str_replace('_', ' ', $gamePlayer->role) }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            @if ($game->status === 'finished')
                <div class="rounded-3xl border border-emerald-500/30 bg-emerald-500/10 p-6 backdrop-blur-sm">
                    <h2 class="text-2xl font-black text-emerald-200">Winner: {{ str_replace('_', ' ', $game->winner) }}</h2>
                    <p class="mt-2 text-purple-300">Kata warga negara: {{ $game->civilian_word }} · Kata undercover: {{ $game->undercover_word }}</p>
                </div>
            @endif
        </div>

        <aside class="space-y-6">
            <div class="rounded-3xl border border-purple-600/30 bg-purple-900/20 p-6 backdrop-blur-sm">
                <h2 class="text-xl font-bold text-cyan-300">Kontrol Host</h2>
                @if ($game->room->host_id === auth()->id() && $game->status !== 'finished')
                    <form method="POST" action="{{ route('games.transition', $game) }}" class="mt-4">
                        @csrf
                        <button class="btn-game btn-primary-game w-full">Lanjutkan Fase</button>
                    </form>
                @else
                    <p class="mt-3 text-sm text-purple-400">Hanya host yang bisa memajukan fase.</p>
                @endif
            </div>

            <div class="rounded-3xl border border-purple-600/30 bg-purple-900/20 p-6 backdrop-blur-sm">
                <h2 class="text-xl font-bold text-cyan-300">Chat</h2>
                <div class="mt-4 max-h-80 space-y-3 overflow-y-auto pr-2" id="chat-messages">
                    <template x-for="message in messages" :key="message.id">
                        <div class="rounded-xl bg-purple-900/50 p-3">
                            <p class="text-sm font-semibold text-cyan-200" x-text="message.user.username"></p>
                            <p class="text-sm text-purple-200" x-text="message.message"></p>
                        </div>
                    </template>
                </div>
                <form method="POST" action="{{ route('games.chat', $game) }}" class="mt-4 space-y-3" @submit.prevent="
                    $el.submit();
                    $el.reset();
                    fetchNewMessages();
                ">
                    @csrf
<textarea name="message" rows="3" x-model="newMessage" {{ $game->status !== 'discussion' ? 'disabled' : '' }} class="form-game-input resize-none" placeholder="Berikan petunjuk..."></textarea>
                     <button class="btn-game btn-primary-game w-full" {{ $game->status !== 'discussion' ? 'disabled' : '' }}>Kirim</button>
                </form>
            </div>
        </aside>
    </div>
</section>

<script>
function gameComponent() {
    return {
        remaining: Math.max(0, Math.floor((new Date('{{ optional($game->current_phase_end_time)->toIso8601String() }}').getTime() - Date.now()) / 1000)),
        gameStatus: '{{ $game->status }}',
        gameRound: {{ $game->round }},
        statusPollingInterval: null,
        messages: @js($game->chatMessages->sortBy('created_at')->toArray()),
        lastMessageId: @js($game->chatMessages->max('id') ?? 0),
        newMessage: '',
        echoChannel: null,

        init() {
            setInterval(() => {
                if (this.remaining > 0) this.remaining--;
            }, 1000);
            this.setupRealtimeListeners();
            if (this.gameStatus === 'discussion') {
                this.$nextTick(() => {
                    this.scrollToBottom();
                });
            }
        },

        setupRealtimeListeners() {
            if (!window.Echo) {
                this.setupPollingFallback();
                return;
            }

            this.echoChannel = window.Echo.private('game.{{ $game->id }}');

            this.echoChannel.subscribed(() => {
                console.log('✅ Subscribed to game.{{ $game->id }}');
            });

            this.echoChannel.error((error) => {
                console.error('❌ WebSocket error:', error);
                this.setupPollingFallback();
            });

            this.echoChannel
                .listen('.game.started', (e) => {
                    window.location.reload();
                })
                .listen('.phase.changed', (e) => {
                    window.location.reload();
                })
                .listen('.chat.message', (e) => {
                    const existingIds = new Set(this.messages.map(m => m.id));
                    if (e.message && !existingIds.has(e.message.id)) {
                        this.messages.push(e.message);
                        this.lastMessageId = Math.max(this.lastMessageId, e.message.id);
                        this.$nextTick(() => {
                            this.scrollToBottom();
                        });
                    }
                });

            this.statusPollingInterval = setInterval(() => {
                this.checkGameStatus();
                this.checkForMissedMessages();
            }, 2000);
        },

        setupPollingFallback() {
            this.statusPollingInterval = setInterval(() => {
                this.checkGameStatus();
                if (this.gameStatus === 'discussion') {
                    this.fetchNewMessages();
                }
            }, 3000);
        },

        async checkGameStatus() {
            try {
                const response = await fetch('/api/games/{{ $game->id }}/status?_=' + Date.now(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                if (response.ok) {
                    const data = await response.json();
                    if (data.status !== this.gameStatus || data.round !== this.gameRound) {
                        window.location.reload();
                    }
                }
            } catch (error) {
                console.error('Failed to check game status:', error);
            }
        },

        async checkForMissedMessages() {
            if (this.gameStatus === 'discussion') {
                await this.fetchNewMessages();
            }
        },

        async fetchNewMessages() {
            try {
                const response = await fetch('/api/games/{{ $game->id }}/chat?after=' + this.lastMessageId, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                if (response.ok) {
                    const newMessages = await response.json();
                    if (newMessages.length > 0) {
                        const existingIds = new Set(this.messages.map(m => m.id));
                        const newUniqueMessages = newMessages.filter(m => !existingIds.has(m.id));
                        if (newUniqueMessages.length > 0) {
                            this.messages.push(...newUniqueMessages);
                            this.lastMessageId = Math.max(this.lastMessageId, ...newUniqueMessages.map(m => m.id));
                            this.scrollToBottom();
                        }
                    }
                }
            } catch (error) {
                console.error('Failed to fetch messages:', error);
            }
        },

        scrollToBottom() {
            setTimeout(() => {
                const chatContainer = document.getElementById('chat-messages');
                if (chatContainer) {
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }
            }, 100);
        }
    }
}
</script>
@endsection