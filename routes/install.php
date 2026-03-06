<?php

use App\Http\Controllers\Install\SetupWizardController;
use App\Http\Middleware\BlockInstallerWhenInstalled;
use Illuminate\Support\Facades\Route;

Route::middleware([BlockInstallerWhenInstalled::class])->group(function (): void {
    Route::get('/install/install', fn () => redirect()->route('install.index'));
    Route::get('/install', [SetupWizardController::class, 'index'])->name('install.index');
    Route::post('/install', [SetupWizardController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('install.store');
});
