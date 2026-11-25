<?php
// Fitur: API chat bertoken dan log penggunaan
// connect with ../../database/migrations/2026_11_13_000002_create_ai_chat_logs_table.php, ../Http/Controllers/Api/ChatAiController.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiChatLog extends Model
{
    protected $fillable = ['user_id', 'prompt', 'ai_response', 'model_used', 'tokens_consumed'];
}
