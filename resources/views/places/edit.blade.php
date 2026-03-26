<x-layouts.app title="Edit Memory">
    <section class="mx-auto max-w-4xl space-y-6">
        <div>
            <p class="eyebrow text-xs font-semibold uppercase tracking-[0.35em]">Edit</p>
            <h2 class="text-display mt-2 text-lg sm:text-2xl font-extrabold">Update {{ $place->name }}</h2>
        </div>

        <x-place-form
            :place="$place"
            :action="route('places.update', $place)"
            method="PUT"
            submit-label="Update memory"
        />
    </section>
</x-layouts.app>
