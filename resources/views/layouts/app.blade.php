<!DOCTYPE html>
<html lang="en" data-theme="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? ($pageTitle ?? 'Local Restaurant Diary') }}</title>
        <meta name="description" content="Track memorable restaurants, revisit-worthy spots, and neighborhood favorites.">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="preload" href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" as="style">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet" />
        <style>body{margin:0;font-family:Manrope,ui-sans-serif,system-ui,sans-serif;background:#0c0a09;color:#f9fafb}.shell{min-height:100vh}.mx-auto{margin-inline:auto}.max-w-7xl{max-width:80rem}.min-h-screen{min-height:100vh}.flex{display:flex}.flex-col{flex-direction:column}.flex-1{flex:1 1 0%}.px-4{padding-left:1rem;padding-right:1rem}.py-5{padding-top:1.25rem;padding-bottom:1.25rem}.mb-6{margin-bottom:1.5rem}.panel{border:1px solid rgba(245,245,244,.14);background:#1c1917;border-radius:1.5rem}.text-lg{font-size:1.125rem;line-height:1.75rem}.font-extrabold{font-weight:800}.text-white{color:#fff}@media (min-width:640px){.sm\:px-6{padding-left:1.5rem;padding-right:1.5rem}.sm\:text-2xl{font-size:1.5rem;line-height:2rem}}@media (min-width:1024px){.lg\:px-8{padding-left:2rem;padding-right:2rem}}</style>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    </head>
    <body>
        <div class="shell">
            <div class="mx-auto flex min-h-screen max-w-7xl flex-col px-4 py-5 sm:px-6 lg:px-8">
                <nav class="panel mb-6 flex flex-col gap-4 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <a href="{{ route('restaurants.index') }}" class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-orange-300/80">local-restaurant-diary</p>
                        <h1 class="truncate text-lg font-extrabold text-white sm:text-2xl">Neighborhood dining log</h1>
                    </a>

                    <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                        <x-theme-toggle />
                        <a href="{{ route('restaurants.index') }}" class="{{ request()->routeIs('restaurants.index') ? 'btn-primary' : 'btn-secondary' }}" @if (request()->routeIs('restaurants.index')) aria-current="page" @endif>List</a>
                        <a href="{{ route('restaurants.create') }}" class="{{ request()->routeIs('restaurants.create') ? 'btn-primary' : 'btn-secondary' }}" @if (request()->routeIs('restaurants.create')) aria-current="page" @endif>Add</a>
                    </div>
                </nav>

                @if (session('success'))
                    <div class="mb-6 rounded-2xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 rounded-2xl border border-rose-400/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                        {{ session('error') }}
                    </div>
                @endif

                <main class="flex-1">
                    @yield('content')
                    {{ $slot ?? '' }}
                </main>

                <footer class="mt-8 border-t border-white/10 py-6 text-center text-xs uppercase tracking-[0.25em] text-stone-500">
                    Capture the places worth revisiting.
                </footer>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
