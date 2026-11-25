<?php
// Fitur: API chat bertoken dan log penggunaan
// connect with ../app/Http/Controllers/Api/ChatAiController.php, ../bootstrap/app.php, ../tests/Feature/ChatAiTest.php

use App\Http\Controllers\Api\ChatAiController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/chat-ai', ChatAiController::class);
});
