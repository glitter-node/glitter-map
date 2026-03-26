<x-layouts.app title="Spatial Memory Log">
    <section class="space-y-4">
        <section class="panel space-y-4 p-3 sm:p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="eyebrow text-xs font-semibold uppercase tracking-[0.35em]">Timeline</p>
                </div>
                <a href="{{ route('places.create') }}" class="btn-primary">Add memory</a>
            </div>

            <form action="{{ route('places.index') }}" method="GET" class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_220px]" x-data>
                <div>
                    <label for="search" class="label">Search</label>
                    <input
                        id="search"
                        name="search"
                        type="search"
                        class="input"
                        value="{{ $filters['search'] }}"
                        placeholder="Search place, area, context, or memory note"
                        x-on:input.debounce.400ms="$el.form.requestSubmit()"
                    >
                </div>

                <div>
                    <label for="sort" class="label">Sort</label>
                    <select id="sort" name="sort" class="input" x-on:change="$el.form.requestSubmit()">
                        <option value="latest" @selected($filters['sort'] === 'latest')>Latest</option>
                        <option value="impression_desc" @selected($filters['sort'] === 'impression_desc')>Strongest impression</option>
                        <option value="impression_asc" @selected($filters['sort'] === 'impression_asc')>Lightest impression</option>
                    </select>
                </div>
            </form>

            @if ($hasActiveFilters)
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="text-muted text-xs font-semibold uppercase tracking-[0.2em]">Active filters</span>
                    @foreach ($activeFilters as $filter)
                        <span class="badge badge-accent">{{ $filter['label'] }}: {{ $filter['value'] }}</span>
                    @endforeach
                    <a href="{{ route('places.index') }}" class="btn-secondary text-sm">Clear all</a>
                </div>
            @endif
        </section>

        @if ($places->count())
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($places as $place)
                    <div class="space-y-2.5">
                        <x-place-card :place="$place" :priority="$loop->first" />
                        @if ($place->geocode_status === 'pending')
                            <p class="status-warning px-4 py-3 text-sm">Coordinates are being resolved.</p>
                        @elseif ($place->geocode_status === 'failed')
                            <p class="status-danger px-4 py-3 text-sm">Coordinates could not be resolved.</p>
                        @endif
                    </div>
                @endforeach
            </section>

            <div>
                {{ $places->links() }}
            </div>
        @else
            <section class="panel p-10 text-center">
                <p class="text-display text-lg font-semibold">No spatial memories match this filter.</p>
                <p class="text-body mt-2 text-sm">Clear the active filters or add a new memory.</p>
            </section>
        @endif
    </section>
</x-layouts.app>
