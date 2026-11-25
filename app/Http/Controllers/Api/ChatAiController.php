<?php
// Fitur: API chat bertoken dan log penggunaan
// connect with ../../../../routes/api.php, ../../../Models/AiChatLog.php, ../../../Services/ChatAiService.php
// connect with ../../Requests/ChatAiRequest.php, ../../Resources/ChatAiResource.php, ../Controller.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChatAiRequest;
use App\Http\Resources\ChatAiResource;
use App\Models\AiChatLog;
use App\Services\ChatAiService;
use Illuminate\Http\JsonResponse;

class ChatAiController extends Controller
{
    public function __invoke(ChatAiRequest $request, ChatAiService $service): JsonResponse
    {
        $prompt = $request->validated('prompt');
        $answer = $service->reply($prompt);
        $log = AiChatLog::create([
            'user_id' => $request->user()->id,
            'prompt' => $prompt,
            ...$answer,
        ]);

        return (new ChatAiResource($log))->additional(['status' => 'success'])->response()->setStatusCode(200);
    }
}
