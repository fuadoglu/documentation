<?php

use App\Http\Controllers\Admin\BrandingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\FolderController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\BrandingAssetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentAttachmentController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');
Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');
Route::get('/branding/logo', [BrandingAssetController::class, 'logo'])->name('branding.logo');
Route::get('/branding/favicon', [BrandingAssetController::class, 'favicon'])->name('branding.favicon');
Route::get('/branding/theme.css', [BrandingAssetController::class, 'theme'])->name('branding.theme');

Route::get('/build/{path}', function (string $path) {
    $decodedPath = urldecode($path);

    if (str_contains($decodedPath, '..')) {
        abort(404);
    }

    $buildRoot = realpath(public_path('build'));
    if ($buildRoot === false) {
        abort(404);
    }

    $targetPath = realpath($buildRoot.DIRECTORY_SEPARATOR.$decodedPath);
    if ($targetPath === false || ! is_file($targetPath)) {
        abort(404);
    }

    if (! str_starts_with($targetPath, $buildRoot.DIRECTORY_SEPARATOR)) {
        abort(404);
    }

    $extension = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
    $mimeType = match ($extension) {
        'css' => 'text/css; charset=UTF-8',
        'js', 'mjs' => 'application/javascript; charset=UTF-8',
        'json', 'map' => 'application/json; charset=UTF-8',
        'svg' => 'image/svg+xml',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'eot' => 'application/vnd.ms-fontobject',
        'png' => 'image/png',
        'jpg', 'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'avif' => 'image/avif',
        default => File::mimeType($targetPath) ?: 'application/octet-stream',
    };

    return response()->stream(function () use ($targetPath): void {
        $stream = fopen($targetPath, 'rb');
        if ($stream === false) {
            abort(404);
        }

        fpassthru($stream);
        fclose($stream);
    }, 200, [
        'Content-Type' => $mimeType,
        'Content-Length' => (string) filesize($targetPath),
        'Cache-Control' => 'public, max-age=31536000, immutable',
        'X-Content-Type-Options' => 'nosniff',
    ]);
})->where('path', '.*')->name('build.asset');

Route::middleware(['auth', 'active', 'no-store'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/documents', [DocumentController::class, 'index'])
        ->middleware('permission:documents.view')
        ->name('documents.index');
    Route::get('/documents/create', [DocumentController::class, 'create'])
        ->middleware('permission:documents.create')
        ->name('documents.create');
    Route::post('/documents', [DocumentController::class, 'store'])
        ->middleware('permission:documents.create')
        ->name('documents.store');
    Route::get('/documents/prefix-preview', [DocumentController::class, 'prefixPreview'])
        ->middleware('permission:documents.create')
        ->name('documents.prefix-preview');
    Route::get('/documents/{document}', [DocumentController::class, 'show'])
        ->middleware('permission:documents.view')
        ->name('documents.show');
    Route::get('/documents/{document}/edit', [DocumentController::class, 'edit'])
        ->middleware('permission:documents.view')
        ->name('documents.edit');
    Route::put('/documents/{document}', [DocumentController::class, 'update'])
        ->middleware('permission:documents.view')
        ->name('documents.update');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])
        ->middleware('permission:documents.view')
        ->name('documents.destroy');
    Route::post('/documents/{document}/versions', [DocumentAttachmentController::class, 'storeVersion'])
        ->middleware(['permission:documents.view', 'throttle:20,1'])
        ->name('documents.versions.store');
    Route::delete('/documents/{document}/versions/{attachment}', [DocumentAttachmentController::class, 'destroyVersion'])
        ->middleware(['permission:documents.view', 'throttle:20,1'])
        ->name('documents.versions.destroy');
    Route::get('/documents/{document}/attachments/{attachment}/download', [DocumentAttachmentController::class, 'download'])
        ->middleware(['permission:documents.download', 'throttle:60,1'])
        ->name('documents.attachments.download');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::patch('/categories/{category}/status', [CategoryController::class, 'status'])->name('categories.status');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/folders', [FolderController::class, 'index'])->name('folders.index');
        Route::post('/folders', [FolderController::class, 'store'])->name('folders.store');
        Route::put('/folders/{folder}', [FolderController::class, 'update'])->name('folders.update');
        Route::patch('/folders/{folder}/status', [FolderController::class, 'status'])->name('folders.status');
        Route::delete('/folders/{folder}', [FolderController::class, 'destroy'])->name('folders.destroy');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/status', [UserController::class, 'status'])->name('users.status');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::get('/branding', [BrandingController::class, 'edit'])->name('branding.edit');
        Route::put('/branding', [BrandingController::class, 'update'])->name('branding.update');

        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/audit-logs/export', [AuditLogController::class, 'export'])->name('audit-logs.export');
        Route::get('/permissions', [RolePermissionController::class, 'index'])->name('permissions.index');
        Route::put('/permissions/employee', [RolePermissionController::class, 'updateEmployee'])->name('permissions.employee.update');
    });
});

require __DIR__.'/auth.php';
