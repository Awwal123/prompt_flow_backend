<?php

use App\Http\Controllers\API\ChatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']); 
    Route::post('/login', [AuthController::class, 'login']);      
});


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']); 
    // Route::get('/auth/me', [AuthController::class, 'me']);  
});

Route::middleware('auth:sanctum')->group(function() {
    Route::get('/chats', [ChatController::class, 'getChats']);
    Route::post('/chats', [ChatController::class, 'createChat']);
    Route::delete('/chats/{chatId}', [ChatController::class, 'deleteChat']);
    Route::get('/chats/{chatId}/messages', [ChatController::class, 'getMessages']);
    Route::post('/chats/{chatId}/messages', [ChatController::class, 'sendMessage']);
});