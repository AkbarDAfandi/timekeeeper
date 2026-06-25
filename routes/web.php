<?php

use App\Http\Controllers\PlayerAuthController;
use App\Http\Controllers\PlayerController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/source-login', [PlayerAuthController::class, 'showLoginForm'])->name('player.login');
Route::post('/source-login', [PlayerAuthController::class, 'authenticate']);

Route::middleware(['auth'])->group(function () {
    Route::get('/source-display', [PlayerController::class, 'index'])->name('player.display');
    Route::get('/source-payload', [PlayerController::class, 'payload'])->name('player.payload');
});
