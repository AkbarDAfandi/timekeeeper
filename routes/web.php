<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/source-login', [PlayerAuthController::class, 'showLoginForm'])->name('player.login');
Route::post('/source-login', [PlayerAuthController::class, 'authenticate']);
Route::get('/source-display', [PlayerDisplayController::class, 'index'])->name('player.display')->middleware('auth');
