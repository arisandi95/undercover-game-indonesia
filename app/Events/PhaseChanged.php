<?php

namespace App\Events;

use App\Models\Game;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PhaseChanged implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public Game $game) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('game.'.$this->game->id)];
    }

    public function broadcastAs(): string
    {
        return 'phase.changed';
    }
}
