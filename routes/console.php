<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Events\RestaurantCreated;
use App\Events\RestaurantCreatedNow;
use App\Events\RestaurantDeleted;
use App\Models\Restaurant;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('debug:broadcast-config', function () {
    $this->line('broadcasting.default='.config('broadcasting.default'));
    $this->line('queue.default='.config('queue.default'));
    $this->line('database.redis.client='.config('database.redis.client'));
    $this->line('REVERB_HOST='.env('REVERB_HOST'));
    $this->line('REVERB_PORT='.env('REVERB_PORT'));
})->purpose('Print broadcast tracing environment values');

Artisan::command('debug:broadcast-compare {restaurantId?}', function (?int $restaurantId = null) {
    $restaurant = $restaurantId
        ? Restaurant::query()->findOrFail($restaurantId)
        : Restaurant::query()->latest('id')->firstOrFail();

    event(new RestaurantCreated($restaurant));
    event(new RestaurantCreatedNow($restaurant));

    $this->info('queued and sync broadcast events dispatched');
})->purpose('Dispatch queued and sync restaurant broadcast events for comparison');

Artisan::command('debug:broadcast-delete {id}', function (int $id) {
    event(new RestaurantDeleted($id));
    $this->info("restaurant.deleted dispatched for id={$id}");
})->purpose('Dispatch a delete broadcast event for tracing');
