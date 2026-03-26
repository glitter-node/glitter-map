@php
    $showMapConfig = [
        'marker' => [
            'latitude' => $place->latitude,
            'longitude' => $place->longitude,
            'label' => $place->name,
        ],
        'zoom' => 16,
        'pollUrl' => route('places.location', $place),
    ];
@endphp

<x-layouts.app :title="$place->name">
    <section class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <div class="panel overflow-hidden">
            <div class="media-surface aspect-[4/3]">
                @if ($place->image_path)
                    <img src="{{ asset('storage/' . $place->image_path) }}" alt="{{ $place->name }}" loading="eager" fetchpriority="high" width="400" height="300" class="h-full w-full object-cover">
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
                        <h2 class="text-display mt-4 text-lg font-extrabold sm:text-2xl">{{ $place->name }}</h2>
                        <p class="text-body mt-2">{{ $place->address }}</p>
                    </div>
                    @if ($place->revisit_intention)
                        <span class="badge badge-success">Return</span>
                    @endif
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div class="surface-subtle rounded-2xl p-2 sm:p-4">
                        <p class="text-muted text-sm uppercase tracking-[0.2em]">Impression</p>
                        <p class="impression-active mt-3 text-lg font-bold sm:text-2xl">{{ number_format((float) $place->impression, 1) }}</p>
                    </div>
                    <div class="surface-subtle rounded-2xl p-2 sm:p-4">
                        <p class="text-muted text-sm uppercase tracking-[0.2em]">Experience Time</p>
                        <p class="text-display mt-3 text-xl font-bold">{{ optional($place->experienced_at)->format('F d, Y') ?? 'Not recorded' }}</p>
                    </div>
                </div>

                <div class="mt-6">
                    <p class="text-muted text-sm uppercase tracking-[0.2em]">Context</p>
                    <p class="text-body mt-3 whitespace-pre-line text-sm leading-7">{{ $place->context }}</p>
                </div>

                <div class="mt-6">
                    <p class="text-muted text-sm uppercase tracking-[0.2em]">Memory Note</p>
                    <p class="text-body mt-3 whitespace-pre-line text-sm leading-7">{{ $place->memory_note ?: 'No memory note recorded for this experience.' }}</p>
                </div>

                <div class="mt-6">
                    <p class="text-muted text-sm uppercase tracking-[0.2em]">Coordinates</p>
                    <p class="text-body mt-3 text-sm">
                        @if (! is_null($place->latitude) && ! is_null($place->longitude))
                            {{ number_format($place->latitude, 7) }}, {{ number_format($place->longitude, 7) }}
                        @elseif ($place->geocode_status === 'pending')
                            Location is being processed
                        @elseif ($place->geocode_status === 'failed')
                            Location processing failed
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
                        <h3 class="text-display mt-2 text-2xl font-bold">Memory anchor</h3>
                    </div>
                </div>

                @if (! is_null($place->latitude) && ! is_null($place->longitude))
                    <div
                        id="place-show-map"
                        class="map-frame mt-6 h-80 overflow-hidden rounded-3xl"
                        data-map-config='@json($showMapConfig)'
                    ></div>
                @elseif ($place->geocode_status === 'pending')
                    <div
                        id="place-show-map"
                        class="status-warning mt-6 h-80 rounded-3xl border-dashed p-10 text-center"
                        data-map-config='@json($showMapConfig)'
                    >
                        <p class="text-lg font-semibold">Location is being processed</p>
                        <p class="mt-2 text-sm">The map will appear automatically when coordinates are ready.</p>
                    </div>
                @elseif ($place->geocode_status === 'failed')
                    <div class="status-danger mt-6 rounded-3xl border-dashed p-10 text-center">
                        <p class="text-lg font-semibold">Location processing failed</p>
                        <p class="mt-2 text-sm">Try again later or edit this place to set coordinates manually.</p>
                    </div>
                @else
                    <div class="surface-subtle mt-6 rounded-3xl border-dashed p-10 text-center">
                        <p class="text-display text-lg font-semibold">No map location saved.</p>
                        <p class="text-body mt-2 text-sm">Edit this place to add coordinates.</p>
                    </div>
                @endif
            </div>

            <div class="panel p-3 sm:p-5 sm:p-6" x-data="deleteModal()">
                <div class="flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('places.edit', $place) }}" class="btn-primary flex-1">Edit</a>
                    <a href="{{ route('places.index') }}" class="btn-secondary flex-1">Back to log</a>
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
                        <h3 class="text-display mt-3 text-2xl font-bold">Remove {{ $place->name }}?</h3>
                        <p class="text-body mt-3 text-sm">This deletes the spatial memory and its uploaded image permanently.</p>
                        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
                            <button type="button" class="btn-secondary" @click="hide()">Cancel</button>
                            <form action="{{ route('places.destroy', $place) }}" method="POST">
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
