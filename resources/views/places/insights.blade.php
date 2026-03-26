<x-layouts.app title="Memory Insights">
    @php
        $placeTotal = max((int) $placeCount, 0);
        $mappedPercentage = $placeTotal > 0 ? (int) round(((int) $locationCount / $placeTotal) * 100) : 0;
        $revisitPercentage = $placeTotal > 0 ? (int) round(((int) $revisitCount / $placeTotal) * 100) : 0;
        $averageImpressionValue = (float) $averageImpression;
    @endphp

    <section class="space-y-6">
        <section class="panel space-y-6 p-3 sm:p-5 sm:p-6">
            <div>
                <p class="eyebrow text-xs font-semibold uppercase tracking-[0.35em]">Insights</p>
                <h2 class="text-display mt-2 text-lg font-extrabold sm:text-2xl">Review the shape of your spatial memory log</h2>
                <p class="text-body mt-2 text-sm">This view emphasizes recall density, revisit intent, and map coverage.</p>
            </div>

            @if ($hasActiveFilters)
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-muted text-xs font-semibold uppercase tracking-[0.2em]">Scoped by</span>
                    @foreach ($activeFilters as $filter)
                        <span class="badge badge-accent">{{ $filter['label'] }}: {{ $filter['value'] }}</span>
                    @endforeach
                    <a href="{{ route('places.insights') }}" class="btn-secondary">Clear</a>
                </div>
            @endif
        </section>

        <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            <article class="panel p-6">
                <p class="text-muted text-sm uppercase tracking-[0.2em]">Places Logged</p>
                <p class="text-display mt-4 text-3xl font-extrabold">{{ $placeCount }}</p>
            </article>
            <article class="panel p-6">
                <p class="text-muted text-sm uppercase tracking-[0.2em]">Average Impression</p>
                <p class="impression-active mt-4 text-3xl font-extrabold">{{ $averageImpression }}</p>
                <p class="text-body mt-3 text-sm">
                    {{ $placeTotal === 0 ? 'No impressions recorded yet.' : ($averageImpressionValue >= 4 ? 'Your saved places tend to leave a strong mark.' : 'Your memory log mixes strong and subtle impressions.') }}
                </p>
            </article>
            <article class="panel p-6">
                <p class="text-muted text-sm uppercase tracking-[0.2em]">Mapped</p>
                <p class="text-display mt-4 text-3xl font-extrabold">{{ $locationCount }}</p>
                <p class="text-body mt-3 text-sm">{{ $mappedPercentage }}% of your saved places are spatially anchored.</p>
            </article>
            <article class="panel p-6">
                <p class="text-muted text-sm uppercase tracking-[0.2em]">Return Intentions</p>
                <p class="text-display mt-4 text-3xl font-extrabold">{{ $revisitCount }}</p>
                <p class="text-body mt-3 text-sm">{{ $revisitPercentage }}% of your saved places are marked for revisiting.</p>
            </article>
        </section>

        <section class="grid gap-6 lg:grid-cols-2">
            <article class="panel p-3 sm:p-5 sm:p-6">
                <p class="eyebrow text-xs font-semibold uppercase tracking-[0.35em]">Latest Memory</p>
                @if ($latestExperience)
                    <h3 class="text-display mt-3 text-2xl font-bold">{{ $latestExperience->name }}</h3>
                    <p class="text-body mt-2 text-sm">{{ optional($latestExperience->experienced_at)->format('F d, Y') ?? 'Not recorded' }}</p>
                    <p class="text-body mt-4 whitespace-pre-line text-sm leading-7">{{ $latestExperience->context }}</p>
                @else
                    <p class="text-body mt-3 text-sm">Add a place to begin building the timeline.</p>
                @endif
            </article>

            <article class="panel p-3 sm:p-5 sm:p-6">
                <p class="eyebrow text-xs font-semibold uppercase tracking-[0.35em]">Recall Guidance</p>
                <div class="mt-4 space-y-3 text-sm">
                    <div class="surface-subtle rounded-2xl p-4">
                        <p class="text-display font-semibold">Timeline first</p>
                        <p class="text-body mt-2">Use recent entries to recover sequence and context.</p>
                    </div>
                    <div class="surface-subtle rounded-2xl p-4">
                        <p class="text-display font-semibold">Map second</p>
                        <p class="text-body mt-2">Coordinates turn memories into navigable anchors.</p>
                    </div>
                    <div class="surface-subtle rounded-2xl p-4">
                        <p class="text-display font-semibold">Return intentionally</p>
                        <p class="text-body mt-2">Revisit flags distinguish enduring places from one-time moments.</p>
                    </div>
                </div>
            </article>
        </section>
    </section>
</x-layouts.app>
