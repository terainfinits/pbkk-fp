<?php
// Fitur: API chat bertoken dan log penggunaan
// connect with ../../app/Models/AiChatLog.php, ../../tests/Feature/ChatAiTest.php, ./0001_01_01_000000_create_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_chat_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('prompt');
            $table->longText('ai_response');
            $table->string('model_used');
            $table->unsignedBigInteger('tokens_consumed')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chat_logs');
    }
};
