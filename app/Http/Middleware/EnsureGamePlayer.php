<?php

namespace App\Http\Middleware;

use App\Models\Game;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGamePlayer
{
    public function handle(Request $request, Closure $next): Response
    {
        $game = $request->route('game');

        if (!$game instanceof Game) {
            $game = Game::findOrFail($game);
        }

        if (!$game->players()->where('user_id', $request->user()?->id)->exists()) {
            abort(403, 'You are not a player in this game.');
        }

        return $next($request);
    }
}
