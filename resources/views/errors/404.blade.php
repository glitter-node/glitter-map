@extends('layouts.app')

@section('content')
    <section class="mx-auto flex max-w-3xl flex-col items-center justify-center gap-6 py-16 text-center sm:py-24">
        <div class="panel w-full p-8 sm:p-12">
            <p class="eyebrow text-xs font-semibold uppercase tracking-[0.35em]">404</p>
            <h2 class="text-display mt-4 text-3xl font-extrabold sm:text-5xl">Place not found</h2>
            <p class="text-body mt-4 text-sm leading-7 sm:text-base">
                The page you requested does not exist or the place entry may have been removed.
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
                <a href="{{ route('places.index') }}" class="btn-primary">Back to log</a>
                <a href="{{ route('places.create') }}" class="btn-secondary">Add memory</a>
            </div>
        </div>
    </section>
@endsection
