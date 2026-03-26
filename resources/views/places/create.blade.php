<x-layouts.app title="Add Memory">
    <section class="mx-auto max-w-4xl space-y-6">
        <div>
            <p class="eyebrow text-xs font-semibold uppercase tracking-[0.35em]">Create</p>
            <h2 class="text-display mt-2 text-lg sm:text-2xl font-extrabold">Add a spatial memory</h2>
        </div>

        <x-place-form
            :place="$place"
            :action="route('places.store')"
            submit-label="Save memory"
            variant="create"
        />
    </section>
</x-layouts.app>
