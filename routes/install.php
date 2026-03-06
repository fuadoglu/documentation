<?php

use App\Http\Controllers\Install\SetupWizardController;
use App\Http\Middleware\BlockInstallerWhenInstalled;
use Illuminate\Support\Facades\Route;

Route::middleware([BlockInstallerWhenInstalled::class])->group(function (): void {
    Route::redirect('/install/install', '/install', 302);
    Route::get('/install', [SetupWizardController::class, 'index'])->name('install.index');
    Route::post('/install', [SetupWizardController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('install.store');
});
