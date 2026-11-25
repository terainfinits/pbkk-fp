<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('workspaces', fn (Blueprint $t) => $t->json('extra_checks')->nullable());
        Schema::table('agent_runs', function (Blueprint $t) {
            $t->string('phase')->nullable();
            $t->string('stop_reason')->nullable();
            $t->string('revision')->nullable();
            $t->json('plan')->nullable();
            $t->json('checks')->nullable();
            $t->json('evidence')->nullable();
            $t->text('question')->nullable();
            $t->text('clarifications')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('agent_runs', fn (Blueprint $t) => $t->dropColumn([
            'phase', 'stop_reason', 'revision', 'plan', 'checks', 'evidence', 'question', 'clarifications',
        ]));
        Schema::table('workspaces', fn (Blueprint $t) => $t->dropColumn('extra_checks'));
    }
};
