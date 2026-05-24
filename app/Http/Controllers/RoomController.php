<?php

namespace App\Http\Controllers;

use App\Events\PlayerLeftRoom;
use App\Http\Requests\StoreRoomRequest;
use App\Models\Room;
use App\Services\GameService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::with(['host', 'latestGame.players.user'])->latest()->paginate(12);

        return view('lobby.index', compact('rooms'));
    }

    public function create()
    {
        return view('lobby.create');
    }

    public function store(StoreRoomRequest $request, GameService $gameService): RedirectResponse
    {
        $room = DB::transaction(function () use ($request, $gameService) {
            $room = Room::create([
                'name' => $request->validated('name'),
                'max_players' => $request->validated('max_players'),
                'host_id' => $request->user()->id,
            ]);

            $gameService->joinWaitingGame($room, $request->user());

            return $room;
        });

        return redirect()->route('rooms.show', $room)->with('status', 'Room created.');
    }

    public function show(Room $room)
    {
        $room->load(['host', 'latestGame.players.user']);

        return view('lobby.show', compact('room'));
    }

    public function players(Room $room, Request $request)
    {
        $room->load(['latestGame.players.user']);

        $isRoomMember = $room->host_id === $request->user()->id
            || $room->latestGame?->players()->where('user_id', $request->user()->id)->exists();

        abort_unless($isRoomMember, 403);

        $players = collect($room->latestGame?->players ?? [])->map(function ($player) use ($room) {
            return [
                'user_id' => $player->user_id,
                'user' => [
                    'username' => $player->user->username,
                ],
                'is_host' => $player->user_id === $room->host_id,
            ];
        })->values();

        if (!$players->contains('user_id', $room->host_id)) {
            $players->prepend([
                'user_id' => $room->host_id,
                'user' => [
                    'username' => $room->host->username,
                ],
                'is_host' => true,
            ]);
        }

        return response()->json($players->values());
    }

    public function status(Room $room, Request $request)
    {
        $room->load(['latestGame.players']);

        $isRoomMember = $room->host_id === $request->user()->id
            || $room->latestGame?->players()->where('user_id', $request->user()->id)->exists();

        abort_unless($isRoomMember, 403);

        return response()->json([
            'status' => $room->status,
            'game_id' => $room->latestGame?->id,
            'game_url' => $room->latestGame ? route('games.show', $room->latestGame) : null,
            'has_started' => $room->status !== 'waiting' && $room->latestGame !== null,
        ]);
    }

    public function join(Room $room, Request $request, GameService $gameService): RedirectResponse
    {
        try {
            $gameService->joinWaitingGame($room, $request->user());
        } catch (\Throwable $exception) {
            return back()->withErrors(['room' => $exception->getMessage()]);
        }

        return redirect()->route('rooms.show', $room)->with('status', 'Joined room.');
    }

    public function leave(Room $room, Request $request): RedirectResponse
    {
        $room->latestGame?->players()->where('user_id', $request->user()->id)->delete();

        try {
            broadcast(new PlayerLeftRoom($room, $request->user()))->toOthers();
        } catch (\Throwable $exception) {
            Log::warning('Room leave broadcast failed', [
                'room_id' => $room->id,
                'user_id' => $request->user()->id,
                'message' => $exception->getMessage(),
            ]);
        }

        return redirect()->route('rooms.index')->with('status', 'Left room.');
    }

    public function destroy(Room $room, Request $request): RedirectResponse
    {
        abort_unless($room->host_id === $request->user()->id, 403);
        $room->delete();

        return redirect()->route('rooms.index')->with('status', 'Room deleted.');
    }
}
