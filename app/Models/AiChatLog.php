<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiChatLog extends Model
{
    protected $fillable = ['user_id', 'prompt', 'ai_response', 'model_used', 'tokens_consumed'];
}
