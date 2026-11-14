<?php

use App\Http\Controllers\Api\ChatAiController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/chat-ai', ChatAiController::class);
});
