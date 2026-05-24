<?php

use App\Http\Controllers\GameController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/profile', fn (Request $request) => $request->user());

Route::middleware(['auth:sanctum', 'game.player'])->group(function () {
    Route::get('/games/{game}/chat', [GameController::class, 'getChatMessages']);
    Route::get('/games/{game}/status', [GameController::class, 'getGameStatus']);
});
