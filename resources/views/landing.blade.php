<x-layouts.app title="Local Restaurant Diary">
    <section class="mx-auto max-w-6xl space-y-8">
        <section class="panel hero-surface landing-card landing-hero px-6 py-10 sm:px-10 sm:py-12">
            <div class="max-w-3xl space-y-4 sm:space-y-5">
                <p class="eyebrow landing-card-eyebrow text-xs font-semibold uppercase tracking-[0.35em]">Local Restaurant Diary</p>
                <h2 class="text-display landing-card-title text-3xl font-extrabold sm:text-5xl">Save memorable meals and find them again on the map.</h2>
                <p class="text-body landing-card-body max-w-2xl text-sm leading-7 sm:text-base">
                    Track restaurants you have visited, keep notes and ratings, and build a personal map of places worth revisiting.
                </p>
                <div class="flex flex-col gap-2.5 sm:flex-row">
                    <a href="{{ route('login') }}" class="btn-primary">Get Started</a>
                    <a href="{{ route('auth.google.redirect') }}" class="btn-secondary landing-card-secondary">Continue with Google</a>
                </div>
            </div>
        </section>

        <section class="grid gap-5 md:grid-cols-3">
            <article class="panel landing-card p-6">
                <p class="eyebrow landing-card-eyebrow text-xs font-semibold uppercase tracking-[0.3em]">Capture</p>
                <h3 class="text-display landing-card-title mt-3 text-xl font-bold">Log each visit</h3>
                <p class="text-body landing-card-body mt-3 text-sm leading-7">
                    Store restaurant names, addresses, categories, ratings, notes, and photos in one place.
                </p>
            </article>

            <article class="panel landing-card p-6">
                <p class="eyebrow landing-card-eyebrow text-xs font-semibold uppercase tracking-[0.3em]">Locate</p>
                <h3 class="text-display landing-card-title mt-3 text-xl font-bold">View everything on a map</h3>
                <p class="text-body landing-card-body mt-3 text-sm leading-7">
                    See your saved restaurants geographically and quickly return to the places you want to revisit.
                </p>
            </article>

            <article class="panel landing-card p-6">
                <p class="eyebrow landing-card-eyebrow text-xs font-semibold uppercase tracking-[0.3em]">Organize</p>
                <h3 class="text-display landing-card-title mt-3 text-xl font-bold">Keep your dining history tidy</h3>
                <p class="text-body landing-card-body mt-3 text-sm leading-7">
                    Filter by category, sort by date or rating, and keep a clean personal archive of favorite spots.
                </p>
            </article>
        </section>
    </section>
</x-layouts.app>
