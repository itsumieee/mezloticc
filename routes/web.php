<?php
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\OgImageController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WatchlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/health', [HealthController::class, 'check'])->name('health');
Route::post('/search', [UserController::class, 'search'])->middleware('throttle.roblox')->name('search');
Route::get('/history', [UserController::class, 'history'])->name('history');
Route::view('/api-docs', 'api-docs')->name('api.docs');
Route::get('/og/{userId}.svg', OgImageController::class)
    ->whereNumber('userId')
    ->middleware('throttle:30,1')
    ->name('og.image');
Route::get('/compare', [CompareController::class, 'form'])->name('compare.form');
Route::post('/compare', [CompareController::class, 'compare'])->middleware('throttle:5,1')->name('compare');
Route::get('/watchlist', [WatchlistController::class, 'index'])->name('watchlist.index');
Route::post('/watchlist', [WatchlistController::class, 'store'])->middleware('throttle:20,1')->name('watchlist.store');
Route::delete('/watchlist/{userId}', [WatchlistController::class, 'destroy'])->middleware('throttle:20,1')->name('watchlist.destroy');

Route::prefix('dashboard/{userId}')->group(function () {
    Route::get('/export/{format}', [ExportController::class, 'inventory'])
        ->whereIn('format', ['csv', 'json'])
        ->middleware('throttle:10,1')
        ->name('dashboard.export');
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