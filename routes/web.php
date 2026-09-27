<?php

use App\Http\Controllers\AgentIdeController;
use App\Http\Controllers\Api\AgentIde\ChatController;
use App\Http\Controllers\Api\AgentIde\FileController;
use App\Http\Controllers\Api\AgentIde\KernelController;
use App\Http\Controllers\CalculatorController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'index'])->name('home');
Route::get('/agent/{tema?}', [PageController::class, 'agent'])->name('agent.idea');
Route::get('/mahasiswa/{nrp}', [PageController::class, 'mahasiswaDetail'])
    ->where('nrp', '[0-9]{10}')
    ->name('mahasiswa.detail');
Route::get('/hitung-ipk/{ip1?}/{ip2?}', [CalculatorController::class, 'calculateIpk'])->name('calculator.ipk');

/* AGENTIC IDE ROUTES 
prefix() AWALAN SETELAH DOMAIN DAN SLASH BUAT GROUPING ROUTES
CLASS AgentIdeController, NAMA METHODNYA index()
name() NAMA FILE?? */
Route::prefix('ide')->name('ide.')->group(function () {
    Route::get('/', [AgentIdeController::class, 'index'])->name('index'); 

    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/tree', [FileController::class, 'tree'])->name('tree');
        Route::post('/file/read', [FileController::class, 'read'])->name('file.read');
        Route::post('/file/save', [FileController::class, 'save'])->name('file.save');
        Route::post('/file/create', [FileController::class, 'create'])->name('file.create');
        Route::post('/file/delete', [FileController::class, 'delete'])->name('file.delete');

        Route::get('/kernels', [KernelController::class, 'index'])->name('kernels');
        Route::post('/code/run', [KernelController::class, 'run'])->name('code.run');

        Route::post('/agent/prompt', [ChatController::class, 'prompt'])->name('agent.prompt');
    });
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

