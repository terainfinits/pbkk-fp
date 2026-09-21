<?php

use App\Http\Controllers\AgentIdeController;
use App\Http\Controllers\CalculatorController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'index'])->name('home');
Route::get('/agent/{tema?}', [PageController::class, 'agent'])->name('agent.idea');
Route::get('/mahasiswa/{nrp}', [PageController::class, 'mahasiswaDetail'])
    ->where('nrp', '[0-9]{10}')
    ->name('mahasiswa.detail');
Route::get('/hitung-ipk/{ip1?}/{ip2?}', [CalculatorController::class, 'calculateIpk'])->name('calculator.ipk');

// Agentic AI IDE Routes
Route::prefix('ide')->name('ide.')->group(function () {
    Route::get('/', [AgentIdeController::class, 'index'])->name('index');
    Route::get('/api/tree', [AgentIdeController::class, 'getTree'])->name('api.tree');
    Route::post('/api/file/read', [AgentIdeController::class, 'getFile'])->name('api.file.read');
    Route::post('/api/file/save', [AgentIdeController::class, 'saveFile'])->name('api.file.save');
    Route::post('/api/file/create', [AgentIdeController::class, 'createItem'])->name('api.file.create');
    Route::post('/api/file/delete', [AgentIdeController::class, 'deleteItem'])->name('api.file.delete');
    Route::post('/api/agent/prompt', [AgentIdeController::class, 'promptAgent'])->name('api.agent.prompt');
});

Route::prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [PageController::class, 'index'])->name('home');
    Route::get('/mahasiswa/{nrp}', [PageController::class, 'mahasiswaDetail'])
        ->where('nrp', '[0-9]{10}')
        ->name('mahasiswa.detail');
});

Route::fallback(function () {
    return view('errors.404');
});

