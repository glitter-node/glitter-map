<?php

use App\Http\Controllers\Api\MapController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\MagicLinkAuthController;
use App\Http\Controllers\PlaceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('places.index');
    }

    return view('landing');
})->name('landing');

Route::get('/auth/login', [MagicLinkAuthController::class, 'showLoginForm'])
    ->name('login');

Route::post('/auth/link', [MagicLinkAuthController::class, 'sendLink'])
    ->name('auth.link');

Route::get('/auth/magic', [MagicLinkAuthController::class, 'consumeLink'])
    ->middleware('signed')
    ->name('auth.magic');

Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])
    ->name('auth.google.redirect');

Route::get('/auth/google/map-callback', [GoogleAuthController::class, 'callback'])
    ->name('auth.google.callback');

Route::post('/auth/google/one-tap', [GoogleAuthController::class, 'oneTap'])
    ->name('auth.google.one-tap');

Route::middleware(['auth', 'throttle:60,1'])->group(function () {
    Route::get('/api/places/map', [MapController::class, 'mapData'])
        ->name('api.places.map');

    Route::get('/api/places/nearby', [MapController::class, 'nearby'])
        ->name('api.places.nearby');
});

Route::middleware('auth')->group(function () {
    Route::get('/places/map', [PlaceController::class, 'map'])
        ->name('places.map');

    Route::get('/places/{place}/location', [PlaceController::class, 'location'])
        ->name('places.location');

    Route::get('/insights', [PlaceController::class, 'insights'])
        ->name('places.insights');

    Route::get('/nearby', [PlaceController::class, 'nearby'])
        ->name('places.nearby');

    Route::resource('places', PlaceController::class)
        ->missing(fn () => response()->view('errors.404', [], 404));
});
