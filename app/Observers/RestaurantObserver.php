<?php

namespace App\Observers;

use App\Events\RestaurantCreated;
use App\Events\RestaurantDeleted;
use App\Events\RestaurantUpdated;
use App\Jobs\GeocodeRestaurant;
use App\Models\Restaurant;
use App\Support\MapTileCache;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Log;
use Throwable;

class RestaurantObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Restaurant $restaurant): void
    {
        $this->invalidateTiles(
            $restaurant->getOriginal('latitude'),
            $restaurant->getOriginal('longitude'),
            $restaurant->latitude,
            $restaurant->longitude,
        );

        $this->dispatchGeocodingIfNeeded($restaurant);
        $this->dispatchBroadcastSafely(fn () => new RestaurantCreated($restaurant->fresh()));
    }

    public function updated(Restaurant $restaurant): void
    {
        $this->invalidateTiles(
            $restaurant->getOriginal('latitude'),
            $restaurant->getOriginal('longitude'),
            $restaurant->latitude,
            $restaurant->longitude,
        );

        $this->dispatchGeocodingIfNeeded($restaurant);
        $this->dispatchBroadcastSafely(fn () => new RestaurantUpdated($restaurant->fresh()));
    }

    public function deleted(Restaurant $restaurant): void
    {
        $this->invalidateTiles(
            $restaurant->getOriginal('latitude'),
            $restaurant->getOriginal('longitude'),
            $restaurant->latitude,
            $restaurant->longitude,
        );

        $this->dispatchBroadcastSafely(fn () => new RestaurantDeleted($restaurant->id));
    }

    protected function dispatchGeocodingIfNeeded(Restaurant $restaurant): void
    {
        if ($restaurant->geocode_status !== 'pending' || blank($restaurant->address)) {
            return;
        }

        try {
            GeocodeRestaurant::dispatch($restaurant->id)->onQueue('geocoding');
        } catch (Throwable $exception) {
            Log::warning('Failed to queue restaurant geocoding job.', [
                'restaurant_id' => $restaurant->id,
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
            Log::warning('Failed to dispatch restaurant broadcast event.', [
                'event' => $event::class,
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
            Log::warning('Failed to invalidate restaurant tile cache.', [
                'original_latitude' => $originalLatitude,
                'original_longitude' => $originalLongitude,
                'current_latitude' => $currentLatitude,
                'current_longitude' => $currentLongitude,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
