@php
    $showMapConfig = [
        'type' => 'show',
        'center' => ['lat' => $restaurant->latitude, 'lng' => $restaurant->longitude],
        'zoom' => 15,
        'marker' => [
            'name' => $restaurant->name,
            'latitude' => $restaurant->latitude,
            'longitude' => $restaurant->longitude,
        ],
    ];
@endphp

<x-layouts.app :title="$restaurant->name">
    <section class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <div class="panel overflow-hidden">
            <div class="aspect-[4/3] bg-stone-900">
                @if ($restaurant->image_path)
                    <img src="{{ asset('storage/' . $restaurant->image_path) }}" alt="{{ $restaurant->name }}" loading="eager" fetchpriority="high" width="400" height="300" class="h-full w-full object-cover">
                @else
                    <div class="flex h-full items-center justify-center bg-[linear-gradient(135deg,_rgba(249,115,22,0.3),_rgba(28,25,23,0.85))]">
                        <span class="text-sm font-semibold uppercase tracking-[0.4em] text-orange-100/70">No Image</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="panel p-3 sm:p-5 sm:p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:justify-between">
                    <div>
                        <span class="badge bg-orange-400/15 text-orange-200">{{ $restaurant->category_label }}</span>
                        <h2 class="mt-4 text-lg sm:text-2xl font-extrabold text-white">{{ $restaurant->name }}</h2>
                        <p class="mt-2 text-stone-300">{{ $restaurant->address }}</p>
                    </div>
                    @if ($restaurant->is_revisit)
                        <span class="badge bg-emerald-400/15 text-emerald-200">Revisit</span>
                    @endif
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl bg-white/5 p-2 sm:p-4">
                        <p class="text-sm uppercase tracking-[0.2em] text-stone-400">Rating</p>
                        <p class="mt-3 text-lg sm:text-2xl font-bold text-amber-300">{{ number_format((float) $restaurant->rating, 1) }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/5 p-2 sm:p-4">
                        <p class="text-sm uppercase tracking-[0.2em] text-stone-400">Visited</p>
                        <p class="mt-3 text-xl font-bold text-white">{{ $restaurant->visited_at->format('F d, Y') }}</p>
                    </div>
                </div>

                <div class="mt-6">
                    <p class="text-sm uppercase tracking-[0.2em] text-stone-400">Memo</p>
                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-stone-200">{{ $restaurant->memo ?: 'No notes recorded for this visit.' }}</p>
                </div>

                <div class="mt-6">
                    <p class="text-sm uppercase tracking-[0.2em] text-stone-400">Coordinates</p>
                    <p class="mt-3 text-sm text-stone-200">
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
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-orange-300/80">Map</p>
                        <h3 class="mt-2 text-2xl font-bold text-white">Restaurant location</h3>
                    </div>
                </div>

                @if (! is_null($restaurant->latitude) && ! is_null($restaurant->longitude))
                    <div
                        id="restaurant-show-map"
                        class="mt-6 h-80 overflow-hidden rounded-3xl border border-white/10"
                        data-map-config='@json($showMapConfig)'
                    ></div>
                @elseif ($restaurant->geocode_status === 'pending')
                    <div class="mt-6 rounded-3xl border border-dashed border-amber-400/20 bg-amber-500/10 p-10 text-center">
                        <p class="text-lg font-semibold text-amber-100">위치 처리 중</p>
                        <p class="mt-2 text-sm text-amber-200/80">좌표가 확인되는 즉시 지도에 자동 반영됩니다.</p>
                    </div>
                @elseif ($restaurant->geocode_status === 'failed')
                    <div class="mt-6 rounded-3xl border border-dashed border-rose-400/20 bg-rose-500/10 p-10 text-center">
                        <p class="text-lg font-semibold text-rose-100">위치 처리 실패</p>
                        <p class="mt-2 text-sm text-rose-200/80">관리자가 재시도하거나 수정 화면에서 좌표를 직접 지정할 수 있습니다.</p>
                    </div>
                @else
                    <div class="mt-6 rounded-3xl border border-dashed border-white/10 bg-white/5 p-10 text-center">
                        <p class="text-lg font-semibold text-white">No map location saved.</p>
                        <p class="mt-2 text-sm text-stone-400">Edit this restaurant to choose a position on the map.</p>
                    </div>
                @endif
            </div>

            <div class="panel p-3 sm:p-5 sm:p-6" x-data="deleteModal()">
                <div class="flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('restaurants.edit', $restaurant) }}" class="btn-primary flex-1">Edit</a>
                    <a href="{{ route('restaurants.index') }}" class="btn-secondary flex-1">Back to list</a>
                    <button type="button" class="btn-secondary flex-1 border-rose-400/20 text-rose-200 hover:bg-rose-500/10" @click="show()">Delete</button>
                </div>

                <div
                    x-cloak
                    x-show="open"
                    x-transition.opacity
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-2 sm:p-4"
                    @keydown.escape.window="hide()"
                >
                    <div class="panel w-full max-w-md p-6" @click.outside="hide()">
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-rose-300/80">Delete entry</p>
                        <h3 class="mt-3 text-2xl font-bold text-white">Remove {{ $restaurant->name }}?</h3>
                        <p class="mt-3 text-sm text-stone-400">This deletes the restaurant record and its uploaded image permanently.</p>
                        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
                            <button type="button" class="btn-secondary" @click="hide()">Cancel</button>
                            <form action="{{ route('restaurants.destroy', $restaurant) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-primary bg-rose-500 text-white hover:bg-rose-400">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
