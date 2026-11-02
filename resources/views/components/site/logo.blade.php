@props([
    'size' => '8',
])
<span {{ $attributes->class('inline-flex items-center gap-2.5 font-semibold tracking-tight text-stone-900') }}>
    <img src="{{ asset('logo.svg') }}" alt="{{ config('site.name') }}"
        class="h-{{ $size }} w-{{ $size }}" />
    <span>{{ config('site.name') }}</span>
</span>
