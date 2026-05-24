<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->foreignId('host_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['waiting', 'in_progress', 'finished'])->default('waiting')->index();
            $table->unsignedTinyInteger('max_players')->default(8);
            $table->timestamps();
        });

        Schema::create('word_pairs', function (Blueprint $table) {
            $table->id();
            $table->string('civilian_word', 100);
            $table->string('undercover_word', 100);
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium')->index();
            $table->timestamps();
        });

        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('round')->default(1);
            $table->enum('status', ['setup', 'discussion', 'voting', 'results', 'finished'])->default('setup')->index();
            $table->string('civilian_word', 100);
            $table->string('undercover_word', 100);
            $table->timestamp('current_phase_end_time')->nullable();
            $table->enum('winner', ['civilians', 'undercover', 'mr_white'])->nullable();
            $table->timestamps();
        });

        Schema::create('game_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['civilian', 'undercover', 'mr_white']);
            $table->boolean('is_alive')->default(true)->index();
            $table->unsignedInteger('votes_received')->default(0);
            $table->timestamps();

            $table->unique(['game_id', 'user_id']);
            $table->index(['game_id', 'role']);
        });

        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('voter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('voted_for_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('round');
            $table->timestamps();

            $table->unique(['game_id', 'voter_id', 'round']);
            $table->index(['game_id', 'round', 'voted_for_id']);
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('message');
            $table->timestamps();

            $table->index(['game_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('votes');
        Schema::dropIfExists('game_players');
        Schema::dropIfExists('games');
        Schema::dropIfExists('word_pairs');
        Schema::dropIfExists('rooms');
    }
};
