<x-layouts.app title="Complete Registration">
    <section class="mx-auto max-w-xl space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-orange-300/80">Register</p>
            <h2 class="mt-2 text-lg font-extrabold text-white sm:text-2xl">Complete your account</h2>
            <p class="mt-2 text-sm text-stone-400">Your email is already verified. Finish registration with your display name.</p>
        </div>

        <form action="{{ route('auth.register.store') }}" method="POST" class="panel space-y-6 p-3 sm:p-5 sm:p-6">
            @csrf

            <div>
                <label class="label">Verified Email</label>
                <div class="input flex items-center" aria-readonly="true">{{ $email }}</div>
            </div>

            <div>
                <label for="name" class="label">Name</label>
                <input id="name" name="name" type="text" class="input" value="{{ old('name') }}" required maxlength="255" autocomplete="name">
                @error('name')
                    <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('auth.email.request') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Create account</button>
            </div>
        </form>
    </section>
</x-layouts.app>
