@props(['variant' => 'primary', 'size' => 'md', 'href' => null, 'type' => 'submit'])
@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-lg font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';
    $sizes = ['sm' => 'px-3 py-1.5 text-sm', 'md' => 'px-4 py-2 text-sm', 'lg' => 'px-6 py-3 text-base'];
    $variants = [
        'primary' => 'bg-blue-700 text-white hover:bg-blue-800',
        'secondary' => 'border border-stone-300 bg-white text-stone-700 hover:bg-stone-50',
        'danger' => 'bg-rose-600 text-white hover:bg-rose-700',
        'ghost' => 'text-stone-600 hover:bg-stone-100',
    ];
    $classes = "$base {$sizes[$size]} {$variants[$variant]}";
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
