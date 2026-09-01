<?php

use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;

Route::get('/messages', [MessageController::class, 'index']);
Route::get('/messages/{message}', [MessageController::class, 'show']);

Route::post('/messages', [MessageController::class, 'store']);

Route::put('/messages/{message}', [MessageController::class, 'update']);
Route::patch('/messages/{message}', [MessageController::class, 'update']);

Route::delete('/messages/{message}', [MessageController::class, 'destroy']);