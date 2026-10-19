<?php

use App\Http\Controllers\AgentIdeController;
use App\Http\Controllers\Api\AgentIde\ChatController;
use App\Http\Controllers\Api\AgentIde\FileController;
use App\Http\Controllers\Api\AgentIde\InteractivePythonController;
use App\Http\Controllers\Api\AgentIde\KernelController;
use App\Http\Controllers\Api\AgentIde\TerminalController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->middleware('throttle:login');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'store'])->middleware('throttle:registration');
});

Route::middleware('auth')->group(function () {
    Route::get('/', [AgentIdeController::class, 'index'])->name('ide.index');
    Route::redirect('/ide', '/');
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::prefix('ide/api')->name('ide.api.')->group(function () {
        Route::get('/tree', [FileController::class, 'tree'])->name('tree');
        Route::post('/file/read', [FileController::class, 'read'])->name('file.read');
        Route::post('/file/save', [FileController::class, 'save'])->name('file.save');
        Route::post('/file/create', [FileController::class, 'create'])->name('file.create');
        Route::post('/file/delete', [FileController::class, 'delete'])->name('file.delete');
        Route::get('/kernels', [KernelController::class, 'index'])->name('kernels');
        Route::post('/code/run', [KernelController::class, 'run'])->middleware('throttle:execution')->name('code.run');
        Route::post('/code/python/start', [InteractivePythonController::class, 'start'])->middleware('throttle:execution')->name('python.start');
        Route::get('/code/python/status', [InteractivePythonController::class, 'status'])->name('python.status');
        Route::post('/code/python/input', [InteractivePythonController::class, 'input'])->name('python.input');
        Route::post('/code/python/stop', [InteractivePythonController::class, 'stop'])->name('python.stop');
        Route::post('/agent/prompt', [ChatController::class, 'prompt'])->name('agent.prompt');
        Route::post('/terminal/execute', [TerminalController::class, 'execute'])->middleware('throttle:execution')->name('terminal.execute');
    });
});
