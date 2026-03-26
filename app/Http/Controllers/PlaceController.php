<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlaceRequest;
use App\Http\Requests\UpdatePlaceRequest;
use App\Models\Place;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PlaceController extends Controller
{
    public function index(Request $request): View
    {
        return view('places.index', $this->buildListContext($request));
    }

    public function map(Request $request): View
    {
        return view('places.map', [
            ...$this->buildFilterContext($request, false),
            'mapApiUrl' => route('api.places.map'),
        ]);
    }

    public function insights(Request $request): View
    {
        $filters = $this->normalizedFilters($request);
        $baseQuery = Place::query()->filter($filters);

        $averageImpression = $baseQuery
            ->clone()
            ->selectRaw('COALESCE(AVG(impression), 0) as average_impression')
            ->value('average_impression');

        $latestExperience = $baseQuery
            ->clone()
            ->orderByDesc('experienced_at')
            ->orderByDesc('created_at')
            ->first(['name', 'experienced_at', 'context']);

        return view('places.insights', [
            ...$this->buildFilterContext($request, false),
            'placeCount' => $baseQuery->clone()->count(),
            'revisitCount' => $baseQuery->clone()->where('revisit_intention', true)->count(),
            'locationCount' => $baseQuery->clone()->hasLocation()->count(),
            'averageImpression' => number_format((float) $averageImpression, 1),
            'latestExperience' => $latestExperience,
        ]);
    }

    public function nearby(): View
    {
        return view('places.nearby', [
            'nearbyApiUrl' => route('api.places.nearby'),
        ]);
    }

    public function create(): View
    {
        return view('places.create', [
            'place' => new Place([
                'experienced_at' => now()->toDateString(),
                'impression' => '3.0',
                'revisit_intention' => false,
            ]),
        ]);
    }

    public function store(StorePlaceRequest $request): RedirectResponse
    {
        try {
            $place = DB::transaction(function () use ($request) {
                return Place::create($this->validatedData($request));
            });
        } catch (Throwable $exception) {
            Log::error('Failed to create place.', [
                'message' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Unable to save the place right now.');
        }

        return redirect()
            ->route('places.show', $place)
            ->with('success', 'Place entry created successfully.');
    }

    public function show(Place $place): View
    {
        return view('places.show', [
            'place' => $place,
        ]);
    }

    public function location(Place $place): JsonResponse
    {
        return response()->json([
            'id' => $place->id,
            'latitude' => $place->latitude,
            'longitude' => $place->longitude,
            'name' => $place->name,
        ]);
    }

    public function edit(Place $place): View
    {
        return view('places.edit', [
            'place' => $place,
        ]);
    }

    public function update(UpdatePlaceRequest $request, Place $place): RedirectResponse
    {
        $oldImagePath = $place->image_path;
        $newImagePath = null;

        try {
            DB::transaction(function () use ($request, $place, &$newImagePath) {
                $data = $this->validatedData($request, $place);
                $newImagePath = $data['image_path'] ?? null;

                $place->update($data);
            });
        } catch (Throwable $exception) {
            if ($newImagePath) {
                Storage::disk('public')->delete($newImagePath);
            }

            Log::error('Failed to update place.', [
                'place_id' => $place->id,
                'message' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Unable to update the place right now.');
        }

        if ($newImagePath && $oldImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }

        return redirect()
            ->route('places.show', $place)
            ->with('success', 'Place entry updated successfully.');
    }

    public function destroy(Place $place): RedirectResponse
    {
        try {
            DB::transaction(function () use ($place) {
                if ($place->image_path) {
                    Storage::disk('public')->delete($place->image_path);
                }

                $place->delete();
            });
        } catch (Throwable $exception) {
            Log::error('Failed to delete place.', [
                'place_id' => $place->id,
                'message' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('places.show', $place)
                ->with('error', 'Unable to delete the place right now.');
        }

        return redirect()
            ->route('places.index')
            ->with('success', 'Place entry deleted successfully.');
    }

    protected function validatedData(
        StorePlaceRequest|UpdatePlaceRequest $request,
        ?Place $place = null
    ): array
    {
        $data = $request->validated();
        $data['revisit_intention'] = $request->boolean('revisit_intention');
        $data['latitude'] = $request->filled('latitude') ? round((float) $request->input('latitude'), 7) : null;
        $data['longitude'] = $request->filled('longitude') ? round((float) $request->input('longitude'), 7) : null;
        $addressChanged = $place?->address !== ($data['address'] ?? null);

        if ($data['latitude'] !== null && $data['longitude'] !== null) {
            $data['geocode_status'] = 'done';
            $data['geocoded_at'] = now();
        } elseif ($addressChanged || ! $place || is_null($place->latitude) || is_null($place->longitude)) {
            $data['latitude'] = null;
            $data['longitude'] = null;
            $data['geocode_status'] = 'pending';
            $data['geocoded_at'] = null;
        }

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('places', 'public');
        }

        unset($data['image']);

        return $data;
    }

    protected function buildListContext(Request $request): array
    {
        $filterContext = $this->buildFilterContext($request);

        return [
            ...$filterContext,
            'places' => Place::query()
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

        if ($includeSort && $filters['sort'] !== 'latest') {
            $activeFilters[] = [
                'label' => 'Sort',
                'value' => match ($filters['sort']) {
                    'impression_desc' => 'Highest impression',
                    'impression_asc' => 'Lowest impression',
                    default => 'Latest',
                },
            ];
        }

        return [
            'filters' => $filters,
            'activeFilters' => $activeFilters,
            'hasActiveFilters' => filled($filters['search']) || ($includeSort && $filters['sort'] !== 'latest'),
        ];
    }

    protected function normalizedFilters(Request $request, bool $includeSort = true): array
    {
        return [
            'search' => $request->filled('search') ? trim($request->string('search')->toString()) : null,
            'sort' => $includeSort ? ($request->string('sort')->toString() ?: 'latest') : 'latest',
        ];
    }
}
