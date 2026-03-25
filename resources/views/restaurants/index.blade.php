<x-layouts.app title="Local Restaurant Diary">
    @push('head')
    @endpush

    <section
        x-data="window.restaurantMapPage({
            mapApiUrl: @js($mapApiUrl),
            nearbyApiUrl: @js($nearbyApiUrl),
            filters: @js([
                'category' => $filters['category'],
                'search' => $filters['search'],
                'sort' => $filters['sort'],
            ]),
        })"
        x-init="init()"
        class="flex flex-col gap-6"
    >
    <section class="order-3 grid gap-6 lg:order-1 lg:grid-cols-[1.2fr_0.8fr]">
        <div class="order-2 panel p-3 sm:p-5 sm:p-6 lg:order-1">
            <div class="flex flex-col gap-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="eyebrow text-xs font-semibold uppercase tracking-[0.35em]">Dashboard</p>
                        <h2 class="text-display mt-2 text-lg sm:text-2xl font-extrabold">Track every memorable meal</h2>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" class="btn-secondary toggle-chip" :class="mapMode === 'list' ? 'toggle-chip-active' : ''" @click="setMapMode('list')">Card list</button>
                        <button type="button" class="btn-secondary toggle-chip" :class="mapMode === 'map' ? 'toggle-chip-active' : ''" @click="setMapMode('map')">Map view</button>
                        <a href="{{ route('restaurants.create') }}" class="btn-primary">Add restaurant</a>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="surface-subtle rounded-3xl p-3 sm:p-5">
                        <p class="text-muted text-sm uppercase tracking-[0.2em]">Average rating</p>
                        <p class="rating-active mt-4 text-xl sm:text-3xl font-extrabold">{{ $averageRating }}</p>
                    </div>
                    <div class="surface-subtle hidden rounded-3xl p-3 sm:p-5 lg:block">
                        <p class="text-muted text-sm uppercase tracking-[0.2em]">Top category</p>
                        <p class="text-display mt-4 text-2xl font-bold">{{ $topCategoryLabel }}</p>
                        <p class="text-body mt-1 text-sm">{{ $topCategoryCount }} visits logged</p>
                    </div>
                    <div class="surface-subtle hidden rounded-3xl p-3 sm:p-5 sm:col-span-2 lg:block">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-muted text-sm uppercase tracking-[0.2em]">Nearby restaurants</p>
                                <p class="text-body mt-2 text-sm">Uses your current location and shows the 10 closest saved places.</p>
                            </div>
                            <button type="button" class="btn-secondary" @click="locateUser()" :disabled="nearbyLoading">
                                <span x-show="!nearbyLoading">Locate me</span>
                                <span x-show="nearbyLoading" x-cloak>Loading...</span>
                            </button>
                        </div>

                        <div class="mt-5 grid gap-3">
                            <template x-if="nearbyError">
                                <p class="status-danger px-4 py-3 text-sm" x-text="nearbyError"></p>
                            </template>

                            <template x-if="userLocation">
                                <p class="text-muted text-xs uppercase tracking-[0.2em]" x-text="`Current location: ${userLocation.latitude.toFixed(4)}, ${userLocation.longitude.toFixed(4)}`"></p>
                            </template>

                            <template x-for="restaurant in nearbyRestaurants" :key="restaurant.id">
                                <a :href="restaurant.show_url" class="surface-elevated rounded-2xl px-4 py-3 transition">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="min-w-0">
                                            <p class="text-display truncate font-semibold" x-text="restaurant.name"></p>
                                            <p class="text-body mt-1 truncate text-sm" x-text="restaurant.address"></p>
                                        </div>
                                        <div class="shrink-0 text-right">
                                            <p class="eyebrow text-sm font-semibold" x-text="`${restaurant.distance_km} km`"></p>
                                            <p class="text-muted mt-1 text-xs uppercase tracking-[0.2em]" x-text="restaurant.category"></p>
                                        </div>
                                    </div>
                                </a>
                            </template>

                            <template x-if="!nearbyLoading && !nearbyRestaurants.length && !nearbyError">
                                <p class="text-body text-sm">Allow location access to see the closest restaurants.</p>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-data="{ open: false }" class="order-1 lg:order-2">
            <button type="button" class="btn-secondary w-full lg:hidden" @click="open = !open">Filters</button>

            <form action="{{ route('restaurants.index') }}" method="GET" class="panel space-y-5 p-3 sm:p-5 sm:p-6" x-show="open || window.innerWidth >= 1024" x-cloak>
            <div>
                <label for="search" class="label">Search</label>
                <input id="search" name="search" type="search" class="input" value="{{ $filters['search'] }}" placeholder="Search name or address">
            </div>

            <div>
                <p class="label">Category</p>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('restaurants.index', request()->except('category', 'page')) }}" class="{{ blank($filters['category']) ? 'btn-primary' : 'btn-secondary' }}">All</a>
                    @foreach ($categories as $value => $label)
                        <button
                            type="submit"
                            name="category"
                            value="{{ $value }}"
                            class="{{ $filters['category'] === $value ? 'btn-primary' : 'btn-secondary' }}"
                        >{{ $label }}</button>
                    @endforeach
                </div>
            </div>

            <div>
                <label for="sort" class="label">Sort</label>
                <select id="sort" name="sort" class="input">
                    <option value="latest" @selected($filters['sort'] === 'latest')>Latest</option>
                    <option value="rating_desc" @selected($filters['sort'] === 'rating_desc')>Highest rating</option>
                    <option value="rating_asc" @selected($filters['sort'] === 'rating_asc')>Lowest rating</option>
                </select>
            </div>

            @if ($filters['category'])
                <input type="hidden" name="category" value="{{ $filters['category'] }}">
            @endif

            <div class="flex flex-col gap-3 sm:flex-row">
                <button type="submit" class="btn-primary flex-1">Apply filters</button>
                <a href="{{ route('restaurants.index') }}" class="btn-secondary flex-1">Reset</a>
            </div>
            </form>
        </div>
    </section>

    <section class="order-2 space-y-6 lg:order-2">
        <div
            x-show="mapMode === 'map'"
            x-cloak
            class="panel p-2 sm:p-4 sm:p-6"
        >
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <p class="eyebrow text-xs font-semibold uppercase tracking-[0.35em]">Viewport loading</p>
                    <h3 class="text-display mt-2 text-2xl font-bold">Visible markers only</h3>
                </div>
                <div class="text-muted text-right text-xs uppercase tracking-[0.2em]">
                    <p x-show="mapLoading" x-cloak>Loading markers</p>
                    <p x-show="!mapLoading" x-text="`${markers.length} markers loaded`"></p>
                </div>
            </div>

            <div id="restaurants-index-map" class="map-frame h-[420px] overflow-hidden rounded-3xl"></div>

            <template x-if="mapError">
                <p class="status-danger mt-4 px-4 py-3 text-sm" x-text="mapError"></p>
            </template>
        </div>

        <div x-show="mapMode === 'list'" x-cloak>
        @if ($restaurants->count())
            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($restaurants as $restaurant)
                    <div class="space-y-3">
                        <x-restaurant-card :restaurant="$restaurant" :priority="$loop->first" />
                        @if ($restaurant->geocode_status === 'pending')
                            <p class="status-warning px-4 py-3 text-sm">위치 처리 중</p>
                        @elseif ($restaurant->geocode_status === 'failed')
                            <p class="status-danger px-4 py-3 text-sm">위치 처리 실패</p>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $restaurants->links() }}
            </div>
        @else
            <div class="panel p-10 text-center">
                <p class="text-display text-lg font-semibold">No restaurants match this filter.</p>
                <p class="text-body mt-2 text-sm">Add your first spot or widen the search conditions.</p>
            </div>
        @endif
        </div>
    </section>
    </section>
</x-layouts.app>
