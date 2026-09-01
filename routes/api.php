<?php

use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;

Route::get('/messages', [MessageController::class, 'index']);
Route::get('/messages/{message}', [MessageController::class, 'show']);
Route::post('/messages', [MessageController::class, 'store']);