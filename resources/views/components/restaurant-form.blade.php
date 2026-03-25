@props([
    'restaurant',
    'categories',
    'action',
    'method' => 'POST',
    'submitLabel' => 'Save',
])

@php
    $formMapConfig = [
        'type' => 'picker',
        'center' => ['lat' => 37.5665, 'lng' => 126.9780],
        'zoom' => 13,
        'latitude' => old('latitude', $restaurant->latitude),
        'longitude' => old('longitude', $restaurant->longitude),
        'inputLatitudeId' => 'latitude',
        'inputLongitudeId' => 'longitude',
    ];
@endphp

<form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="panel space-y-6 p-3 sm:p-5 sm:p-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid gap-5 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label for="name" class="label">Name</label>
            <input id="name" name="name" type="text" class="input" value="{{ old('name', $restaurant->name) }}" required maxlength="255">
            @error('name')
                <p class="text-danger mt-2 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="address" class="label">Address</label>
            <input id="address" name="address" type="text" class="input" value="{{ old('address', $restaurant->address) }}" required maxlength="255">
            @error('address')
                <p class="text-danger mt-2 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="category" class="label">Category</label>
            <select id="category" name="category" class="input" required>
                @foreach ($categories as $value => $label)
                    <option value="{{ $value }}" @selected(old('category', $restaurant->category) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('category')
                <p class="text-danger mt-2 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <div x-data="ratingInput(@js((float) old('rating', $restaurant->rating ?: 3)))">
            <label class="label">Rating</label>
            <div class="surface-subtle rounded-2xl p-2 sm:p-4">
                <div class="flex items-center gap-1">
                    <template x-for="star in 5" :key="star">
                        <button
                            type="button"
                            class="text-lg sm:text-2xl transition"
                            :class="(hover || rating) >= star ? 'rating-active' : 'rating-inactive'"
                            @mouseenter="hover = star"
                            @mouseleave="hover = 0"
                            @click="setRating(star)"
                            :aria-label="`${star} star`"
                        >★</button>
                    </template>
                </div>
                <div class="text-body mt-3 flex items-center justify-between text-sm">
                    <span>Tap to rate</span>
                    <span x-text="Number(rating).toFixed(1)"></span>
                </div>
                <input type="hidden" name="rating" :value="Number(rating).toFixed(1)">
            </div>
            @error('rating')
                <p class="text-danger mt-2 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="visited_at" class="label">Visited At</label>
            <input
                id="visited_at"
                name="visited_at"
                type="date"
                class="input"
                value="{{ old('visited_at', optional($restaurant->visited_at)->format('Y-m-d') ?? $restaurant->visited_at) }}"
                required
            >
            @error('visited_at')
                <p class="text-danger mt-2 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="memo" class="label">Memo</label>
            <textarea id="memo" name="memo" class="textarea">{{ old('memo', $restaurant->memo) }}</textarea>
            @error('memo')
                <p class="text-danger mt-2 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <div
            class="sm:col-span-2"
            x-data="{ latitude: @js(old('latitude', $restaurant->latitude)), longitude: @js(old('longitude', $restaurant->longitude)) }"
        >
            <div class="mb-2 flex items-center justify-between gap-3">
                <label class="label mb-0">Location</label>
                <span class="text-muted text-xs uppercase tracking-[0.2em]" x-text="latitude && longitude ? `${Number(latitude).toFixed(5)}, ${Number(longitude).toFixed(5)}` : 'No location selected'"></span>
            </div>

            <div
                id="restaurant-form-map"
                class="map-frame h-72 overflow-hidden rounded-3xl"
                data-map-config='@json($formMapConfig)'
            ></div>

            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                <div class="surface-subtle text-body rounded-2xl px-4 py-3 text-sm">
                    <p class="text-muted text-xs uppercase tracking-[0.2em]">Latitude</p>
                    <p class="text-display mt-2 font-semibold" x-text="latitude ? Number(latitude).toFixed(7) : 'Not selected'"></p>
                </div>
                <div class="surface-subtle text-body rounded-2xl px-4 py-3 text-sm">
                    <p class="text-muted text-xs uppercase tracking-[0.2em]">Longitude</p>
                    <p class="text-display mt-2 font-semibold" x-text="longitude ? Number(longitude).toFixed(7) : 'Not selected'"></p>
                </div>
            </div>

            <input id="latitude" name="latitude" type="hidden" x-model="latitude">
            <input id="longitude" name="longitude" type="hidden" x-model="longitude">

            @error('latitude')
                <p class="text-danger mt-2 text-sm">{{ $message }}</p>
            @enderror
            @error('longitude')
                <p class="text-danger mt-2 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="image" class="label">Image</label>
            <input id="image" name="image" type="file" class="input px-3 py-2.5 file:mr-4 file:rounded-full file:border-0 file:bg-orange-500 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-stone-950">
            @if ($restaurant->image_path)
                <div class="theme-border mt-3 overflow-hidden rounded-2xl border">
                    <img src="{{ asset('storage/' . $restaurant->image_path) }}" alt="{{ $restaurant->name }}" loading="lazy" width="400" height="192" class="h-48 w-full object-cover">
                </div>
            @endif
            @error('image')
                <p class="text-danger mt-2 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <div class="sm:col-span-2">
            <label class="surface-subtle text-body inline-flex items-center gap-3 rounded-2xl px-4 py-3 text-sm">
                <input type="hidden" name="is_revisit" value="0">
                <input type="checkbox" name="is_revisit" value="1" class="theme-border h-5 w-5 rounded border bg-transparent text-orange-500 focus:ring-orange-400" @checked(old('is_revisit', $restaurant->is_revisit))>
                <span>Mark as worth revisiting</span>
            </label>
            @error('is_revisit')
                <p class="text-danger mt-2 text-sm">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ route('restaurants.index') }}" class="btn-secondary">Cancel</a>
        <button type="submit" class="btn-primary">{{ $submitLabel }}</button>
    </div>
</form>
