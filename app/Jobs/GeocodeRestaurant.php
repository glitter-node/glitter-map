<?php

namespace App\Jobs;

use App\Models\Restaurant;
use App\Services\RestaurantGeocodingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class GeocodeRestaurant implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 20;

    public function __construct(public int $restaurantId)
    {
        $this->onQueue('geocoding');
        $this->afterCommit();
    }

    public function handle(RestaurantGeocodingService $geocodingService): void
    {
        $restaurant = Restaurant::query()->find($this->restaurantId);

        if (! $restaurant) {
            return;
        }

        if (blank($restaurant->address)) {
            $restaurant->forceFill([
                'geocode_status' => 'failed',
                'geocoded_at' => null,
            ])->save();

            return;
        }

        if (! is_null($restaurant->latitude) && ! is_null($restaurant->longitude) && $restaurant->geocode_status === 'done') {
            return;
        }

        $coordinates = $geocodingService->geocode($restaurant->address);

        if (! $coordinates) {
            throw new \RuntimeException('Unable to geocode restaurant address.');
        }

        $restaurant->forceFill([
            'latitude' => $coordinates['latitude'],
            'longitude' => $coordinates['longitude'],
            'geocode_status' => 'done',
            'geocoded_at' => now(),
        ])->save();
    }

    public function failed(Throwable $exception): void
    {
        Restaurant::query()
            ->whereKey($this->restaurantId)
            ->update([
                'geocode_status' => 'failed',
                'geocoded_at' => null,
            ]);

        Log::error('Restaurant geocoding job failed.', [
            'restaurant_id' => $this->restaurantId,
            'message' => $exception->getMessage(),
        ]);
    }
}
