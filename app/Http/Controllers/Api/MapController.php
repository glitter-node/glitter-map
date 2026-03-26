<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Place;
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
        $variant = sha1(json_encode([
            'search' => $search,
        ]));
        $store = Cache::store(config('cache.default'));
        $places = collect();

        foreach ($tileCache->tileKeysForBounds($south, $west, $north, $east, $zoom) as $tile) {
            $cacheKey = $tileCache->tileCacheKey($tile['key'], $variant);

            $tileData = $store->remember($cacheKey, now()->addSeconds(MapTileCache::DEFAULT_TTL_SECONDS), function () use ($tile, $search) {
                return Place::query()
                    ->select(['id', 'name', 'latitude', 'longitude'])
                    ->hasLocation()
                    ->where('geocode_status', 'done')
                    ->when(filled($search), function ($query) use ($search) {
                        $query->where(function ($query) use ($search) {
                            $query
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('address', 'like', "%{$search}%")
                                ->orWhere('context', 'like', "%{$search}%")
                                ->orWhere('memory_note', 'like', "%{$search}%");
                        });
                    })
                    ->whereBetween('latitude', [$tile['bounds']['south'], $tile['bounds']['north']])
                    ->whereBetween('longitude', [$tile['bounds']['west'], $tile['bounds']['east']])
                    ->orderByDesc('experienced_at')
                    ->limit(500)
                    ->get()
                    ->map(fn (Place $place) => [
                        'id' => $place->id,
                        'name' => $place->name,
                        'latitude' => $place->latitude,
                        'longitude' => $place->longitude,
                    ])
                    ->values()
                    ->all();
            });

            $places = $places->merge($tileData);
        }

        return response()->json([
            'data' => $places
                ->filter(fn (array $place) => $place['latitude'] >= $south
                    && $place['latitude'] <= $north
                    && $place['longitude'] >= $west
                    && $place['longitude'] <= $east)
                ->unique('id')
                ->values(),
        ]);
    }

    public function nearby(Request $request): JsonResponse
    {
        $latitude = (float) $request->input('latitude');
        $longitude = (float) $request->input('longitude');
        $distance = min(max((float) $request->input('distance', 5), 0.5), 50);

        $places = Place::query()
            ->select(['id', 'name', 'address', 'context', 'impression', 'experienced_at', 'latitude', 'longitude'])
            ->hasLocation()
            ->where('geocode_status', 'done')
            ->nearby($latitude, $longitude, $distance)
            ->limit(10)
            ->get();

        return response()->json([
            'data' => $places->map(fn (Place $place) => [
                'id' => $place->id,
                'name' => $place->name,
                'address' => $place->address,
                'context' => $place->context,
                'impression' => number_format((float) $place->impression, 1),
                'experienced_at' => $place->experienced_at?->format('Y-m-d'),
                'distance_km' => round((float) $place->distance_km, 2),
                'show_url' => route('places.show', $place),
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
