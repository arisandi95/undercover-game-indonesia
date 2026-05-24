@extends('layouts.app')

@section('content')
<section x-data="gameComponent()" class="space-y-6">
    <div class="rounded-3xl border border-slate-800 bg-gradient-to-br from-slate-900 to-slate-950 p-6">
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
            <div>
                <p class="text-sm uppercase tracking-[0.35em] text-cyan-300">Round {{ $game->round }} · {{ $game->status }}</p>
                <h1 class="mt-2 text-4xl font-black">{{ $game->room->name }}</h1>
            </div>
            <div class="rounded-2xl border border-cyan-400/20 bg-cyan-400/10 px-5 py-4 text-center">
                <p class="text-xs uppercase tracking-widest text-cyan-200">Timer</p>
                <p class="text-3xl font-black text-cyan-100" x-text="remaining > 0 ? remaining + 's' : 'Waiting'"></p>
            </div>
        </div>
        <div class="mt-6 rounded-2xl border border-slate-700 bg-slate-950 p-5">
            <p class="text-sm text-slate-400">Your secret</p>
            <p class="mt-1 text-2xl font-bold text-amber-200">{{ $word }}</p>
            <p class="mt-2 text-sm text-slate-500">Never say the word directly. Give clues and detect suspicious players.</p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-800 bg-slate-900 p-6">
                <h2 class="mb-4 text-xl font-bold">Players</h2>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($game->players as $gamePlayer)
                        <div class="rounded-xl border {{ $gamePlayer->is_alive ? 'border-slate-700 bg-slate-950' : 'border-red-900 bg-red-950/30' }} p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold">{{ $gamePlayer->user->username }}</p>
                                    <p class="text-sm {{ $gamePlayer->is_alive ? 'text-emerald-300' : 'text-red-300' }}">{{ $gamePlayer->is_alive ? 'Alive' : 'Eliminated' }}</p>
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
                                <p class="mt-2 text-xs uppercase tracking-widest text-slate-500">{{ str_replace('_', ' ', $gamePlayer->role) }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            @if ($game->status === 'finished')
                <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-6">
                    <h2 class="text-2xl font-black text-emerald-200">Winner: {{ str_replace('_', ' ', $game->winner) }}</h2>
                    <p class="mt-2 text-slate-300">Civilian word: {{ $game->civilian_word }} · Undercover word: {{ $game->undercover_word }}</p>
                </div>
            @endif
        </div>

        <aside class="space-y-6">
            <div class="rounded-2xl border border-slate-800 bg-slate-900 p-6">
                <h2 class="text-xl font-bold">Host Controls</h2>
                @if ($game->room->host_id === auth()->id() && $game->status !== 'finished')
                    <form method="POST" action="{{ route('games.transition', $game) }}" class="mt-4">
                        @csrf
                        <button class="w-full rounded-xl bg-cyan-400 px-4 py-3 font-bold text-slate-950">Advance Phase</button>
                    </form>
                @else
                    <p class="mt-3 text-sm text-slate-400">Only the host can advance phases.</p>
                @endif
            </div>

            <div class="rounded-2xl border border-slate-800 bg-slate-900 p-6">
                <h2 class="text-xl font-bold">Chat</h2>
                <div class="mt-4 max-h-80 space-y-3 overflow-y-auto pr-2" id="chat-messages">
                    <template x-for="message in messages" :key="message.id">
                        <div class="rounded-xl bg-slate-950 p-3">
                            <p class="text-sm font-semibold text-cyan-200" x-text="message.user.username"></p>
                            <p class="text-sm text-slate-200" x-text="message.message"></p>
                        </div>
                    </template>
                </div>
                <form method="POST" action="{{ route('games.chat', $game) }}" class="mt-4 space-y-3" @submit.prevent="
                    $el.submit();
                    $el.reset();
                    fetchNewMessages();
                ">
                    @csrf
                    <textarea name="message" rows="3" x-model="newMessage" {{ $game->status !== 'discussion' ? 'disabled' : '' }} class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white outline-none focus:border-cyan-400 disabled:opacity-50" placeholder="Give a clue..."></textarea>
                    <button class="w-full rounded-xl bg-slate-100 px-4 py-3 font-bold text-slate-950 disabled:opacity-50" {{ $game->status !== 'discussion' ? 'disabled' : '' }}>Send</button>
                </form>
            </div>
        </aside>
    </div>
</section>

<script>
function gameComponent() {
    return {
        // Timer properties
        remaining: Math.max(0, Math.floor((new Date('{{ optional($game->current_phase_end_time)->toIso8601String() }}').getTime() - Date.now()) / 1000)),

        // Game status properties
        gameStatus: '{{ $game->status }}',
        gameRound: {{ $game->round }},
        statusPollingInterval: null,

        // Chat properties
        messages: @js($game->chatMessages->sortBy('created_at')->toArray()),
        lastMessageId: @js($game->chatMessages->max('id') ?? 0),
        newMessage: '',
        echoChannel: null,

        init() {
            // Start timer countdown
            setInterval(() => {
                if (this.remaining > 0) this.remaining--;
            }, 1000);

            // Setup WebSocket listeners
            this.setupRealtimeListeners();

            // Scroll chat to bottom on load
            if (this.gameStatus === 'discussion') {
                this.scrollToBottom();
            }
        },

        setupRealtimeListeners() {
            if (!window.Echo) {
                console.error('Laravel Echo is not initialized');
                // Fallback to polling if Echo is not available
                this.setupPollingFallback();
                return;
            }

            console.log('Setting up WebSocket connection for game {{ $game->id }}');

            // Subscribe to the game channel
            this.echoChannel = window.Echo.private('game.{{ $game->id }}');

            // Log connection status
            this.echoChannel.subscribed(() => {
                console.log('✅ Successfully subscribed to game.{{ $game->id }} channel');
            });

            this.echoChannel.error((error) => {
                console.error('❌ WebSocket subscription error:', error);
                this.setupPollingFallback();
            });

            // Listen for game status changes
            this.echoChannel
                .listen('.game.started', (e) => {
                    console.log('🎮 Game started event received');
                    window.location.reload();
                })
                .listen('.phase.changed', (e) => {
                    console.log('🔄 Phase changed event received');
                    window.location.reload();
                })
                .listen('.chat.message', (e) => {
                    console.log('💬 Chat message received via WebSocket:', e);
                    // Add new message if it doesn't already exist
                    const existingIds = new Set(this.messages.map(m => m.id));
                    if (e.message && !existingIds.has(e.message.id)) {
                        console.log('➕ Adding new message to chat:', e.message);
                        this.messages.push(e.message);
                        this.lastMessageId = Math.max(this.lastMessageId, e.message.id);
                        this.$nextTick(() => {
                            this.scrollToBottom();
                        });
                    } else {
                        console.log('⚠️ Message already exists or invalid:', e.message);
                    }
                });

            // Polling for phase changes every 2 seconds as backup
            // This ensures phase changes are detected even if WebSocket fails
            this.statusPollingInterval = setInterval(() => {
                this.checkGameStatus();
                this.checkForMissedMessages();
            }, 2000);
        },

        setupPollingFallback() {
            // Only used if WebSocket is not available
            console.warn('Using polling fallback for real-time updates');
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
                        console.log('Game status changed, reloading...');
                        window.location.reload();
                    }
                }
            } catch (error) {
                console.error('Failed to check game status:', error);
            }
        },

        async checkForMissedMessages() {
            // Light polling to catch any messages that might have been missed
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