<?php
// Fitur: Harness tugas coding
// connect with ../app/Http/Controllers/HarnessController.php, ../resources/js/components/agent-ide/HarnessPanel.vue, ../tests/Feature/HarnessTest.php
// connect with ../tests/Feature/LiveHarnessTest.php, ./web.php

use App\Http\Controllers\HarnessController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('ide/api/harness')->controller(HarnessController::class)->group(function () {
    Route::get('/workspaces', 'index');
    Route::post('/workspaces/{workspace}/runs', 'start')->whereNumber('workspace');
    Route::get('/runs/{run}', 'show')->whereNumber('run');
    Route::post('/runs/{run}/{action}', 'action')->whereNumber('run')->whereIn('action', ['cancel', 'apply', 'reject']);
});
