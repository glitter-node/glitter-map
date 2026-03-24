<?php

namespace App\Services;

use App\Models\Restaurant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class RestaurantGeocodingService
{
    public function geocode(string $address): ?array
    {
        $normalizedAddress = trim($address);

        if ($normalizedAddress === '') {
            return null;
        }

        $cacheKey = 'geocode:'.sha1(mb_strtolower($normalizedAddress));

        $cachedCoordinates = Cache::get($cacheKey);

        if (is_array($cachedCoordinates) && isset($cachedCoordinates['latitude'], $cachedCoordinates['longitude'])) {
            return [
                'latitude' => round((float) $cachedCoordinates['latitude'], 7),
                'longitude' => round((float) $cachedCoordinates['longitude'], 7),
            ];
        }

        $cached = Restaurant::query()
            ->hasLocation()
            ->where('address', $normalizedAddress)
            ->first(['latitude', 'longitude']);

        if ($cached) {
            $coordinates = [
                'latitude' => round((float) $cached->latitude, 7),
                'longitude' => round((float) $cached->longitude, 7),
            ];

            Cache::put($cacheKey, $coordinates, now()->addDays(30));

            return $coordinates;
        }

        $response = Http::acceptJson()
            ->withHeaders([
                'User-Agent' => config('app.name', 'local-restaurant-diary').'/1.0 ('.config('app.url', 'http://localhost').')',
                'Referer' => config('app.url', 'http://localhost'),
            ])
            ->timeout(8)
            ->retry(2, 300)
            ->get('https://nominatim.openstreetmap.org/search', [
                'q' => $normalizedAddress,
                'format' => 'jsonv2',
                'limit' => 1,
                'addressdetails' => 0,
            ]);

        if (! $response->successful()) {
            return null;
        }

        $payload = $response->json();
        $first = is_array($payload) ? ($payload[0] ?? null) : null;

        if (! is_array($first) || ! isset($first['lat'], $first['lon'])) {
            return null;
        }

        $coordinates = [
            'latitude' => round((float) $first['lat'], 7),
            'longitude' => round((float) $first['lon'], 7),
        ];

        Cache::put($cacheKey, $coordinates, now()->addDays(30));

        return $coordinates;
    }
}
