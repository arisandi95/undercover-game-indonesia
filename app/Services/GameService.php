<?php

namespace App\Services;

use App\Events\GameFinished;
use App\Events\GameStarted;
use App\Events\PhaseChanged;
use App\Events\PlayerEliminated;
use App\Events\PlayerJoinedRoom;
use App\Events\PlayerLeftRoom;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\Room;
use App\Models\User;
use App\Models\WordPair;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class GameService
{
    public function startGame(Room $room): Game
    {
        if ($room->status !== null && $room->status !== 'waiting') {
            throw new InvalidArgumentException('Room is not waiting for players.');
        }

        $existingGame = $room->latestGame()->with('players.user')->first();
        $players = $existingGame?->players->pluck('user')->filter()->values() ?? collect([$room->host]);

        if ($players->count() < 3) {
            throw new InvalidArgumentException('At least 3 players are required to start.');
        }

        return DB::transaction(function () use ($room, $players) {
            $wordPair = $this->selectWordPair();

            $game = Game::create([
                'room_id' => $room->id,
                'round' => 1,
                'status' => 'discussion',
                'civilian_word' => $wordPair->civilian_word,
                'undercover_word' => $wordPair->undercover_word,
                'current_phase_end_time' => now()->addSeconds(120),
            ]);

            $this->assignRoles($game, $players->pluck('id')->all());
            $room->update(['status' => 'in_progress']);

            Log::info('Game started', ['game_id' => $game->id, 'room_id' => $room->id]);

            try {
                broadcast(new GameStarted($game->fresh(['players.user'])))->toOthers();
            } catch (\Throwable $exception) {
                Log::warning('Game start broadcast failed', [
                    'game_id' => $game->id,
                    'room_id' => $room->id,
                    'message' => $exception->getMessage(),
                ]);
            }

            return $game->fresh(['players.user', 'room']);
        });
    }

    public function joinWaitingGame(Room $room, User $user): Game
    {
        return DB::transaction(function () use ($room, $user) {
            if ($room->status !== null && $room->status !== 'waiting') {
                throw new InvalidArgumentException('Room already started.');
            }

            $game = $room->latestGame()->first() ?? Game::create([
                'room_id' => $room->id,
                'round' => 1,
                'status' => 'setup',
                'civilian_word' => 'pending',
                'undercover_word' => 'pending',
            ]);

            if ($game->players()->count() >= $room->max_players) {
                throw new InvalidArgumentException('Room is full.');
            }

            $game->players()->firstOrCreate([
                'user_id' => $user->id,
            ], [
                'role' => 'civilian',
                'is_alive' => true,
            ]);

            try {
                broadcast(new PlayerJoinedRoom($room, $user))->toOthers();
            } catch (\Throwable $exception) {
                Log::warning('Room join broadcast failed', [
                    'room_id' => $room->id,
                    'user_id' => $user->id,
                    'message' => $exception->getMessage(),
                ]);
            }

            return $game->fresh(['players.user']);
        });
    }

    public function assignRoles(Game $game, array $userIds): void
    {
        shuffle($userIds);
        $undercoverId = array_shift($userIds);
        $mrWhiteId = array_shift($userIds);

        GamePlayer::updateOrCreate(['game_id' => $game->id, 'user_id' => $undercoverId], ['role' => 'undercover', 'is_alive' => true]);
        GamePlayer::updateOrCreate(['game_id' => $game->id, 'user_id' => $mrWhiteId], ['role' => 'mr_white', 'is_alive' => true]);

        foreach ($userIds as $userId) {
            GamePlayer::updateOrCreate(['game_id' => $game->id, 'user_id' => $userId], ['role' => 'civilian', 'is_alive' => true]);
        }
    }

    public function selectWordPair(): WordPair
    {
        $wordPair = WordPair::inRandomOrder()->first();

        if (!$wordPair) {
            throw new InvalidArgumentException('No word pairs are available.');
        }

        return $wordPair;
    }

    public function processVote(Game $game, User $voter, User $votedFor): Game
    {
        return app(VoteService::class)->recordVote($game, $voter, $votedFor);
    }

    public function eliminatePlayer(Game $game, User $player): void
    {
        $gamePlayer = $game->players()->where('user_id', $player->id)->firstOrFail();
        $gamePlayer->update(['is_alive' => false]);

        // broadcast(new PlayerEliminated($game->fresh(['players.user']), $gamePlayer))->toOthers();
        Log::info('Player eliminated', ['game_id' => $game->id, 'user_id' => $player->id, 'role' => $gamePlayer->role]);
    }

    public function checkWinConditions(Game $game): ?string
    {
        $alive = $game->players()->where('is_alive', true)->get();
        $aliveRoles = $alive->pluck('role');

        if (!$aliveRoles->contains('undercover') && !$aliveRoles->contains('mr_white')) {
            return 'civilians';
        }

        if (!$aliveRoles->contains('civilian')) {
            return $aliveRoles->contains('mr_white') ? 'mr_white' : 'undercover';
        }

        if ($alive->count() <= 2 && $aliveRoles->contains('undercover')) {
            return 'undercover';
        }

        return null;
    }

    public function transitionPhase(Game $game): Game
    {
        $next = match ($game->status) {
            'setup' => ['discussion', 120],
            'discussion' => ['voting', 60],
            'voting' => ['results', 30],
            'results' => ['discussion', 120],
            default => ['finished', null],
        };

        $game->update([
            'status' => $next[0],
            'round' => $game->status === 'results' ? $game->round + 1 : $game->round,
            'current_phase_end_time' => $next[1] ? now()->addSeconds($next[1]) : null,
        ]);

        try {
            // Broadcast to all users including the admin who triggered the phase change
            broadcast(new PhaseChanged($game->fresh()));
        } catch (\Throwable $exception) {
            Log::warning('Phase change broadcast failed', [
                'game_id' => $game->id,
                'message' => $exception->getMessage(),
            ]);
        }

        return $game->fresh(['players.user']);
    }

    public function finishGame(Game $game, string $winner): Game
    {
        $game->update([
            'status' => 'finished',
            'winner' => $winner,
            'current_phase_end_time' => null,
        ]);
        $game->room->update(['status' => 'finished']);

        // broadcast(new GameFinished($game->fresh(['players.user'])))->toOthers();

        return $game->fresh(['players.user']);
    }
}
