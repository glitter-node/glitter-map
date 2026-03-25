@php
    $showMapConfig = [
        'marker' => [
            'latitude' => $restaurant->latitude,
            'longitude' => $restaurant->longitude,
            'label' => $restaurant->name,
        ],
        'zoom' => 16,
    ];
@endphp

<x-layouts.app :title="$restaurant->name">
    <section class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <div class="panel overflow-hidden">
            <div class="media-surface aspect-[4/3]">
                @if ($restaurant->image_path)
                    <img src="{{ asset('storage/' . $restaurant->image_path) }}" alt="{{ $restaurant->name }}" loading="eager" fetchpriority="high" width="400" height="300" class="h-full w-full object-cover">
                @else
                    <div class="image-fallback flex h-full items-center justify-center">
                        <span class="eyebrow text-sm font-semibold uppercase tracking-[0.4em]">No Image</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="panel p-3 sm:p-5 sm:p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:justify-between">
                    <div>
                        <span class="badge badge-accent">{{ $restaurant->category_label }}</span>
                        <h2 class="text-display mt-4 text-lg sm:text-2xl font-extrabold">{{ $restaurant->name }}</h2>
                        <p class="text-body mt-2">{{ $restaurant->address }}</p>
                    </div>
                    @if ($restaurant->is_revisit)
                        <span class="badge badge-success">Revisit</span>
                    @endif
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div class="surface-subtle rounded-2xl p-2 sm:p-4">
                        <p class="text-muted text-sm uppercase tracking-[0.2em]">Rating</p>
                        <p class="rating-active mt-3 text-lg sm:text-2xl font-bold">{{ number_format((float) $restaurant->rating, 1) }}</p>
                    </div>
                    <div class="surface-subtle rounded-2xl p-2 sm:p-4">
                        <p class="text-muted text-sm uppercase tracking-[0.2em]">Visited</p>
                        <p class="text-display mt-3 text-xl font-bold">{{ optional($restaurant->visited_at)->format('F d, Y') ?? 'Not recorded' }}</p>
                    </div>
                </div>

                <div class="mt-6">
                    <p class="text-muted text-sm uppercase tracking-[0.2em]">Memo</p>
                    <p class="text-body mt-3 whitespace-pre-line text-sm leading-7">{{ $restaurant->memo ?: 'No notes recorded for this visit.' }}</p>
                </div>

                <div class="mt-6">
                    <p class="text-muted text-sm uppercase tracking-[0.2em]">Coordinates</p>
                    <p class="text-body mt-3 text-sm">
                        @if (! is_null($restaurant->latitude) && ! is_null($restaurant->longitude))
                            {{ number_format($restaurant->latitude, 7) }}, {{ number_format($restaurant->longitude, 7) }}
                        @elseif ($restaurant->geocode_status === 'pending')
                            위치 처리 중
                        @elseif ($restaurant->geocode_status === 'failed')
                            위치 처리 실패
                        @else
                            Location not selected.
                        @endif
                    </p>
                </div>
            </div>

            <div class="panel p-3 sm:p-5 sm:p-6">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="eyebrow text-xs font-semibold uppercase tracking-[0.35em]">Map</p>
                        <h3 class="text-display mt-2 text-2xl font-bold">Restaurant location</h3>
                    </div>
                </div>

                @if (! is_null($restaurant->latitude) && ! is_null($restaurant->longitude))
                    <div
                        id="restaurant-show-map"
                        class="map-frame mt-6 h-80 overflow-hidden rounded-3xl"
                        data-map-config='@json($showMapConfig)'
                    ></div>
                @elseif ($restaurant->geocode_status === 'pending')
                    <div class="status-warning mt-6 rounded-3xl border-dashed p-10 text-center">
                        <p class="text-lg font-semibold">위치 처리 중</p>
                        <p class="mt-2 text-sm">좌표가 확인되는 즉시 지도에 자동 반영됩니다.</p>
                    </div>
                @elseif ($restaurant->geocode_status === 'failed')
                    <div class="status-danger mt-6 rounded-3xl border-dashed p-10 text-center">
                        <p class="text-lg font-semibold">위치 처리 실패</p>
                        <p class="mt-2 text-sm">관리자가 재시도하거나 수정 화면에서 좌표를 직접 지정할 수 있습니다.</p>
                    </div>
                @else
                    <div class="surface-subtle mt-6 rounded-3xl border-dashed p-10 text-center">
                        <p class="text-display text-lg font-semibold">No map location saved.</p>
                        <p class="text-body mt-2 text-sm">Edit this restaurant to choose a position on the map.</p>
                    </div>
                @endif
            </div>

            <div class="panel p-3 sm:p-5 sm:p-6" x-data="deleteModal()">
                <div class="flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('restaurants.edit', $restaurant) }}" class="btn-primary flex-1">Edit</a>
                    <a href="{{ route('restaurants.index') }}" class="btn-secondary flex-1">Back to list</a>
                    <button type="button" class="btn-secondary danger-button flex-1" @click="show()">Delete</button>
                </div>

                <div
                    x-cloak
                    x-show="open"
                    x-transition.opacity
                    class="modal-backdrop fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4"
                    @keydown.escape.window="hide()"
                >
                    <div class="panel w-full max-w-md p-6" @click.outside="hide()">
                        <p class="text-danger text-xs font-semibold uppercase tracking-[0.35em]">Delete entry</p>
                        <h3 class="text-display mt-3 text-2xl font-bold">Remove {{ $restaurant->name }}?</h3>
                        <p class="text-body mt-3 text-sm">This deletes the restaurant record and its uploaded image permanently.</p>
                        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
                            <button type="button" class="btn-secondary" @click="hide()">Cancel</button>
                            <form action="{{ route('restaurants.destroy', $restaurant) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-primary btn-danger">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
