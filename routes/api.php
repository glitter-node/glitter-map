<?php

use App\Http\Controllers\Api\MapController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'throttle:60,1'])->group(function () {
    Route::get('/places/map', [MapController::class, 'mapData'])->name('api.places.map');
    Route::get('/places/nearby', [MapController::class, 'nearby'])->name('api.places.nearby');
});
