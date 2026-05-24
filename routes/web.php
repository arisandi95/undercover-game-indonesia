<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\WelcomeController;

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::resource('rooms', RoomController::class)->except(['edit', 'update']);
    Route::get('/rooms/{room}/players', [RoomController::class, 'players'])->name('rooms.players');
    Route::get('/rooms/{room}/status', [RoomController::class, 'status'])->name('rooms.status');
    Route::post('/rooms/{room}/join', [RoomController::class, 'join'])->name('rooms.join');
    Route::post('/rooms/{room}/leave', [RoomController::class, 'leave'])->name('rooms.leave');
    Route::post('/rooms/{room}/games', [GameController::class, 'store'])->name('games.store');

    Route::get('/games/{game}', [GameController::class, 'show'])->name('games.show');

    Route::middleware('game.player')->group(function () {
        Route::post('/games/{game}/vote', [GameController::class, 'vote'])->name('games.vote');
        Route::post('/games/{game}/chat', [GameController::class, 'chat'])->name('games.chat');
        Route::post('/games/{game}/transition', [GameController::class, 'transition'])->name('games.transition');
    });
});
