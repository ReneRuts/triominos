{{-- Page title with optional back link, subtitle and a slot of action buttons. --}}
@props(['title', 'subtitle' => null, 'back' => null, 'backLabel' => 'Terug'])
<div class="mb-6 flex flex-wrap items-start justify-between gap-4">
    <div>
        @if ($back)
            <a href="{{ $back }}" class="text-sm text-stone-500 hover:text-stone-800">&larr; {{ $backLabel }}</a>
        @endif
        <h1 class="text-2xl font-semibold tracking-tight text-stone-900">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 text-sm text-stone-500">{{ $subtitle }}</p>
        @endif
    </div>
    @if (! $slot->isEmpty())
        <div class="flex flex-wrap items-center gap-2">{{ $slot }}</div>
    @endif
</div>
