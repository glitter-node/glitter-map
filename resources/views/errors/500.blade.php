@extends('layouts.app')

@section('content')
    <section class="mx-auto flex max-w-3xl flex-col items-center justify-center gap-6 py-16 text-center sm:py-24">
        <div class="panel w-full p-8 sm:p-12">
            <p class="text-danger text-xs font-semibold uppercase tracking-[0.35em]">500</p>
            <h2 class="text-display mt-4 text-3xl font-extrabold sm:text-5xl">Something went wrong</h2>
            <p class="text-body mt-4 text-sm leading-7 sm:text-base">
                An unexpected error interrupted this request. Please try again in a moment.
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
                <a href="{{ route('places.index') }}" class="btn-primary">Back to list</a>
                <button type="button" class="btn-secondary js-reload-page">Retry</button>
            </div>
        </div>
    </section>
@endsection
