<?php

use App\Http\Controllers\Api\MapController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->group(function () {
    Route::get('/restaurants/map', [MapController::class, 'mapData'])->name('api.restaurants.map');
    Route::get('/restaurants/nearby', [MapController::class, 'nearby'])->name('api.restaurants.nearby');
});
