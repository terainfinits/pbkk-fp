<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Cast JSON metadata into native PHP array.
     */
    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the session that this message belongs to.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class, 'chat_session_id');
    }
}
