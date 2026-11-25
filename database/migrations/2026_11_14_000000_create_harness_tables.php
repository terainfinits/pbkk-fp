<?php
// Fitur: Harness tugas coding
// connect with ../../app/Console/Commands/RegisterWorkspace.php, ../../app/Http/Controllers/HarnessController.php, ../../app/Jobs/RunCodingAgent.php
// connect with ../../routes/console.php, ../../tests/Feature/HarnessTest.php, ../../tests/Feature/LiveHarnessTest.php
// connect with ./0001_01_01_000000_create_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('workspaces', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->text('path');
            $t->string('name');
            $t->string('image');
        });
        Schema::create('agent_runs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('workspace_id')->constrained();
            $t->boolean('active')->nullable();
            $t->unique(['workspace_id', 'active']);
            $t->string('status');
            $t->text('task');
            $t->string('base');
            $t->longText('patch')->nullable();
            $t->longText('changes')->nullable();
            $t->timestamps();
        });
        Schema::create('run_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('agent_run_id')->constrained();
            $t->string('kind');
            $t->text('message');
            $t->integer('exit_code')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('run_events');
        Schema::dropIfExists('agent_runs');
        Schema::dropIfExists('workspaces');
    }
};
