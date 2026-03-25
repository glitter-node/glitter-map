<x-layouts.app title="Complete Registration">
    <section class="mx-auto max-w-xl space-y-6">
        <div>
            <p class="eyebrow text-xs font-semibold uppercase tracking-[0.35em]">Register</p>
            <h2 class="text-display mt-2 text-lg font-extrabold sm:text-2xl">Complete your account</h2>
            <p class="text-body mt-2 text-sm">Your email is already verified. Finish registration with your display name.</p>
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
                    <p class="text-danger mt-2 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('auth.email.request') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Create account</button>
            </div>
        </form>
    </section>
</x-layouts.app>
