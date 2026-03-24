<x-layouts.app title="Verify Email">
    <section class="mx-auto max-w-xl space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-orange-300/80">Register</p>
            <h2 class="mt-2 text-lg font-extrabold text-white sm:text-2xl">Verify your email first</h2>
            <p class="mt-2 text-sm text-stone-400">We will send a verification link before creating your account.</p>
        </div>

        <form action="{{ route('auth.email.request.send') }}" method="POST" class="panel space-y-6 p-3 sm:p-5 sm:p-6">
            @csrf

            <div>
                <label for="email" class="label">Email</label>
                <input id="email" name="email" type="email" class="input" value="{{ old('email') }}" required autocomplete="email">
                @error('email')
                    <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('restaurants.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Send verification link</button>
            </div>
        </form>
    </section>
</x-layouts.app>
