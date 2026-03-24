<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    public const CATEGORIES = [
        'korean' => 'Korean',
        'chinese' => 'Chinese',
        'japanese' => 'Japanese',
        'western' => 'Western',
        'cafe' => 'Cafe',
        'other' => 'Other',
    ];

    protected $fillable = [
        'name',
        'address',
        'category',
        'rating',
        'visited_at',
        'memo',
        'is_revisit',
        'image_path',
        'latitude',
        'longitude',
        'geocode_status',
        'geocoded_at',
    ];

    protected function casts(): array
    {
        return [
            'visited_at' => 'date',
            'is_revisit' => 'boolean',
            'rating' => 'decimal:1',
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
                filled($filters['category'] ?? null) && array_key_exists($filters['category'], self::CATEGORIES),
                fn (Builder $query) => $query->where('category', $filters['category'])
            )
            ->when(filled($filters['search'] ?? null), function (Builder $query) use ($filters) {
                $search = trim($filters['search']);

                $query->where(function (Builder $query) use ($search) {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            });
    }

    public function scopeSort(Builder $query, ?string $sort): Builder
    {
        return match ($sort) {
            'rating_desc' => $query->orderByDesc('rating')->orderByDesc('visited_at'),
            'rating_asc' => $query->orderBy('rating')->orderByDesc('visited_at'),
            default => $query->orderByDesc('visited_at')->orderByDesc('created_at'),
        };
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }
}
