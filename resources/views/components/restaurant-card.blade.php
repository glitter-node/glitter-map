@props([
    'restaurant',
    'priority' => false,
])

<article class="panel overflow-hidden">
    <a href="{{ route('restaurants.show', $restaurant) }}" class="block">
        <div class="media-surface aspect-[4/3] overflow-hidden">
            @if ($restaurant->image_path)
                <img
                    src="{{ asset('storage/' . $restaurant->image_path) }}"
                    alt="{{ $restaurant->name }}"
                    loading="{{ $priority ? 'eager' : 'lazy' }}"
                    fetchpriority="{{ $priority ? 'high' : 'auto' }}"
                    width="400"
                    height="300"
                    class="h-full w-full object-cover transition duration-500 hover:scale-105"
                >
            @else
                <div class="image-fallback flex h-full items-center justify-center">
                    <span class="eyebrow text-sm font-semibold uppercase tracking-[0.4em]">No Image</span>
                </div>
            @endif
        </div>

        <div class="space-y-4 p-3 sm:p-5">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between sm:gap-0">
                <div class="min-w-0">
                    <h2 class="text-display truncate text-lg font-bold">{{ $restaurant->name }}</h2>
                    <p class="text-body mt-1 text-sm">{{ $restaurant->address }}</p>
                </div>
                <span class="badge badge-accent">{{ $restaurant->category_label }}</span>
            </div>

            <div class="text-body flex items-center gap-3 text-sm">
                <div class="rating-active flex items-center gap-1">
                    @php($filledStars = (int) round((float) $restaurant->rating))
                    @for ($star = 1; $star <= 5; $star++)
                        <span class="{{ $star <= $filledStars ? 'rating-active' : 'rating-inactive' }}">★</span>
                    @endfor
                </div>
                <span class="font-semibold">{{ number_format((float) $restaurant->rating, 1) }}</span>
            </div>

            <div class="text-body flex flex-col gap-2 text-sm sm:flex-row sm:items-center sm:justify-between sm:gap-0">
                <span>{{ optional($restaurant->visited_at)->format('M d, Y') ?? 'Not recorded' }}</span>
                @if ($restaurant->is_revisit)
                    <span class="badge badge-success">Revisit</span>
                @endif
            </div>
        </div>
    </a>
</article>
