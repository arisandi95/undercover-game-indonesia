<?php

namespace App\Events;

use App\Models\Game;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameStarted implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public Game $game) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('game.'.$this->game->id),
            new PrivateChannel('room.'.$this->game->room_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'game.started';
    }

    public function broadcastWith(): array
    {
        return [
            'game_id' => $this->game->id,
            'room_id' => $this->game->room_id,
            'game_url' => route('games.show', $this->game),
        ];
    }
}
