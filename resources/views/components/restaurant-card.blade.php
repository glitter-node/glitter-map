@props([
    'restaurant',
    'priority' => false,
])

<article class="panel overflow-hidden">
    <a href="{{ route('restaurants.show', $restaurant) }}" class="block">
        <div class="aspect-[4/3] overflow-hidden bg-stone-900">
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
                <div class="flex h-full items-center justify-center bg-[linear-gradient(135deg,_rgba(249,115,22,0.25),_rgba(28,25,23,0.8))]">
                    <span class="text-sm font-semibold uppercase tracking-[0.4em] text-orange-200/80">No Image</span>
                </div>
            @endif
        </div>

        <div class="space-y-4 p-3 sm:p-5">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between sm:gap-0">
                <div class="min-w-0">
                    <h2 class="truncate text-lg font-bold text-white">{{ $restaurant->name }}</h2>
                    <p class="mt-1 text-sm text-stone-400">{{ $restaurant->address }}</p>
                </div>
                <span class="badge bg-orange-400/15 text-orange-200">{{ $restaurant->category_label }}</span>
            </div>

            <div class="flex items-center gap-3 text-sm text-stone-300">
                <div class="flex items-center gap-1 text-amber-300">
                    @php($filledStars = (int) round((float) $restaurant->rating))
                    @for ($star = 1; $star <= 5; $star++)
                        <span class="{{ $star <= $filledStars ? 'text-amber-300' : 'text-stone-700' }}">★</span>
                    @endfor
                </div>
                <span class="font-semibold">{{ number_format((float) $restaurant->rating, 1) }}</span>
            </div>

            <div class="flex flex-col gap-2 text-sm text-stone-400 sm:flex-row sm:items-center sm:justify-between sm:gap-0">
                <span>{{ $restaurant->visited_at->format('M d, Y') }}</span>
                @if ($restaurant->is_revisit)
                    <span class="badge bg-emerald-400/15 text-emerald-200">Revisit</span>
                @endif
            </div>
        </div>
    </a>
</article>
