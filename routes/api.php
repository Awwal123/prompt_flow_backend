<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ChatController;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        // Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/chats', [ChatController::class, 'getChats']);
    Route::post('/chats', [ChatController::class, 'createChat']);
    Route::delete('/chats/{chatId}', [ChatController::class, 'deleteChat']);
    Route::get('/chats/{chatId}/messages', [ChatController::class, 'getMessages']);
    Route::post('/chats/{chatId}/messages', [ChatController::class, 'sendMessage']);
});