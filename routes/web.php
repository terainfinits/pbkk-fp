<?php

use App\Http\Controllers\AgentIdeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\AgentIde\ChatController;
use App\Http\Controllers\Api\AgentIde\ChatSessionController;
use App\Http\Controllers\Api\AgentIde\FileController;
use App\Http\Controllers\Api\AgentIde\KernelController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route(Auth::check() ? 'ide.index' : 'login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('ide')->name('ide.')->group(function () {
        Route::get('/', [AgentIdeController::class, 'index'])->name('index');

        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/tree', [FileController::class, 'tree'])->name('tree');
            Route::post('/file/read', [FileController::class, 'read'])->name('file.read');
            Route::post('/file/save', [FileController::class, 'save'])->name('file.save');
            Route::post('/file/create', [FileController::class, 'create'])->name('file.create');
            Route::post('/file/delete', [FileController::class, 'delete'])->name('file.delete');
            Route::post('/file/rename', [FileController::class, 'rename'])->name('file.rename');
            Route::post('/file/upload', [FileController::class, 'upload'])->name('file.upload');

            Route::get('/kernels', [KernelController::class, 'index'])->name('kernels');
            Route::post('/code/run', [KernelController::class, 'run'])->name('code.run');
            Route::post('/agent/prompt', [ChatController::class, 'prompt'])->name('agent.prompt');

            Route::get('/chats', [ChatSessionController::class, 'index'])->name('chats.index');
            Route::post('/chats', [ChatSessionController::class, 'store'])->name('chats.store');
            Route::get('/chats/{id}', [ChatSessionController::class, 'show'])->name('chats.show');
            Route::get('/chats/{id}/export', [ChatSessionController::class, 'export'])->name('chats.export');
            Route::put('/chats/{id}', [ChatSessionController::class, 'update'])->name('chats.update');
            Route::delete('/chats/{id}', [ChatSessionController::class, 'destroy'])->name('chats.destroy');

            Route::post('/terminal/execute', [\App\Http\Controllers\Api\AgentIde\TerminalController::class, 'execute'])->name('terminal.execute');
        });
    });
});

Route::fallback(fn() => response()->view('errors.404', [], 404));
