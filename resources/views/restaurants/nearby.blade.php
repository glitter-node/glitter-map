<x-layouts.app title="Nearby">
    <section
        x-data="window.restaurantMapPage({
            nearbyApiUrl: @js($nearbyApiUrl),
        })"
        class="space-y-4"
    >
        <section class="panel space-y-6 p-4 text-center sm:p-6">
            <div class="space-y-3">
                <p class="eyebrow text-xs font-semibold uppercase tracking-[0.35em]">Nearby</p>
                <h2 class="text-display text-2xl font-extrabold sm:text-3xl">Use your location to see saved restaurants nearby.</h2>
                <p class="text-body mx-auto max-w-2xl text-sm">Press the button to find the closest places you have already saved.</p>
            </div>

            <div class="flex justify-center">
                <button type="button" class="btn-primary" @click="locateUser()" :disabled="nearbyLoading">
                    <span x-show="!nearbyLoading">Use current location</span>
                    <span x-show="nearbyLoading" x-cloak>Finding nearby restaurants...</span>
                </button>
            </div>

            <div class="grid gap-3 text-left">
                <template x-if="nearbyState === 'idle'">
                    <div class="surface-subtle rounded-3xl p-8 text-center">
                        <p class="text-display text-lg font-semibold">Press “Use current location” to begin.</p>
                        <p class="text-body mt-2 text-sm">The results list will update here after location access is granted.</p>
                    </div>
                </template>

                <template x-if="nearbyState === 'loading'">
                    <div class="surface-subtle rounded-3xl p-8 text-center">
                        <p class="text-display text-lg font-semibold">Checking your location...</p>
                        <p class="text-body mt-2 text-sm">Once location access succeeds, your closest saved restaurants will appear here.</p>
                    </div>
                </template>

                <template x-if="nearbyState === 'error'">
                    <div class="status-danger px-4 py-4 text-sm">
                        <p class="font-semibold">Nearby search could not complete.</p>
                        <p class="mt-2" x-text="nearbyError"></p>
                    </div>
                </template>

                <template x-if="nearbyState === 'empty'">
                    <div class="surface-subtle rounded-3xl p-8 text-center">
                        <p class="text-display text-lg font-semibold">No nearby saved restaurants found.</p>
                        <p class="text-body mt-2 text-sm">Your location worked, but none of your saved places are close to you right now.</p>
                    </div>
                </template>

                <template x-if="nearbyState === 'success'">
                    <div class="space-y-4">
                        <div>
                            <p class="eyebrow text-xs font-semibold uppercase tracking-[0.35em]">Results</p>
                            <h3 class="text-display mt-2 text-2xl font-bold">Closest saved places</h3>
                            <p class="text-muted mt-2 text-xs uppercase tracking-[0.2em]" x-text="`Current location: ${userLocation.latitude.toFixed(4)}, ${userLocation.longitude.toFixed(4)}`"></p>
                        </div>

                        <div class="grid gap-3">
                            <template x-for="restaurant in nearbyRestaurants" :key="restaurant.id">
                                <a :href="restaurant.show_url" class="surface-elevated rounded-2xl px-4 py-3 transition">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="min-w-0">
                                            <p class="text-display truncate font-semibold" x-text="restaurant.name"></p>
                                            <p class="text-body mt-1 truncate text-sm" x-text="restaurant.address"></p>
                                        </div>
                                        <div class="shrink-0 text-right">
                                            <p class="eyebrow text-sm font-semibold" x-text="`${restaurant.distance_km} km`"></p>
                                            <p class="text-muted mt-1 text-xs uppercase tracking-[0.2em]" x-text="restaurant.category"></p>
                                        </div>
                                    </div>
                                </a>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </section>
    </section>
</x-layouts.app>
