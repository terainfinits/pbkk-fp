<?php
// Fitur: Riwayat dan ekspor percakapan
// connect with ../../app/Models/ChatSession.php, ./0001_01_01_000000_create_users_table.php, ./2026_10_18_100000_create_chat_storage_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_sessions', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('chat_sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
