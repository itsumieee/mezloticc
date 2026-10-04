<?php

use App\Http\Controllers\Api\PublicApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:30,1')->group(function (): void {
    Route::get('/user/{identifier}', [PublicApiController::class, 'user'])
        ->where('identifier', '[A-Za-z0-9_]+');
    Route::get('/user/{userId}/limited', [PublicApiController::class, 'limited'])->whereNumber('userId');
    Route::get('/user/{userId}/inventory/{assetTypeId}', [PublicApiController::class, 'inventory'])
        ->whereNumber('userId')
        ->whereNumber('assetTypeId');
    Route::get('/user/{userId}/avatar', [PublicApiController::class, 'avatar'])->whereNumber('userId');
    Route::get('/user/{userId}/value', [PublicApiController::class, 'value'])->whereNumber('userId');
});