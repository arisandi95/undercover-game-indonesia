<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['name', 'host_id', 'status', 'max_players'])]
class Room extends Model
{
    use HasFactory;

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }

    public function latestGame(): HasOne
    {
        return $this->hasOne(Game::class)->latestOfMany();
    }

    public function getCurrentPlayersAttribute(): int
    {
        $game = $this->relationLoaded('latestGame') ? $this->latestGame : $this->latestGame()->first();

        return $game ? $game->players()->count() : 1;
    }
}
