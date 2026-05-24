<?php

namespace App\Services;

use App\Events\PlayerVoted;
use App\Models\Game;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class VoteService
{
    public function recordVote(Game $game, User $voter, User $votedFor): Game
    {
        return DB::transaction(function () use ($game, $voter, $votedFor) {
            $voterPlayer = $game->players()->where('user_id', $voter->id)->where('is_alive', true)->first();
            $targetPlayer = $game->players()->where('user_id', $votedFor->id)->where('is_alive', true)->first();

            if (!$voterPlayer || !$targetPlayer) {
                throw new InvalidArgumentException('Both voter and target must be alive players.');
            }

            if ($this->hasPlayerVoted($game, $voter)) {
                throw new InvalidArgumentException('You have already voted this round.');
            }

            Vote::create([
                'game_id' => $game->id,
                'voter_id' => $voter->id,
                'voted_for_id' => $votedFor->id,
                'round' => $game->round,
            ]);

            $targetPlayer->increment('votes_received');
            // broadcast(new PlayerVoted($game->fresh(['players.user']), $voter, $votedFor))->toOthers();

            if ($this->allAlivePlayersVoted($game)) {
                $eliminated = $this->getEliminatedPlayer($game);

                if ($eliminated) {
                    app(GameService::class)->eliminatePlayer($game, $eliminated);
                }

                $winner = app(GameService::class)->checkWinConditions($game->fresh('players'));

                if ($winner) {
                    return app(GameService::class)->finishGame($game, $winner);
                }

                $game->players()->update(['votes_received' => 0]);
                $game->update(['status' => 'results', 'current_phase_end_time' => now()->addSeconds(30)]);
            }

            return $game->fresh(['players.user', 'votes']);
        });
    }

    public function getVoteCount(Game $game, User $player): int
    {
        return $game->votes()->where('round', $game->round)->where('voted_for_id', $player->id)->count();
    }

    public function getEliminatedPlayer(Game $game): ?User
    {
        $topVote = $game->votes()
            ->where('round', $game->round)
            ->select('voted_for_id', DB::raw('count(*) as votes_count'))
            ->groupBy('voted_for_id')
            ->orderByDesc('votes_count')
            ->orderBy('voted_for_id')
            ->first();

        return $topVote ? User::find($topVote->voted_for_id) : null;
    }

    public function hasPlayerVoted(Game $game, User $player): bool
    {
        return $game->votes()->where('round', $game->round)->where('voter_id', $player->id)->exists();
    }

    private function allAlivePlayersVoted(Game $game): bool
    {
        $aliveCount = $game->players()->where('is_alive', true)->count();
        $voteCount = $game->votes()->where('round', $game->round)->distinct('voter_id')->count('voter_id');

        return $aliveCount > 0 && $voteCount >= $aliveCount;
    }
}