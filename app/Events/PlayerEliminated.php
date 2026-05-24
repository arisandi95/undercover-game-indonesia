<?php

namespace App\Events;

use App\Models\Game;
use App\Models\GamePlayer;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerEliminated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public Game $game, public GamePlayer $player) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('game.'.$this->game->id)];
    }

    public function broadcastAs(): string
    {
        return 'player.eliminated';
    }
}
