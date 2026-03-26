<?php

namespace App\Jobs;

use App\Models\Place;
use App\Services\PlaceGeocodingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class GeocodePlace implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 20;

    public function __construct(public int $placeId)
    {
        $this->onQueue('geocoding');
        $this->afterCommit();
    }

    public function handle(PlaceGeocodingService $geocodingService): void
    {
        $place = Place::query()->find($this->placeId);

        if (! $place) {
            return;
        }

        if (blank($place->address)) {
            $place->forceFill([
                'geocode_status' => 'failed',
                'geocoded_at' => null,
            ])->save();

            return;
        }

        if (! is_null($place->latitude) && ! is_null($place->longitude) && $place->geocode_status === 'done') {
            return;
        }

        $coordinates = $geocodingService->geocode($place->address);

        if (! $coordinates) {
            throw new \RuntimeException('Unable to geocode place address.');
        }

        $place->forceFill([
            'latitude' => $coordinates['latitude'],
            'longitude' => $coordinates['longitude'],
            'geocode_status' => 'done',
            'geocoded_at' => now(),
        ])->save();
    }

    public function failed(Throwable $exception): void
    {
        Place::query()
            ->whereKey($this->placeId)
            ->update([
                'geocode_status' => 'failed',
                'geocoded_at' => null,
            ]);

        Log::error('Place geocoding job failed.', [
            'place_id' => $this->placeId,
            'message' => $exception->getMessage(),
        ]);
    }
}
