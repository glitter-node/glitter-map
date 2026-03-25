<x-layouts.app title="Restaurant List">
    <section class="space-y-4">
        <section class="panel space-y-4 p-3 sm:p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="eyebrow text-xs font-semibold uppercase tracking-[0.35em]">Restaurants</p>
                </div>
                <a href="{{ route('restaurants.create') }}" class="btn-primary">Add restaurant</a>
            </div>

            <form action="{{ route('restaurants.index') }}" method="GET" class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_220px]" x-data>
                <div>
                    <label for="search" class="label">Search</label>
                    <input
                        id="search"
                        name="search"
                        type="search"
                        class="input"
                        value="{{ $filters['search'] }}"
                        placeholder="Search name or address"
                        x-on:input.debounce.400ms="$el.form.requestSubmit()"
                    >
                </div>

                <div>
                    <label for="sort" class="label">Sort</label>
                    <select id="sort" name="sort" class="input" x-on:change="$el.form.requestSubmit()">
                        <option value="latest" @selected($filters['sort'] === 'latest')>Latest</option>
                        <option value="rating_desc" @selected($filters['sort'] === 'rating_desc')>Highest rating</option>
                        <option value="rating_asc" @selected($filters['sort'] === 'rating_asc')>Lowest rating</option>
                    </select>
                </div>

                @if ($filters['category'])
                    <input type="hidden" name="category" value="{{ $filters['category'] }}">
                @endif
            </form>

            <div>
                <p class="label">Category</p>
                <div class="flex flex-wrap gap-1.5">
                    <a href="{{ route('restaurants.index', array_filter(['search' => $filters['search'], 'sort' => $filters['sort'] !== 'latest' ? $filters['sort'] : null])) }}" class="{{ blank($filters['category']) ? 'btn-primary' : 'btn-secondary' }} text-sm">All</a>
                    @foreach ($categories as $value => $label)
                        <a
                            href="{{ route('restaurants.index', array_filter(['search' => $filters['search'], 'sort' => $filters['sort'] !== 'latest' ? $filters['sort'] : null, 'category' => $value])) }}"
                            class="{{ $filters['category'] === $value ? 'btn-primary' : 'btn-secondary' }} text-sm"
                        >{{ $label }}</a>
                    @endforeach
                </div>
            </div>

            @if ($hasActiveFilters)
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="text-muted text-xs font-semibold uppercase tracking-[0.2em]">Active filters</span>
                    @foreach ($activeFilters as $filter)
                        <span class="badge badge-accent">{{ $filter['label'] }}: {{ $filter['value'] }}</span>
                    @endforeach
                    <a href="{{ route('restaurants.index') }}" class="btn-secondary text-sm">Clear all</a>
                </div>
            @endif
        </section>

        @if ($restaurants->count())
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($restaurants as $restaurant)
                    <div class="space-y-2.5">
                        <x-restaurant-card :restaurant="$restaurant" :priority="$loop->first" />
                        @if ($restaurant->geocode_status === 'pending')
                            <p class="status-warning px-4 py-3 text-sm">위치 처리 중</p>
                        @elseif ($restaurant->geocode_status === 'failed')
                            <p class="status-danger px-4 py-3 text-sm">위치 처리 실패</p>
                        @endif
                    </div>
                @endforeach
            </section>

            <div>
                {{ $restaurants->links() }}
            </div>
        @else
            <section class="panel p-10 text-center">
                <p class="text-display text-lg font-semibold">No restaurants match this filter.</p>
                <p class="text-body mt-2 text-sm">Clear the active filters or add a new restaurant.</p>
            </section>
        @endif
    </section>
</x-layouts.app>
