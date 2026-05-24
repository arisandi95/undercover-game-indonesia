<?php

namespace App\Events;

use App\Models\Game;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerVoted implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public Game $game, public User $voter, public User $votedFor) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('game.'.$this->game->id)];
    }

    public function broadcastAs(): string
    {
        return 'player.voted';
    }
}
