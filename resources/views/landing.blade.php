<x-layouts.app title="Personal Spatial Memory Log">
    <section class="mx-auto max-w-6xl space-y-8">
        <section class="panel hero-surface landing-card landing-hero px-6 py-10 sm:px-10 sm:py-12">
            <div class="max-w-3xl space-y-4 sm:space-y-5">
                <p class="eyebrow landing-card-eyebrow text-xs font-semibold uppercase tracking-[0.35em]">Personal Spatial Memory Log</p>
                <h2 class="text-display landing-card-title text-3xl font-extrabold sm:text-5xl">Save meaningful places and recover them through map, time, and recall.</h2>
                <p class="text-body landing-card-body max-w-2xl text-sm leading-7 sm:text-base">
                    Track personal spatial experiences, preserve why a place mattered, and navigate your memories through spatial anchors.
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
                <h3 class="text-display landing-card-title mt-3 text-xl font-bold">Log each experience</h3>
                <p class="text-body landing-card-body mt-3 text-sm leading-7">
                    Store place names, coordinates, impressions, context, memory notes, and recall aids in one place.
                </p>
            </article>

            <article class="panel landing-card p-6">
                <p class="eyebrow landing-card-eyebrow text-xs font-semibold uppercase tracking-[0.3em]">Navigate</p>
                <h3 class="text-display landing-card-title mt-3 text-xl font-bold">Browse memories on a map</h3>
                <p class="text-body landing-card-body mt-3 text-sm leading-7">
                    See your saved places geographically and move through your personal geography with spatial context.
                </p>
            </article>

            <article class="panel landing-card p-6">
                <p class="eyebrow landing-card-eyebrow text-xs font-semibold uppercase tracking-[0.3em]">Recall</p>
                <h3 class="text-display landing-card-title mt-3 text-xl font-bold">Reconnect memories with time</h3>
                <p class="text-body landing-card-body mt-3 text-sm leading-7">
                    Use timeline sorting, revisit intentions, and context-rich notes to reconstruct why a place mattered.
                </p>
            </article>
        </section>
    </section>
</x-layouts.app>
