<?php
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/search', [UserController::class, 'search'])->middleware('throttle.roblox')->name('search');
Route::get('/history', [UserController::class, 'history'])->name('history');

Route::prefix('dashboard/{userId}')->group(function () {
    Route::post('/refresh',     [UserController::class, 'refresh'])->name('dashboard.refresh');
    Route::get('/',            [UserController::class, 'overview'])->name('dashboard.overview');
    Route::get('/profile',     [UserController::class, 'profile'])->name('dashboard.profile');
    Route::get('/inventory',   [UserController::class, 'inventory'])->name('dashboard.inventory');
    Route::get('/limited',     [UserController::class, 'limited'])->name('dashboard.limited');
    Route::get('/animations',  [UserController::class, 'animations'])->name('dashboard.animations');
    Route::get('/avatar',      [UserController::class, 'avatar'])->name('dashboard.avatar');
    Route::get('/bundles',     [UserController::class, 'bundles'])->name('dashboard.bundles');
    Route::get('/statistics',  [UserController::class, 'statistics'])->name('dashboard.statistics');
    Route::get('/value',       [UserController::class, 'value'])->name('dashboard.value');
});