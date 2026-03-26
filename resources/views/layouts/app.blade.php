<!DOCTYPE html>
<html lang="en" data-theme="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? ($pageTitle ?? 'Personal Spatial Memory Log') }}</title>
        <meta name="description" content="Track personal spatial experiences, recall meaningful places, and navigate them through map and timeline views.">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="preload" href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" as="style">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet" />
        <style>:root{--boot-bg:#0c0a09;--boot-text:#f9fafb;--boot-panel:#1c1917;--boot-border:rgba(245,245,244,.14)}[data-theme='light']{--boot-bg:#edf1f5;--boot-text:#1f2933;--boot-panel:#f1f4f7;--boot-border:rgba(148,163,184,.22)}body{margin:0;font-family:Manrope,ui-sans-serif,system-ui,sans-serif;background:var(--boot-bg);color:var(--boot-text)}.shell{min-height:100vh}.mx-auto{margin-inline:auto}.max-w-7xl{max-width:80rem}.min-h-screen{min-height:100vh}.flex{display:flex}.flex-col{flex-direction:column}.flex-1{flex:1 1 0%}.px-4{padding-left:1rem;padding-right:1rem}.py-5{padding-top:1.25rem;padding-bottom:1.25rem}.mb-6{margin-bottom:1.5rem}.panel{border:1px solid var(--boot-border);background:var(--boot-panel);border-radius:1.5rem}.text-lg{font-size:1.125rem;line-height:1.75rem}.font-extrabold{font-weight:800}@media (min-width:640px){.sm\:px-6{padding-left:1.5rem;padding-right:1.5rem}.sm\:text-2xl{font-size:1.5rem;line-height:2rem}}@media (min-width:1024px){.lg\:px-8{padding-left:2rem;padding-right:2rem}}</style>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    </head>
    <body>
        @php($isAuthenticated = auth()->check())
        <div class="shell">
            <div class="mx-auto flex min-h-screen max-w-7xl flex-col px-4 {{ $isAuthenticated ? 'py-5' : 'py-3 sm:py-4' }} sm:px-6 lg:px-8">
                <nav class="panel {{ $isAuthenticated ? 'mb-6 gap-4 px-5 py-4 sm:px-6' : 'mb-4 gap-3 px-4 py-3 sm:px-5' }} flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ $isAuthenticated ? route('places.index') : route('landing') }}" class="min-w-0">
                        <p class="eyebrow text-xs font-semibold uppercase tracking-[0.35em]">spatial-memory-log</p>
                        <h1 class="text-display truncate {{ $isAuthenticated ? 'text-lg sm:text-2xl' : 'text-base sm:text-xl' }} font-extrabold">Personal Spatial Memory Log</h1>
                    </a>

                    <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                        <x-theme-toggle />
                        @auth
                            <a href="{{ route('places.index') }}" class="{{ request()->routeIs('places.index') ? 'btn-primary' : 'btn-secondary' }}" @if (request()->routeIs('places.index')) aria-current="page" @endif>List</a>
                            <a href="{{ route('places.map') }}" class="{{ request()->routeIs('places.map') ? 'btn-primary' : 'btn-secondary' }}" @if (request()->routeIs('places.map')) aria-current="page" @endif>Map</a>
                            <a href="{{ route('places.insights') }}" class="{{ request()->routeIs('places.insights') ? 'btn-primary' : 'btn-secondary' }}" @if (request()->routeIs('places.insights')) aria-current="page" @endif>Insights</a>
                            <a href="{{ route('places.nearby') }}" class="{{ request()->routeIs('places.nearby') ? 'btn-primary' : 'btn-secondary' }}" @if (request()->routeIs('places.nearby')) aria-current="page" @endif>Nearby</a>
                            <a href="{{ route('places.create') }}" class="{{ request()->routeIs('places.create') ? 'btn-primary' : 'btn-secondary' }}" @if (request()->routeIs('places.create')) aria-current="page" @endif>Add</a>
                        @else
                            <a href="{{ route('auth.google.redirect') }}" class="btn-primary">Get Started</a>
                        @endauth
                    </div>
                </nav>

                @if (session('success'))
                    <div class="status-success mb-6 px-4 py-3 text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="status-danger mb-6 px-4 py-3 text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                <main class="flex-1">
                    @yield('content')
                    {{ $slot ?? '' }}
                </main>

                <footer class="theme-border mt-8 border-t py-6 text-center text-xs uppercase tracking-[0.25em] text-muted">
                    Recall places through time and space.
                </footer>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
