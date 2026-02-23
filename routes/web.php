<?php

use App\Http\Controllers\PolozkaController;
use App\Http\Controllers\SubjektController;
use App\Http\Controllers\DruhSubjektuController;
use App\Http\Controllers\ProstredekController;
use App\Http\Controllers\StaDoklController;
use App\Http\Controllers\PrinterController;
use App\Http\Controllers\StaPoController;
use App\Http\Controllers\DokladLabelController;

use App\Models\Polozka;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('/terminal/{token}', \App\Http\Controllers\Terminal::class)
    ->name('terminal.init');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware('auth')->group(function () {
    Route::resource('/polozka', PolozkaController::class)
        -> only(['index', 'store']);
    Route::get('/polozka/delete', [PolozkaController::class, 'deleteForm'])->name('polozka.delete-form');
    Route::delete('/polozka/delete', [PolozkaController::class, 'destroyById'])->name('polozka.destroy-by-id');
    Route::resource('/subjekt', SubjektController::class)
        -> only(['index']);
    Route::resource('/druh-subjektu', DruhSubjektuController::class)
        -> only(['index', 'create']);
    Route::get('/prostredky', [ProstredekController::class, 'index'])->name('prostredky.index');
    Route::get('/stadokl', [StaDoklController::class, 'index'])->name('stadokl.index');
    Route::get('/stadokl/{id}', [StaDoklController::class, 'show'])->name('stadokl.show')->where('id', '.*');
    Route::get('/stadokl-label', [DokladLabelController::class, 'show'])->name('stadokl.label');
    Route::get('/stapo', [StaPoController::class, 'index'])->name('stapo.index');
    Route::get('/qr-result/{code}', \App\Livewire\QrScannerResult::class)->name('qr.result');

    // --- ADMIN  ---
    Route::middleware(['role:Admin'])->prefix('admin')->group(function () {
        Route::get('/printers', [PrinterController::class, 'index'])->name('printers.index');
        Route::get('/areas', \App\Livewire\Admin\AreaIndex::class)->name('admin.areas');
        Route::get('/terminals', \App\Livewire\Admin\TerminalIndex::class)->name('admin.terminals');
    });
});

require __DIR__.'/auth.php';
