<x-layouts.app title="Local Restaurant Diary">
    <section class="mx-auto max-w-6xl space-y-10">
        <section class="panel hero-surface landing-card landing-hero px-6 py-12 sm:px-10 sm:py-16">
            <div class="max-w-3xl space-y-6">
                <p class="eyebrow landing-card-eyebrow text-xs font-semibold uppercase tracking-[0.35em]">Local Restaurant Diary</p>
                <h2 class="text-display landing-card-title text-3xl font-extrabold sm:text-5xl">Save memorable meals and find them again on the map.</h2>
                <p class="text-body landing-card-body max-w-2xl text-sm leading-7 sm:text-base">
                    Track restaurants you have visited, keep notes and ratings, and build a personal map of places worth revisiting.
                </p>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('auth.login') }}" class="btn-primary">Get Started</a>
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

        <section class="panel landing-card landing-cta px-6 py-10 text-center sm:px-10">
            <p class="eyebrow landing-card-eyebrow text-xs font-semibold uppercase tracking-[0.35em]">Start Now</p>
            <h3 class="text-display landing-card-title mt-3 text-2xl font-extrabold sm:text-3xl">Create your restaurant diary.</h3>
            <p class="text-body landing-card-body mx-auto mt-3 max-w-2xl text-sm leading-7">
                Begin with email verification or sign in with Google and start recording the places that matter.
            </p>
            <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('auth.login') }}" class="btn-primary">Get Started</a>
                <a href="{{ route('auth.google.redirect') }}" class="btn-secondary landing-card-secondary">Continue with Google</a>
            </div>
        </section>
    </section>
</x-layouts.app>
