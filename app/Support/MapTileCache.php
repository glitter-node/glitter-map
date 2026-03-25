<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class MapTileCache
{
    public const DEFAULT_TTL_SECONDS = 45;

    public const MIN_INVALIDATION_ZOOM = 10;

    public const MAX_INVALIDATION_ZOOM = 18;

    public function tileKey(int $zoom, int $tileX, int $tileY): string
    {
        return "map:{$zoom}:{$tileX}:{$tileY}";
    }

    public function tileKeyFromPoint(float $latitude, float $longitude, int $zoom): string
    {
        [$tileX, $tileY] = $this->tileXYFromPoint($latitude, $longitude, $zoom);

        return $this->tileKey($zoom, $tileX, $tileY);
    }

    public function tileKeysForBounds(float $south, float $west, float $north, float $east, int $zoom): array
    {
        [$westTileX, $northTileY] = $this->tileXYFromPoint($north, $west, $zoom);
        [$eastTileX, $southTileY] = $this->tileXYFromPoint($south, $east, $zoom);

        $tileKeys = [];

        for ($tileX = min($westTileX, $eastTileX); $tileX <= max($westTileX, $eastTileX); $tileX++) {
            for ($tileY = min($northTileY, $southTileY); $tileY <= max($northTileY, $southTileY); $tileY++) {
                $tileKeys[] = [
                    'key' => $this->tileKey($zoom, $tileX, $tileY),
                    'x' => $tileX,
                    'y' => $tileY,
                    'bounds' => $this->tileBounds($zoom, $tileX, $tileY),
                ];
            }
        }

        return $tileKeys;
    }

    public function tileCacheKey(string $tileKey, string $variant = 'base'): string
    {
        $version = Cache::store(config('cache.default'))->get("{$tileKey}:version", 1);

        return "{$tileKey}:v{$version}:{$variant}";
    }

    public function invalidatePoint(?float $latitude, ?float $longitude): void
    {
        if (is_null($latitude) || is_null($longitude)) {
            return;
        }

        for ($zoom = self::MIN_INVALIDATION_ZOOM; $zoom <= self::MAX_INVALIDATION_ZOOM; $zoom++) {
            $tileKey = $this->tileKeyFromPoint($latitude, $longitude, $zoom);
            $store = Cache::store(config('cache.default'));
            $versionKey = "{$tileKey}:version";

            if (! $store->has($versionKey)) {
                $store->forever($versionKey, 2);
                continue;
            }

            $store->increment($versionKey);
        }
    }

    protected function tileXYFromPoint(float $latitude, float $longitude, int $zoom): array
    {
        $latitude = max(min($latitude, 85.05112878), -85.05112878);
        $longitude = max(min($longitude, 180), -180);
        $tileCount = 2 ** $zoom;
        $latitudeRad = deg2rad($latitude);

        $tileX = (int) floor((($longitude + 180) / 360) * $tileCount);
        $tileY = (int) floor(((1 - log(tan($latitudeRad) + (1 / cos($latitudeRad))) / pi()) / 2) * $tileCount);

        return [
            max(0, min($tileCount - 1, $tileX)),
            max(0, min($tileCount - 1, $tileY)),
        ];
    }

    protected function tileBounds(int $zoom, int $tileX, int $tileY): array
    {
        $tileCount = 2 ** $zoom;

        $west = ($tileX / $tileCount) * 360 - 180;
        $east = (($tileX + 1) / $tileCount) * 360 - 180;
        $north = rad2deg(atan(sinh(pi() * (1 - 2 * $tileY / $tileCount))));
        $south = rad2deg(atan(sinh(pi() * (1 - 2 * ($tileY + 1) / $tileCount))));

        return [
            'south' => $south,
            'west' => $west,
            'north' => $north,
            'east' => $east,
        ];
    }
}
