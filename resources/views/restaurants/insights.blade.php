<x-layouts.app title="Insights">
    @php
        $restaurantTotal = max((int) $restaurantCount, 0);
        $mappedPercentage = $restaurantTotal > 0 ? (int) round(((int) $locationCount / $restaurantTotal) * 100) : 0;
        $revisitPercentage = $restaurantTotal > 0 ? (int) round(((int) $revisitCount / $restaurantTotal) * 100) : 0;
        $topCategoryPercentage = $restaurantTotal > 0 ? (int) round(((int) $topCategoryCount / $restaurantTotal) * 100) : 0;
        $averageRatingValue = (float) $averageRating;
        $averageRatingSummary = match (true) {
            $restaurantTotal === 0 => 'No ratings yet.',
            $averageRatingValue >= 4.5 => 'Your visits are consistently excellent.',
            $averageRatingValue >= 4.0 => 'Most visits land firmly in favorite territory.',
            $averageRatingValue >= 3.5 => 'Mostly positive visits with a few mixed experiences.',
            $averageRatingValue >= 3.0 => 'Your dining history looks mixed overall.',
            default => 'Many visits feel below repeat-worthy.',
        };
        $revisitSummary = match (true) {
            $restaurantTotal === 0 => 'No revisit pattern yet.',
            $revisitCount === 0 => 'Nothing is marked as worth returning to yet.',
            $revisitPercentage >= 60 => 'You return to the same places often.',
            $revisitPercentage >= 30 => 'You have a healthy mix of new places and repeats.',
            default => 'You mostly explore new restaurants rather than revisiting.',
        };
        $mappingSummary = match (true) {
            $restaurantTotal === 0 => 'Map coverage will appear after you add places.',
            $mappedPercentage === 100 => 'Every saved place is ready to browse on the map.',
            $mappedPercentage >= 70 => 'Most saved places are mapped and ready for spatial browsing.',
            $mappedPercentage >= 40 => 'Map coverage is partial, with several places still missing coordinates.',
            default => 'Most saved places still need map coordinates.',
        };
        $topCategorySummary = match (true) {
            $restaurantTotal === 0 => 'Add restaurants to surface category trends.',
            $topCategoryCount === 0 => 'No category trend is visible yet.',
            default => "You mostly visit {$topCategoryLabel} restaurants.",
        };
    @endphp
    <section class="space-y-6">
        <section class="panel space-y-6 p-3 sm:p-5 sm:p-6">
            <div>
                <p class="eyebrow text-xs font-semibold uppercase tracking-[0.35em]">Insights</p>
                <h2 class="text-display mt-2 text-lg font-extrabold sm:text-2xl">Review the shape of your dining history</h2>
                <p class="text-body mt-2 text-sm">Analytics moved out of the main list to keep browsing focused.</p>
            </div>

            @if ($hasActiveFilters)
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-muted text-xs font-semibold uppercase tracking-[0.2em]">Scoped by</span>
                    @foreach ($activeFilters as $filter)
                        <span class="badge badge-accent">{{ $filter['label'] }}: {{ $filter['value'] }}</span>
                    @endforeach
                    <a href="{{ route('restaurants.insights') }}" class="btn-secondary">Clear</a>
                </div>
            @endif
        </section>

        <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            <article class="panel p-6">
                <p class="text-muted text-sm uppercase tracking-[0.2em]">Restaurants</p>
                <p class="text-display mt-4 text-3xl font-extrabold">{{ $restaurantCount }}</p>
                <p class="text-body mt-3 text-sm">{{ $mappingSummary }}</p>
            </article>
            <article class="panel p-6">
                <p class="text-muted text-sm uppercase tracking-[0.2em]">Average rating</p>
                <p class="rating-active mt-4 text-3xl font-extrabold">{{ $averageRating }}</p>
                <p class="text-body mt-3 text-sm">{{ $averageRatingSummary }}</p>
            </article>
            <article class="panel p-6">
                <p class="text-muted text-sm uppercase tracking-[0.2em]">Revisit picks</p>
                <p class="text-display mt-4 text-3xl font-extrabold">{{ $revisitCount }}</p>
                <p class="text-body mt-3 text-sm">{{ $revisitSummary }}</p>
            </article>
        </section>

        <section class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
            <div class="panel p-3 sm:p-5 sm:p-6">
                <p class="eyebrow text-xs font-semibold uppercase tracking-[0.35em]">Highlights</p>
                <div class="mt-6 space-y-4">
                    <div class="surface-subtle rounded-3xl p-4">
                        <p class="text-muted text-sm uppercase tracking-[0.2em]">Top pattern</p>
                        <p class="text-display mt-3 text-2xl font-bold">{{ $topCategorySummary }}</p>
                        <p class="text-body mt-1 text-sm">
                            @if ($restaurantTotal)
                                {{ $topCategoryLabel }} accounts for {{ $topCategoryPercentage }}% of your saved visits ({{ $topCategoryCount }} total).
                            @else
                                No category data yet.
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="panel p-3 sm:p-5 sm:p-6">
                <p class="eyebrow text-xs font-semibold uppercase tracking-[0.35em]">Category breakdown</p>
                @if ($categoryBreakdown->count())
                    <div class="mt-6 grid gap-3">
                        @foreach ($categoryBreakdown as $item)
                            <div class="surface-subtle flex items-center justify-between rounded-2xl px-4 py-3">
                                <div>
                                    <span class="text-display font-semibold">{{ $item['label'] }}</span>
                                    <p class="text-body mt-1 text-sm">
                                        {{ $restaurantTotal > 0 ? (int) round(($item['total'] / $restaurantTotal) * 100) : 0 }}% of your saved visits
                                    </p>
                                </div>
                                <span class="badge badge-accent">
                                    {{ $restaurantTotal > 0 ? (int) round(($item['total'] / $restaurantTotal) * 100) : 0 }}% ({{ $item['total'] }} visits)
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="mt-6 surface-subtle rounded-3xl p-8 text-center">
                        <p class="text-display text-lg font-semibold">No insight data yet.</p>
                        <p class="text-body mt-2 text-sm">Add restaurants first, then come back here for trends.</p>
                    </div>
                @endif
            </div>
        </section>
    </section>
</x-layouts.app>
