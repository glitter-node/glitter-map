<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    protected $fillable = [
        'name',
        'address',
        'context',
        'impression',
        'experienced_at',
        'memory_note',
        'revisit_intention',
        'image_path',
        'latitude',
        'longitude',
        'geocode_status',
        'geocoded_at',
    ];

    protected function casts(): array
    {
        return [
            'experienced_at' => 'date',
            'revisit_intention' => 'boolean',
            'impression' => 'decimal:1',
            'latitude' => 'float',
            'longitude' => 'float',
            'geocoded_at' => 'datetime',
        ];
    }

    public function scopeHasLocation(Builder $query): Builder
    {
        return $query
            ->where('geocode_status', 'done')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');
    }

    public function scopeNearby(Builder $query, float $latitude, float $longitude, float $distanceKm = 5): Builder
    {
        $haversine = '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))))';

        return $query
            ->hasLocation()
            ->selectRaw("{$haversine} as distance_km", [$latitude, $longitude, $latitude])
            ->having('distance_km', '<=', $distanceKm)
            ->orderBy('distance_km');
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                filled($filters['search'] ?? null),
                function (Builder $query) use ($filters) {
                    $search = trim($filters['search']);

                    $query->where(function (Builder $query) use ($search) {
                        $query
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('address', 'like', "%{$search}%")
                            ->orWhere('context', 'like', "%{$search}%")
                            ->orWhere('memory_note', 'like', "%{$search}%");
                    });
                }
            )
        ;
    }

    public function scopeSort(Builder $query, ?string $sort): Builder
    {
        return match ($sort) {
            'impression_desc' => $query->orderByDesc('impression')->orderByDesc('experienced_at'),
            'impression_asc' => $query->orderBy('impression')->orderByDesc('experienced_at'),
            default => $query->orderByDesc('experienced_at')->orderByDesc('created_at'),
        };
    }

}
