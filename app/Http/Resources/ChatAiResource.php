<?php
// Fitur: API chat bertoken dan log penggunaan
// connect with ../Controllers/Api/ChatAiController.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatAiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'session_id' => $this->id,
            'model_used' => $this->model_used,
            'prompt' => $this->prompt,
            'ai_response' => $this->ai_response,
            'timestamp' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
