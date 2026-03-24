<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Support\MapTileCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MapController extends Controller
{
    public function mapData(Request $request, MapTileCache $tileCache): JsonResponse
    {
        [$south, $west, $north, $east] = $this->parseBounds($request);
        $zoom = max(0, min((int) $request->input('zoom', 15), 18));
        $search = trim($request->string('search')->toString());
        $category = $request->string('category')->toString();
        $variant = sha1(json_encode([
            'category' => $category,
            'search' => $search,
        ]));
        $store = Cache::store(config('cache.default'));
        $restaurants = collect();

        foreach ($tileCache->tileKeysForBounds($south, $west, $north, $east, $zoom) as $tile) {
            $cacheKey = $tileCache->tileCacheKey($tile['key'], $variant);

            $tileData = $store->remember($cacheKey, now()->addSeconds(MapTileCache::DEFAULT_TTL_SECONDS), function () use ($tile, $category, $search) {
                return Restaurant::query()
                    ->select(['id', 'name', 'latitude', 'longitude'])
                    ->hasLocation()
                    ->where('geocode_status', 'done')
                    ->when(filled($category), fn ($query) => $query->where('category', $category))
                    ->when(filled($search), function ($query) use ($search) {
                        $query->where(function ($query) use ($search) {
                            $query
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('address', 'like', "%{$search}%");
                        });
                    })
                    ->whereBetween('latitude', [$tile['bounds']['south'], $tile['bounds']['north']])
                    ->whereBetween('longitude', [$tile['bounds']['west'], $tile['bounds']['east']])
                    ->orderByDesc('visited_at')
                    ->limit(500)
                    ->get()
                    ->map(fn (Restaurant $restaurant) => [
                        'id' => $restaurant->id,
                        'name' => $restaurant->name,
                        'latitude' => $restaurant->latitude,
                        'longitude' => $restaurant->longitude,
                    ])
                    ->values()
                    ->all();
            });

            $restaurants = $restaurants->merge($tileData);
        }

        return response()->json([
            'data' => $restaurants
                ->filter(fn (array $restaurant) => $restaurant['latitude'] >= $south
                    && $restaurant['latitude'] <= $north
                    && $restaurant['longitude'] >= $west
                    && $restaurant['longitude'] <= $east)
                ->unique('id')
                ->values(),
        ]);
    }

    public function nearby(Request $request): JsonResponse
    {
        $latitude = (float) $request->input('latitude');
        $longitude = (float) $request->input('longitude');
        $distance = min(max((float) $request->input('distance', 5), 0.5), 50);

        $restaurants = Restaurant::query()
            ->select(['id', 'name', 'address', 'category', 'rating', 'visited_at', 'latitude', 'longitude'])
            ->hasLocation()
            ->where('geocode_status', 'done')
            ->nearby($latitude, $longitude, $distance)
            ->limit(10)
            ->get();

        return response()->json([
            'data' => $restaurants->map(fn (Restaurant $restaurant) => [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'address' => $restaurant->address,
                'category' => $restaurant->category_label,
                'rating' => number_format((float) $restaurant->rating, 1),
                'visited_at' => $restaurant->visited_at?->format('Y-m-d'),
                'distance_km' => round((float) $restaurant->distance_km, 2),
                'show_url' => route('restaurants.show', $restaurant),
            ]),
        ]);
    }

    protected function parseBounds(Request $request): array
    {
        if ($request->filled('bounds')) {
            $parts = array_map('trim', explode(',', (string) $request->input('bounds')));

            if (count($parts) === 4) {
                return [
                    (float) $parts[0],
                    (float) $parts[1],
                    (float) $parts[2],
                    (float) $parts[3],
                ];
            }
        }

        return [
            (float) $request->input('south'),
            (float) $request->input('west'),
            (float) $request->input('north'),
            (float) $request->input('east'),
        ];
    }
}
