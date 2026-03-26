@props([
    'place',
    'priority' => false,
])

<article class="panel overflow-hidden">
    <a href="{{ route('places.show', $place) }}" class="block">
        <div class="media-surface aspect-[4/3] overflow-hidden">
            @if ($place->image_path)
                <img
                    src="{{ asset('storage/' . $place->image_path) }}"
                    alt="{{ $place->name }}"
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
                    <h2 class="text-display truncate text-lg font-bold">{{ $place->name }}</h2>
                    <p class="text-body mt-1 text-sm">{{ $place->address }}</p>
                </div>
                @if ($place->revisit_intention)
                    <span class="badge badge-success">Return</span>
                @endif
            </div>

            <div class="text-body flex items-center gap-3 text-sm">
                <div class="impression-active flex items-center gap-1">
                    @php($filledStars = (int) round((float) $place->impression))
                    @for ($star = 1; $star <= 5; $star++)
                        <span class="{{ $star <= $filledStars ? 'impression-active' : 'impression-inactive' }}">★</span>
                    @endfor
                </div>
                <span class="font-semibold">{{ number_format((float) $place->impression, 1) }}</span>
            </div>

            <p class="text-body line-clamp-2 text-sm">{{ $place->context }}</p>

            <div class="text-body flex flex-col gap-2 text-sm sm:flex-row sm:items-center sm:justify-between sm:gap-0">
                <span>{{ optional($place->experienced_at)->format('M d, Y') ?? 'Not recorded' }}</span>
                <span>{{ ! is_null($place->latitude) && ! is_null($place->longitude) ? 'Mapped' : 'Unmapped' }}</span>
            </div>
        </div>
    </a>
</article>
