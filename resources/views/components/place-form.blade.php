@props([
    'place',
    'action',
    'method' => 'POST',
    'submitLabel' => 'Save',
    'variant' => 'default',
])

<form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="panel space-y-6 p-3 sm:p-5 sm:p-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="space-y-6">
        <section class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="name" class="label">Place Name</label>
                <input id="name" name="name" type="text" class="input" value="{{ old('name', $place->name) }}" required maxlength="255" placeholder="Han River lookout, alley bench, archive room">
                @error('name')
                    <p class="text-danger mt-2 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="address" class="label">Address or Area</label>
                <input id="address" name="address" type="text" class="input" value="{{ old('address', $place->address) }}" required maxlength="255" placeholder="Street address, district, or landmark">
                @error('address')
                    <p class="text-danger mt-2 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="context" class="label">Context</label>
                <textarea id="context" name="context" class="textarea" required placeholder="Why did this place matter? What was happening here?">{{ old('context', $place->context) }}</textarea>
                @error('context')
                    <p class="text-danger mt-2 text-sm">{{ $message }}</p>
                @enderror
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2">
            <div x-data="impressionInput(@js((float) old('impression', $place->impression ?: 3)))">
                <label class="label">Impression</label>
                <div class="surface-subtle rounded-2xl p-3 sm:p-4">
                    <div class="flex items-center gap-1">
                        <template x-for="star in 5" :key="star">
                            <button
                                type="button"
                                class="text-lg sm:text-2xl transition"
                                :class="(hover || impression) >= star ? 'impression-active' : 'impression-inactive'"
                                @mouseenter="hover = star"
                                @mouseleave="hover = 0"
                                @click="setImpression(star)"
                                :aria-label="`${star} impression`"
                            >★</button>
                        </template>
                    </div>
                    <div class="text-body mt-3 flex items-center justify-between text-sm">
                        <span>How strong was the feeling?</span>
                        <span x-text="Number(impression).toFixed(1)"></span>
                    </div>
                    <input type="hidden" name="impression" :value="Number(impression).toFixed(1)">
                </div>
                @error('impression')
                    <p class="text-danger mt-2 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="experienced_at" class="label">Experience Time</label>
                <input
                    id="experienced_at"
                    name="experienced_at"
                    type="date"
                    class="input"
                    value="{{ old('experienced_at', optional($place->experienced_at)->format('Y-m-d') ?? $place->experienced_at) }}"
                    required
                >
                @error('experienced_at')
                    <p class="text-danger mt-2 text-sm">{{ $message }}</p>
                @enderror
            </div>
        </section>

        <section>
            <label for="memory_note" class="label">Memory Note</label>
            <textarea id="memory_note" name="memory_note" class="textarea" placeholder="Details that help future recall: weather, sound, who was there, what changed.">{{ old('memory_note', $place->memory_note) }}</textarea>
            @error('memory_note')
                <p class="text-danger mt-2 text-sm">{{ $message }}</p>
            @enderror
        </section>

        <section class="surface-subtle space-y-3 rounded-3xl p-4">
            <div>
                <p class="eyebrow text-xs font-semibold uppercase tracking-[0.35em]">Spatial Position</p>
                <p class="text-body mt-2 text-sm">Coordinates anchor the memory on the map. They can be added now or resolved from the address later.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="latitude" class="label">Latitude</label>
                    <input id="latitude" name="latitude" type="number" step="0.0000001" class="input" value="{{ old('latitude', $place->latitude) }}">
                </div>
                <div>
                    <label for="longitude" class="label">Longitude</label>
                    <input id="longitude" name="longitude" type="number" step="0.0000001" class="input" value="{{ old('longitude', $place->longitude) }}">
                </div>
            </div>

            @error('latitude')
                <p class="text-danger text-sm">{{ $message }}</p>
            @enderror
            @error('longitude')
                <p class="text-danger text-sm">{{ $message }}</p>
            @enderror
        </section>

        <section class="surface-subtle space-y-4 rounded-3xl p-4">
            <div>
                <p class="eyebrow text-xs font-semibold uppercase tracking-[0.35em]">Optional Recall Aids</p>
                <p class="text-body mt-2 text-sm">Keep only what helps later recall.</p>
            </div>

            <div class="grid gap-4">
                <div>
                    <label for="image" class="label">Reference Image</label>
                    <input id="image" name="image" type="file" class="input px-3 py-2.5 file:mr-4 file:rounded-full file:border-0 file:bg-orange-500 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-stone-950">
                    @if ($place->image_path)
                        <div class="theme-border mt-3 overflow-hidden rounded-2xl border">
                            <img src="{{ asset('storage/' . $place->image_path) }}" alt="{{ $place->name }}" loading="lazy" width="400" height="192" class="h-48 w-full object-cover">
                        </div>
                    @endif
                    @error('image')
                        <p class="text-danger mt-2 text-sm">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="surface-subtle text-body inline-flex items-center gap-3 rounded-2xl px-4 py-3 text-sm">
                        <input type="hidden" name="revisit_intention" value="0">
                        <input type="checkbox" name="revisit_intention" value="1" class="theme-border h-5 w-5 rounded border bg-transparent text-orange-500 focus:ring-orange-400" @checked(old('revisit_intention', $place->revisit_intention))>
                        <span>I want to return to this place</span>
                    </label>
                    @error('revisit_intention')
                        <p class="text-danger mt-2 text-sm">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>
    </div>

    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
        <a href="{{ route('places.index') }}" class="btn-secondary">Cancel</a>
        <button type="submit" class="btn-primary">{{ $submitLabel }}</button>
    </div>
</form>
