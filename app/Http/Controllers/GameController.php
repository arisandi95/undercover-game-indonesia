<?php

namespace App\Http\Controllers;

use App\Events\ChatMessageSent;
use App\Http\Requests\StoreChatMessageRequest;
use App\Http\Requests\StoreVoteRequest;
use App\Models\ChatMessage;
use App\Models\Game;
use App\Models\Room;
use App\Models\User;
use App\Services\GameService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function store(Room $room, GameService $gameService): RedirectResponse
    {
        abort_unless($room->host_id === auth()->id(), 403);

        try {
            $game = $gameService->startGame($room);
        } catch (\Throwable $exception) {
            return back()->withErrors(['game' => $exception->getMessage()]);
        }

        return redirect()->route('games.show', $game)->with('status', 'Game started.');
    }

    public function show(Game $game)
    {
        $game->load(['room.host', 'players.user', 'chatMessages.user', 'votes']);
        $player = $game->players->firstWhere('user_id', auth()->id());
        $word = match ($player?->role) {
            'civilian' => $game->civilian_word,
            'undercover' => $game->undercover_word,
            'mr_white' => 'You are Mr. White. Blend in without a word.',
            default => null,
        };
        $hasVoted = $game->votes()->where('round', $game->round)->where('voter_id', auth()->id())->exists();

        return view('game.index', compact('game', 'player', 'word', 'hasVoted'));
    }

    public function vote(StoreVoteRequest $request, Game $game, GameService $gameService): RedirectResponse
    {
        try {
            $gameService->processVote($game, $request->user(), User::findOrFail($request->validated('voted_for_id')));
        } catch (\Throwable $exception) {
            return back()->withErrors(['vote' => $exception->getMessage()]);
        }

        return back()->with('status', 'Vote submitted.');
    }

    public function chat(StoreChatMessageRequest $request, Game $game): RedirectResponse
    {
        if ($game->status !== 'discussion') {
            return back()->withErrors(['chat' => 'Chat is only enabled during discussion.']);
        }

        $message = ChatMessage::create([
            'game_id' => $game->id,
            'user_id' => $request->user()->id,
            'message' => strip_tags($request->validated('message')),
        ]);

        // Broadcast to all users including the sender for consistent real-time updates
        broadcast(new ChatMessageSent($message->load('user')));

        return back();
    }

    public function transition(Game $game, GameService $gameService): RedirectResponse
    {
        abort_unless($game->room->host_id === auth()->id(), 403);
        $gameService->transitionPhase($game);

        return back()->with('status', 'Phase advanced.');
    }

    public function getChatMessages(Game $game, Request $request)
    {
        $after = $request->query('after', 0);

        $messages = $game->chatMessages()
            ->with('user:id,username')
            ->where('id', '>', $after)
            ->orderBy('created_at')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'user' => [
                        'username' => $message->user->username,
                    ],
                    'created_at' => $message->created_at->toISOString(),
                ];
            });

        return response()->json($messages);
    }

    public function getGameStatus(Game $game): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => $game->status,
            'round' => $game->round,
            'current_phase_end_time' => $game->current_phase_end_time?->toISOString(),
            'has_started' => $game->status !== 'setup',
        ]);
    }
}