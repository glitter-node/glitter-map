<?php

namespace App\Observers;

use App\Events\PlaceCreated;
use App\Events\PlaceDeleted;
use App\Events\PlaceUpdated;
use App\Jobs\GeocodePlace;
use App\Models\Place;
use App\Support\MapTileCache;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Log;
use Throwable;

class PlaceObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Place $place): void
    {
        $this->invalidateTiles(
            $place->getOriginal('latitude'),
            $place->getOriginal('longitude'),
            $place->latitude,
            $place->longitude,
        );

        $this->dispatchGeocodingIfNeeded($place);
        $this->dispatchBroadcastSafely(fn () => new PlaceCreated($place->fresh()));
    }

    public function updated(Place $place): void
    {
        $this->invalidateTiles(
            $place->getOriginal('latitude'),
            $place->getOriginal('longitude'),
            $place->latitude,
            $place->longitude,
        );

        $this->dispatchGeocodingIfNeeded($place);
        $this->dispatchBroadcastSafely(fn () => new PlaceUpdated($place->fresh()));
    }

    public function deleted(Place $place): void
    {
        $this->invalidateTiles(
            $place->getOriginal('latitude'),
            $place->getOriginal('longitude'),
            $place->latitude,
            $place->longitude,
        );

        $this->dispatchBroadcastSafely(fn () => new PlaceDeleted($place->id));
    }

    protected function dispatchGeocodingIfNeeded(Place $place): void
    {
        if ($place->geocode_status !== 'pending' || blank($place->address)) {
            return;
        }

        try {
            GeocodePlace::dispatch($place->id)->onQueue('geocoding');
        } catch (Throwable $exception) {
            Log::warning('Failed to queue place geocoding job.', [
                'place_id' => $place->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    protected function dispatchBroadcastSafely(callable $resolver): void
    {
        try {
            $event = $resolver();
            event($event);
        } catch (Throwable $exception) {
            Log::warning('Failed to dispatch place broadcast event.', [
                'event' => isset($event) ? $event::class : 'unknown',
                'message' => $exception->getMessage(),
            ]);
        }
    }

    protected function invalidateTiles(
        ?float $originalLatitude,
        ?float $originalLongitude,
        ?float $currentLatitude,
        ?float $currentLongitude,
    ): void {
        try {
            $tileCache = app(MapTileCache::class);

            $tileCache->invalidatePoint($originalLatitude, $originalLongitude);
            $tileCache->invalidatePoint($currentLatitude, $currentLongitude);
        } catch (Throwable $exception) {
            Log::warning('Failed to invalidate place tile cache.', [
                'original_latitude' => $originalLatitude,
                'original_longitude' => $originalLongitude,
                'current_latitude' => $currentLatitude,
                'current_longitude' => $currentLongitude,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
