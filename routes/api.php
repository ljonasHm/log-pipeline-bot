<?php

use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;
use SergiX44\Nutgram\Nutgram;

Route::post('/telegram/webhook', fn(Nutgram $bot) => $bot->run());

// Route::get('/messages', [MessageController::class, 'index']);
// Route::get('/messages/{message}', [MessageController::class, 'show']);

Route::post('/messages', [MessageController::class, 'store'])->middleware('api-key');

// Route::put('/messages/{message}', [MessageController::class, 'update']);
// Route::patch('/messages/{message}', [MessageController::class, 'update']);

// Route::delete('/messages/{message}', [MessageController::class, 'destroy']);