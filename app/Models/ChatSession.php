<?php
// Fitur: Riwayat dan ekspor percakapan
// connect with ../../database/migrations/2026_10_18_100000_create_chat_storage_tables.php, ../../database/migrations/2026_10_19_000000_add_user_id_to_chat_sessions_table.php, ../../tests/Feature/AuthTest.php
// connect with ../../tests/Feature/ChatStorageTest.php, ../Http/Controllers/Api/AgentIde/ChatController.php, ../Http/Controllers/Api/AgentIde/ChatSessionController.php
// connect with ./ChatMessage.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatSession extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Get all messages associated with this chat session.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'chat_session_id')->orderBy('id', 'asc');
    }
}
