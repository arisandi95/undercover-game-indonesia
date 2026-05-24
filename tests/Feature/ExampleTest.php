    public function test_chat_api_returns_new_messages(): void
    {
        // Create word pairs
        \App\Models\WordPair::create(['civilian_word' => 'apple', 'undercover_word' => 'orange', 'difficulty' => 'easy']);
        \App\Models\WordPair::create(['civilian_word' => 'cat', 'undercover_word' => 'dog', 'difficulty' => 'easy']);

        $users = User::factory()->count(3)->create();
        $room = Room::create(['name' => 'Test Room', 'host_id' => $users[0]->id, 'max_players' => 8, 'status' => 'waiting']);

        // Add all users to the room
        $gameService = app(GameService::class);
        foreach ($users as $user) {
            $gameService->joinWaitingGame($room, $user);
        }

        $game = $gameService->startGame($room);

        // Create a chat message
        ChatMessage::create([
            'game_id' => $game->id,
            'user_id' => $users[0]->id,
            'message' => 'Test message',
        ]);

        $response = $this->actingAs($users[0])->getJson("/api/games/{$game->id}/chat");

        $response->assertOk()
                 ->assertJsonStructure([
                     '*' => [
                         'id',
                         'message',
                         'user' => ['username'],
                         'created_at'
                     ]
                 ]);
    }