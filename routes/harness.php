<?php

use App\Http\Controllers\HarnessController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('ide/api/harness')->controller(HarnessController::class)->group(function () {
    Route::get('/workspaces', 'index');
    Route::post('/workspaces/{workspace}/runs', 'start')->whereNumber('workspace');
    Route::get('/runs/{run}', 'show')->whereNumber('run');
    Route::post('/runs/{run}/{action}', 'action')->whereNumber('run')->whereIn('action', ['cancel', 'apply', 'reject']);
});
