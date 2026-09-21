<?php

use App\Http\Controllers\CalculatorController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'index'])->name('home');
Route::get('/agent/{tema?}', [PageController::class, 'agent'])->name('agent.idea');
Route::get('/hitung/{a?}/{b?}/{operation?}', [CalculatorController::class, 'calculate'])->name('calculator');
Route::get('/', [PageController::class, 'index'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');

Route::get('/hitung-ipk/{ip1}/{ip2}', [CalculatorController::class, 'calculateIpk'])->name('calculator.ipk');

Route::prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/about', [PageController::class, 'about'])->name('about');
    Route::get('/mahasiswa/{nrp}', [PageController::class, 'mahasiswaDetail'])
        ->where('nrp', '[0-9]{10}')
        ->name('mahasiswa.detail');
});

Route::fallback(function () {
    return view('errors.404');
});
