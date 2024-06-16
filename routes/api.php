<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicTacToeController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/input', [TicTacToeController::class, 'handleInput']);
Route::post('/process-move', [TicTacToeController::class, 'handleVoiceProcess']);