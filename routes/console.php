<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Events\PlaceCreated;
use App\Events\PlaceCreatedNow;
use App\Events\PlaceDeleted;
use App\Models\Place;

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

Artisan::command('debug:broadcast-compare {placeId?}', function (?int $placeId = null) {
    $place = $placeId
        ? Place::query()->findOrFail($placeId)
        : Place::query()->latest('id')->firstOrFail();

    event(new PlaceCreated($place));
    event(new PlaceCreatedNow($place));

    $this->info('queued and sync broadcast events dispatched');
})->purpose('Dispatch queued and sync place broadcast events for comparison');

Artisan::command('debug:broadcast-delete {id}', function (int $id) {
    event(new PlaceDeleted($id));
    $this->info("place.deleted dispatched for id={$id}");
})->purpose('Dispatch a delete broadcast event for tracing');
