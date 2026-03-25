<x-layouts.app title="Edit Restaurant">
    <section class="mx-auto max-w-4xl space-y-6">
        <div>
            <p class="eyebrow text-xs font-semibold uppercase tracking-[0.35em]">Edit</p>
            <h2 class="text-display mt-2 text-lg sm:text-2xl font-extrabold">Update {{ $restaurant->name }}</h2>
        </div>

        <x-restaurant-form
            :restaurant="$restaurant"
            :categories="$categories"
            :action="route('restaurants.update', $restaurant)"
            method="PUT"
            submit-label="Update restaurant"
        />
    </section>
</x-layouts.app>
