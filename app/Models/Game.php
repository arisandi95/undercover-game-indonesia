<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['room_id', 'round', 'status', 'civilian_word', 'undercover_word', 'current_phase_end_time', 'winner'])]
class Game extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'current_phase_end_time' => 'datetime',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function players(): HasMany
    {
        return $this->hasMany(GamePlayer::class);
    }

    public function alivePlayers(): HasMany
    {
        return $this->players()->where('is_alive', true);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }
}
