<?php

use App\Http\Controllers\DokladLabelController;
use App\Http\Controllers\DruhSubjektuController;
use App\Http\Controllers\PolozkaController;
use App\Http\Controllers\PrinterController;
use App\Http\Controllers\ProstredekController;
use App\Http\Controllers\StaDoklController;
use App\Http\Controllers\StaPoController;
use App\Http\Controllers\SubjektController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Volt::route('/', 'welcome')->name('welcome');

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
        ->only(['index', 'store']);
    Route::get('/polozka/delete', [PolozkaController::class, 'deleteForm'])->name('polozka.delete-form');
    Route::delete('/polozka/delete', [PolozkaController::class, 'destroyById'])->name('polozka.destroy-by-id');
    Route::resource('/subjekt', SubjektController::class)
        ->only(['index']);
    Route::resource('/druh-subjektu', DruhSubjektuController::class)
        ->only(['index', 'create']);
    Route::get('/prostredky', [ProstredekController::class, 'index'])->name('prostredky.index');
    Route::get('/stadokl', [StaDoklController::class, 'index'])->name('stadokl.index');
    Route::get('/stadokl/{id}', [StaDoklController::class, 'show'])->name('stadokl.show')->where('id', '.*');
    Route::get('/stadokl-label', [DokladLabelController::class, 'show'])->name('stadokl.label');
    Route::get('/stapo', [StaPoController::class, 'index'])->name('stapo.index');
    Route::get('/operace', \App\Livewire\Operace\Index::class)->name('operace.index');
    Route::get('/qr-result/{code}', \App\Livewire\QrScannerResult::class)->name('qr.result');

    // --- ADMIN  ---
    Route::middleware(['permission:manage terminals|manage printers|manage areas|manage users'])->prefix('admin')->group(function () {
        Route::get('/printers', [PrinterController::class, 'index'])->name('printers.index');
        Route::get('/areas', \App\Livewire\Admin\AreaIndex::class)->name('admin.areas');
        Route::get('/terminals', \App\Livewire\Admin\TerminalIndex::class)->name('admin.terminals');
        Route::get('/users', \App\Livewire\Admin\UserIndex::class)->name('admin.users');
        Route::get('/users/{user}/edit', \App\Livewire\Admin\UserEdit::class)->name('admin.users.edit');
        Route::get('/machines', \App\Livewire\Admin\MachineIndex::class)->name('admin.machines');
        Route::get('/machines/{machineKey}/edit', \App\Livewire\Admin\MachineEdit::class)->name('admin.machines.edit');
    });
});

require __DIR__.'/auth.php';
