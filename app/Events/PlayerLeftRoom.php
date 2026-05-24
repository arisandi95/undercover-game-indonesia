<?php

namespace App\Events;

use App\Models\Room;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerLeftRoom implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public Room $room, public User $user) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('room.'.$this->room->id)];
    }

    public function broadcastAs(): string
    {
        return 'player.left';
    }

    public function broadcastWith(): array
    {
        return [
            'player' => [
                'id' => $this->user->id,
                'username' => $this->user->username,
            ],
            'total_players' => $this->room->latestGame?->players()->count() ?? 0,
        ];
    }
}