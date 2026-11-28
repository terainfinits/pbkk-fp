<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('run_events', function (Blueprint $t) {
            $t->unsignedInteger('turn')->nullable();
            $t->string('activity')->nullable();
            $t->unsignedBigInteger('time_ms')->nullable();
            $t->unsignedInteger('duration_ms')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('run_events', fn (Blueprint $t) => $t->dropColumn(['turn','activity','time_ms','duration_ms']));
    }
};
