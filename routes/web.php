<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\PreVerifiedRegistrationController;
use App\Http\Controllers\RestaurantController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('restaurants.index');
    }

    return view('landing');
})->name('landing');
Route::get('/auth/login', function () {
    if (auth()->check()) {
        return redirect('/restaurants');
    }

    return view('auth.login');
})->name('auth.login');

Route::get('/auth/email/request', [PreVerifiedRegistrationController::class, 'showEmailRequestForm'])
    ->name('auth.email.request');
Route::post('/auth/email/request', [PreVerifiedRegistrationController::class, 'sendVerificationEmail'])
    ->name('auth.email.request.send');
Route::get('/auth/email/verify/{token}', [PreVerifiedRegistrationController::class, 'verify'])
    ->name('auth.email.verify');
Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])
    ->name('auth.google.redirect');
Route::get('/auth/google/map-callback', [GoogleAuthController::class, 'callback'])
    ->name('auth.google.callback');
Route::post('/auth/google/one-tap', [GoogleAuthController::class, 'oneTap'])
    ->name('auth.google.one-tap');
Route::middleware('preverified')->group(function () {
    Route::get('/auth/register', [PreVerifiedRegistrationController::class, 'showRegistrationForm'])
        ->name('auth.register.show');
    Route::post('/auth/register', [PreVerifiedRegistrationController::class, 'register'])
        ->name('auth.register.store');
});

Route::resource('restaurants', RestaurantController::class)
    ->missing(fn () => response()->view('errors.404', [], 404));
