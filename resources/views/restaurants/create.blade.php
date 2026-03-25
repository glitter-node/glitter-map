<x-layouts.app title="Add Restaurant">
    <section class="mx-auto max-w-4xl space-y-6">
        <div>
            <p class="eyebrow text-xs font-semibold uppercase tracking-[0.35em]">Create</p>
            <h2 class="text-display mt-2 text-lg sm:text-2xl font-extrabold">Add a new restaurant log</h2>
        </div>

        <x-restaurant-form
            :restaurant="$restaurant"
            :categories="$categories"
            :action="route('restaurants.store')"
            submit-label="Save restaurant"
        />
    </section>
</x-layouts.app>
