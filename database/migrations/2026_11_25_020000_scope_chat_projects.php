<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('chat_sessions', fn(Blueprint $t) => $t->unsignedBigInteger('workspace_id')->nullable()->index());
    }
    public function down(): void {
        Schema::table('chat_sessions', fn(Blueprint $t) => $t->dropColumn('workspace_id'));
    }
};
