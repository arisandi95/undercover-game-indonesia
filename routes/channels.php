<?php

use App\Models\Game;
use App\Models\Room;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('room.{roomId}', function ($user, int $roomId) {
    $room = Room::find($roomId);
    if (!$room) return false;

    // Allow access if user is the host or a player in the room
    return $room->host_id === $user->id ||
           $room->latestGame?->players()->where('user_id', $user->id)->exists();
});

Broadcast::channel('game.{gameId}', function ($user, int $gameId) {
    return Game::whereKey($gameId)->whereHas('players', fn ($query) => $query->where('user_id', $user->id))->exists();
});
