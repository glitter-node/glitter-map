<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Continue | Personal Spatial Memory Log</title>
        <meta name="description" content="Enter your email to receive a secure access link.">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://accounts.google.com/gsi/client" async defer></script>
        <script src="{{ asset('js/google-one-tap.js') }}" defer></script>
    </head>
    <body>
        <div class="shell">
            <main class="mx-auto flex min-h-screen max-w-7xl items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
                <section class="panel w-full max-w-md space-y-6 p-6 sm:p-8">
                    <div
                        id="g_id_onload"
                        data-client_id="{{ config('services.google.client_id') }}"
                        data-callback="handleGoogleCredential"
                        data-auto_prompt="true"
                    ></div>

                    @if (session('success'))
                        <div class="status-success px-4 py-3 text-sm">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="status-danger px-4 py-3 text-sm">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="space-y-3 text-center">
                        <p class="eyebrow text-xs font-semibold uppercase tracking-[0.35em]">Personal Spatial Memory Log</p>
                        <h1 class="text-display text-2xl font-extrabold sm:text-3xl">Continue with email</h1>
                        <p class="text-body text-sm leading-7">Enter your email to receive a secure access link</p>
                    </div>

                    <form action="{{ route('auth.link') }}" method="POST" class="space-y-4">
                        @csrf

                        <div>
                            <label for="email" class="label text-left">Email</label>
                            <input id="email" name="email" type="email" class="input" value="{{ old('email') }}" required autocomplete="email">
                            @error('email')
                                <p class="text-danger mt-2 text-sm">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="btn-primary w-full">Send access link</button>
                    </form>

                    <div class="flex flex-col gap-3">
                        <a href="{{ route('auth.google.redirect') }}" class="btn-secondary w-full">Continue with Google</a>
                    </div>

                    <div class="pt-2 text-center">
                        <a href="{{ route('landing') }}" class="btn-secondary">Cancel</a>
                    </div>
                </section>
            </main>
        </div>
    </body>
</html>
