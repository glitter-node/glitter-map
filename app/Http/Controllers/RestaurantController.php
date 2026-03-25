<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRestaurantRequest;
use App\Http\Requests\UpdateRestaurantRequest;
use App\Models\Restaurant;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class RestaurantController extends Controller
{
    public function index(Request $request): View
    {
        return view('restaurants.index', $this->buildListContext($request));
    }

    public function map(Request $request): View
    {
        return view('restaurants.map', [
            ...$this->buildFilterContext($request, false),
            'mapApiUrl' => route('api.restaurants.map'),
        ]);
    }

    public function insights(Request $request): View
    {
        $filters = $this->normalizedFilters($request);
        $baseQuery = Restaurant::query()->filter($filters);

        $averageRating = $baseQuery
            ->clone()
            ->selectRaw('COALESCE(AVG(rating), 0) as average_rating')
            ->value('average_rating');

        $topCategory = $baseQuery
            ->clone()
            ->select('category', DB::raw('COUNT(*) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->orderBy('category')
            ->first();

        $categoryBreakdown = $baseQuery
            ->clone()
            ->select('category', DB::raw('COUNT(*) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->orderBy('category')
            ->get()
            ->map(fn ($row) => [
                'label' => Restaurant::CATEGORIES[$row->category] ?? ucfirst($row->category),
                'total' => $row->total,
            ]);

        return view('restaurants.insights', [
            ...$this->buildFilterContext($request, false),
            'restaurantCount' => $baseQuery->clone()->count(),
            'revisitCount' => $baseQuery->clone()->where('is_revisit', true)->count(),
            'locationCount' => $baseQuery->clone()->hasLocation()->count(),
            'averageRating' => number_format((float) $averageRating, 1),
            'topCategoryLabel' => $topCategory ? Restaurant::CATEGORIES[$topCategory->category] : 'No data',
            'topCategoryCount' => $topCategory?->total ?? 0,
            'categoryBreakdown' => $categoryBreakdown,
        ]);
    }

    public function nearby(): View
    {
        return view('restaurants.nearby', [
            'nearbyApiUrl' => route('api.restaurants.nearby'),
        ]);
    }

    public function create(): View
    {
        return view('restaurants.create', [
            'restaurant' => new Restaurant([
                'visited_at' => now()->toDateString(),
                'rating' => '3.0',
                'is_revisit' => false,
            ]),
            'categories' => Restaurant::CATEGORIES,
        ]);
    }

    public function store(StoreRestaurantRequest $request): RedirectResponse
    {
        try {
            $restaurant = DB::transaction(function () use ($request) {
                return Restaurant::create($this->validatedData($request));
            });
        } catch (Throwable $exception) {
            Log::error('Failed to create restaurant.', [
                'message' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Unable to save the restaurant right now.');
        }

        return redirect()
            ->route('restaurants.show', $restaurant)
            ->with('success', 'Restaurant entry created successfully.');
    }

    public function show(Restaurant $restaurant): View
    {
        return view('restaurants.show', [
            'restaurant' => $restaurant,
        ]);
    }

    public function edit(Restaurant $restaurant): View
    {
        return view('restaurants.edit', [
            'restaurant' => $restaurant,
            'categories' => Restaurant::CATEGORIES,
        ]);
    }

    public function update(UpdateRestaurantRequest $request, Restaurant $restaurant): RedirectResponse
    {
        $oldImagePath = $restaurant->image_path;
        $newImagePath = null;

        try {
            DB::transaction(function () use ($request, $restaurant, &$newImagePath) {
                $data = $this->validatedData($request, $restaurant);
                $newImagePath = $data['image_path'] ?? null;

                $restaurant->update($data);
            });
        } catch (Throwable $exception) {
            if ($newImagePath) {
                Storage::disk('public')->delete($newImagePath);
            }

            Log::error('Failed to update restaurant.', [
                'restaurant_id' => $restaurant->id,
                'message' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Unable to update the restaurant right now.');
        }

        if ($newImagePath && $oldImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }

        return redirect()
            ->route('restaurants.show', $restaurant)
            ->with('success', 'Restaurant entry updated successfully.');
    }

    public function destroy(Restaurant $restaurant): RedirectResponse
    {
        try {
            DB::transaction(function () use ($restaurant) {
                if ($restaurant->image_path) {
                    Storage::disk('public')->delete($restaurant->image_path);
                }

                $restaurant->delete();
            });
        } catch (Throwable $exception) {
            Log::error('Failed to delete restaurant.', [
                'restaurant_id' => $restaurant->id,
                'message' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('restaurants.show', $restaurant)
                ->with('error', 'Unable to delete the restaurant right now.');
        }

        return redirect()
            ->route('restaurants.index')
            ->with('success', 'Restaurant entry deleted successfully.');
    }

    protected function validatedData(
        StoreRestaurantRequest|UpdateRestaurantRequest $request,
        ?Restaurant $restaurant = null
    ): array
    {
        $data = $request->validated();
        $data['is_revisit'] = $request->boolean('is_revisit');
        $data['latitude'] = $request->filled('latitude') ? round((float) $request->input('latitude'), 7) : null;
        $data['longitude'] = $request->filled('longitude') ? round((float) $request->input('longitude'), 7) : null;
        $addressChanged = $restaurant?->address !== ($data['address'] ?? null);

        if ($data['latitude'] !== null && $data['longitude'] !== null) {
            $data['geocode_status'] = 'done';
            $data['geocoded_at'] = now();
        } elseif ($addressChanged || ! $restaurant || is_null($restaurant->latitude) || is_null($restaurant->longitude)) {
            $data['latitude'] = null;
            $data['longitude'] = null;
            $data['geocode_status'] = 'pending';
            $data['geocoded_at'] = null;
        }

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('restaurants', 'public');
        }

        unset($data['image']);

        return $data;
    }

    protected function buildListContext(Request $request): array
    {
        $filterContext = $this->buildFilterContext($request);

        return [
            ...$filterContext,
            'restaurants' => Restaurant::query()
                ->filter($filterContext['filters'])
                ->sort($filterContext['filters']['sort'])
                ->paginate(12)
                ->withQueryString(),
        ];
    }

    protected function buildFilterContext(Request $request, bool $includeSort = true): array
    {
        $filters = $this->normalizedFilters($request, $includeSort);
        $activeFilters = [];

        if ($filters['search']) {
            $activeFilters[] = [
                'label' => 'Search',
                'value' => $filters['search'],
            ];
        }

        if ($filters['category']) {
            $activeFilters[] = [
                'label' => 'Category',
                'value' => Restaurant::CATEGORIES[$filters['category']] ?? ucfirst($filters['category']),
            ];
        }

        if ($includeSort && $filters['sort'] !== 'latest') {
            $activeFilters[] = [
                'label' => 'Sort',
                'value' => match ($filters['sort']) {
                    'rating_desc' => 'Highest rating',
                    'rating_asc' => 'Lowest rating',
                    default => 'Latest',
                },
            ];
        }

        return [
            'categories' => Restaurant::CATEGORIES,
            'filters' => $filters,
            'activeFilters' => $activeFilters,
            'hasActiveFilters' => filled($filters['search']) || filled($filters['category']) || ($includeSort && $filters['sort'] !== 'latest'),
        ];
    }

    protected function normalizedFilters(Request $request, bool $includeSort = true): array
    {
        return [
            'category' => $request->filled('category') ? $request->string('category')->toString() : null,
            'search' => $request->filled('search') ? trim($request->string('search')->toString()) : null,
            'sort' => $includeSort ? ($request->string('sort')->toString() ?: 'latest') : 'latest',
        ];
    }
}
