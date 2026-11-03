<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JoinController;
use App\Http\Controllers\LiveController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ScoreEventController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\TournamentController;
use App\Http\Controllers\TournamentGameController;
use Illuminate\Support\Facades\Route;

/*
| Terminology: toernooi = Tournament (several spellen, one winner by total points),
|              spel     = Game (one hand; part of a toernooi or played on its own).
*/

// --- Open for everybody -------------------------------------------------------------------
Route::get('/', HomeController::class)->name('home');
Route::view('/spelregels', 'pages.rules')->name('pages.rules');
Route::view('/handleiding', 'pages.guide')->name('pages.guide');
Route::view('/privacy', 'pages.privacy')->name('pages.privacy');
Route::view('/cookies', 'pages.cookies')->name('pages.cookies');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::post('/join', [JoinController::class, 'store'])->middleware('throttle:15,1')->name('join');

// Guests follow a toernooi or standalone spel live with the secret link or the game ID, no account needed.
Route::get('/live/{token}', [LiveController::class, 'show'])->name('live.show');
Route::get('/live/{token}/data', [LiveController::class, 'data'])->middleware('throttle:60,1')->name('live.data');
Route::get('/claim/{token}/{player}', [ClaimController::class, 'start'])->name('claim.start');

// --- Accounts -----------------------------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:20,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// --- Logged in ----------------------------------------------------------------------------
Route::middleware('auth')->group(function () {
    Route::get('/friends', [FriendController::class, 'index'])->name('friends.index');
    Route::post('/friends', [FriendController::class, 'store'])->name('friends.store');
    Route::post('/friends/{friendship}/accept', [FriendController::class, 'accept'])->name('friends.accept');
    Route::delete('/friends/{friendship}', [FriendController::class, 'destroy'])->name('friends.destroy');

    Route::get('/ranking', [RankingController::class, 'index'])->name('ranking.index');

    // Toernooien
    Route::controller(TournamentController::class)->prefix('tournaments')->name('tournaments.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{tournament}', 'show')->name('show');
        Route::get('/{tournament}/version', 'version')->name('version');
        Route::post('/{tournament}/finish', 'finish')->name('finish');
        Route::post('/{tournament}/target', 'updateTarget')->name('target');
        Route::post('/{tournament}/link', 'regenerateLink')->name('link');
        Route::delete('/{tournament}', 'destroy')->name('destroy');
    });
    Route::post('/tournaments/{tournament}/games', [TournamentGameController::class, 'store'])->name('tournaments.games.store');

    // Spellen (standalone or inside a toernooi)
    Route::controller(GameController::class)->prefix('games')->name('games.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{game}', 'show')->name('show');
        Route::get('/{game}/version', 'version')->name('version');
        Route::post('/{game}/start', 'start')->name('start');
        Route::post('/{game}/finish', 'finish')->name('finish');
        Route::post('/{game}/reopen', 'reopen')->name('reopen');
        Route::post('/{game}/link', 'regenerateLink')->name('link');
        Route::patch('/{game}/visibility', 'updateVisibility')->name('visibility');
        Route::delete('/{game}', 'destroy')->name('destroy');
    });

    // Scores inside a spel. {event} must belong to {game}.
    Route::controller(ScoreEventController::class)->prefix('games/{game}')->name('events.')->scopeBindings()->group(function () {
        Route::post('/events', 'store')->name('store');
        Route::post('/timeout', 'timeout')->name('timeout');
        Route::delete('/events/{event}', 'destroy')->name('destroy');
        Route::post('/events/{event}/lock', 'toggleLock')->name('lock');
    });
});
