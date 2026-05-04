<?php

use App\Http\Controllers\PrinterController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Volt::route('/', 'welcome')->name('welcome');

Route::get('/terminal-logout', function () {
    auth()->logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect()->route('welcome');
})->name('terminal.logout');

Route::get('/terminal/{token}', \App\Http\Controllers\Terminal::class)
    ->name('terminal.init');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware('auth')->group(function () {
    Route::get('/qr', \App\Livewire\QrRedirect::class)->name('qr.redirect');

    // --- VP DETAIL (pro všechny přihlášené) ---
    Route::get('/vp/{id}', \App\Livewire\Zasobovac\Show::class)->name('vp.show')->where('id', '.*');

    // --- ZÁSOBOVAČ ---
    Route::middleware(['permission:manage zasobovani'])->group(function () {
        Route::get('/zasobovac', \App\Livewire\Zasobovac\Index::class)->name('zasobovac.index');
        Route::get('/zasobovac/{id}', \App\Livewire\Zasobovac\Show::class)->name('zasobovac.show')->where('id', '.*')->defaults('context', 'zasobovac');
    });

    // --- VEDOUCÍ ---
    Route::middleware(['permission:manage production records'])->prefix('vedouci')->group(function () {
        Route::get('/', \App\Livewire\Vedouci\Index::class)->name('vedouci.index');
        Route::get('/stroje', \App\Livewire\Vedouci\MachineIndex::class)->name('vedouci.machines');
        Route::get('/stroje/{machineKey}', \App\Livewire\Vedouci\MachineShow::class)->name('vedouci.machine');
        Route::get('/operator/{klicSubjektu}', \App\Livewire\Vedouci\Show::class)->name('vedouci.show');
        Route::get('/operator/{klicSubjektu}/edit/{id?}', \App\Livewire\Vedouci\RecordEdit::class)->name('vedouci.record-edit');
    });

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
