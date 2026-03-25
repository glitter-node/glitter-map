<x-layouts.app title="Restaurant Map">
    <section
        x-data="window.restaurantMapPage({
            mapApiUrl: @js($mapApiUrl),
            filters: @js([
                'category' => $filters['category'],
                'search' => $filters['search'],
                'sort' => 'latest',
            ]),
        })"
        x-init="init()"
        class="space-y-4"
    >
        <section class="panel space-y-3 p-3 sm:p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="eyebrow text-xs font-semibold uppercase tracking-[0.35em]">Map</p>
                <a href="{{ route('restaurants.create') }}" class="btn-primary">Add restaurant</a>
            </div>

            <form action="{{ route('restaurants.map') }}" method="GET" class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_220px]">
                <div>
                    <label for="map-search" class="label">Search</label>
                    <input
                        id="map-search"
                        name="search"
                        type="search"
                        class="input"
                        value="{{ $filters['search'] }}"
                        placeholder="Search name or address"
                        x-on:input.debounce.400ms="$el.form.requestSubmit()"
                    >
                </div>

                <div>
                    <label for="map-category" class="label">Category</label>
                    <select id="map-category" name="category" class="input" x-on:change="$el.form.requestSubmit()">
                        <option value="">All categories</option>
                        @foreach ($categories as $value => $label)
                            <option value="{{ $value }}" @selected($filters['category'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </form>

            @if ($hasActiveFilters)
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="text-muted text-xs font-semibold uppercase tracking-[0.2em]">Active filters</span>
                    @foreach ($activeFilters as $filter)
                        <span class="badge badge-accent">{{ $filter['label'] }}: {{ $filter['value'] }}</span>
                    @endforeach
                    <a href="{{ route('restaurants.map') }}" class="btn-secondary text-sm">Clear</a>
                </div>
            @endif
        </section>

        <section class="panel p-2 sm:p-3">
            <div class="relative">
                <div class="pointer-events-none absolute left-3 top-3 z-[500]">
                    <div class="panel px-3 py-2 text-xs uppercase tracking-[0.2em]">
                        <p class="text-muted">Visible markers only</p>
                        <p class="text-display mt-1 font-bold" x-show="mapLoading" x-cloak>Loading markers</p>
                        <p class="text-display mt-1 font-bold" x-show="!mapLoading" x-text="`${markers.length} markers loaded`"></p>
                    </div>
                </div>

                <div id="restaurants-index-map" class="map-frame h-[620px] overflow-hidden rounded-3xl"></div>
            </div>

            <template x-if="mapError">
                <p class="status-danger mt-4 px-4 py-3 text-sm" x-text="mapError"></p>
            </template>
        </section>
    </section>
</x-layouts.app>
