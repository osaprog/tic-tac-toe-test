<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;

Route::get('/', [GameController::class, 'create']);
Route::post('/games/{gameId}/move', [GameController::class, 'move']);