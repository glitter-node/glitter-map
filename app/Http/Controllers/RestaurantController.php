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
        $filters = $request->only(['category', 'search']);
        $sort = $request->string('sort')->toString();

        $restaurants = Restaurant::query()
            ->filter($filters)
            ->sort($sort)
            ->paginate(12)
            ->withQueryString();

        $averageRating = Restaurant::query()
            ->filter($filters)
            ->selectRaw('COALESCE(AVG(rating), 0) as average_rating')
            ->value('average_rating');

        $topCategory = Restaurant::query()
            ->filter($filters)
            ->select('category', DB::raw('COUNT(*) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->orderBy('category')
            ->first();

        return view('restaurants.index', [
            'restaurants' => $restaurants,
            'categories' => Restaurant::CATEGORIES,
            'filters' => [
                'category' => $filters['category'] ?? null,
                'search' => $filters['search'] ?? null,
                'sort' => $sort ?: 'latest',
            ],
            'averageRating' => number_format((float) $averageRating, 1),
            'topCategoryLabel' => $topCategory ? Restaurant::CATEGORIES[$topCategory->category] : 'No data',
            'topCategoryCount' => $topCategory?->total ?? 0,
            'mapApiUrl' => route('api.restaurants.map'),
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
        try {
            DB::transaction(function () use ($request, $restaurant) {
                $restaurant->update($this->validatedData($request, $restaurant));
            });
        } catch (Throwable $exception) {
            Log::error('Failed to update restaurant.', [
                'restaurant_id' => $restaurant->id,
                'message' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Unable to update the restaurant right now.');
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
            if ($restaurant?->image_path) {
                Storage::disk('public')->delete($restaurant->image_path);
            }

            $data['image_path'] = $request->file('image')->store('restaurants', 'public');
        }

        unset($data['image']);

        return $data;
    }
}
