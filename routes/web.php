<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\MagicLinkAuthController;
use App\Http\Controllers\RestaurantController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('restaurants.index');
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

Route::middleware('auth')->group(function () {
    Route::get('/restaurants/map', [RestaurantController::class, 'map'])
        ->name('restaurants.map');
    Route::get('/restaurants/{restaurant}/location', [RestaurantController::class, 'location'])
        ->name('restaurants.location');
    Route::get('/insights', [RestaurantController::class, 'insights'])
        ->name('restaurants.insights');
    Route::get('/nearby', [RestaurantController::class, 'nearby'])
        ->name('restaurants.nearby');
    Route::resource('restaurants', RestaurantController::class)
        ->missing(fn () => response()->view('errors.404', [], 404));
});
