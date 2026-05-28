<?php

declare(strict_types=1);

use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\EditorImageController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\StoryController as AdminStoryController;
use App\Http\Middleware\EnsureAdminAuthenticated;
use Illuminate\Support\Facades\Route;

// ─── Public ─────────────────────────────────────────────────────────────────

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::prefix('gallery')->name('gallery.')->group(function () {
    Route::get('/', [GalleryController::class, 'index'])->name('index');
    Route::get('/{mediaItem}', [GalleryController::class, 'show'])->name('show');
});

Route::prefix('stories')->name('stories.')->group(function () {
    Route::get('/', [StoryController::class, 'index'])->name('index');
    Route::get('/{slug}', [StoryController::class, 'show'])->name('show');
});

// ─── Admin auth ──────────────────────────────────────────────────────────────

Route::prefix('admin')->name('admin.')->group(function () {

    Route::get('/login',  [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

});

// ─── Admin protected area ────────────────────────────────────────────────────

Route::prefix('admin')->name('admin.')->middleware([EnsureAdminAuthenticated::class])->group(function () {

    Route::get('/', fn () => redirect()->route('admin.media.index'));

    // Media uploads
    Route::resource('media', MediaController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::patch('media/{media}/feature', [MediaController::class, 'feature'])->name('media.feature');

    // Categories (inline creation + search)
    Route::get('categories',       [CategoryController::class, 'index'])->name('categories.index');
    Route::post('categories',      [CategoryController::class, 'store'])->name('categories.store');

    // Editor image upload (EasyMDE → R2)
    Route::post('editor/image', [EditorImageController::class, 'store'])->name('editor.image.store');

    // Stories
    Route::resource('stories', AdminStoryController::class)
        ->except(['show'])
        ->names('stories');
});

// ─── Auth (Breeze / Fortify stubs — swap in your preferred auth package) ─────
if (file_exists(__DIR__ . '/auth.php')) {
    require __DIR__ . '/auth.php';
}
