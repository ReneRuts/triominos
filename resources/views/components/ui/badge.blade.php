@props(['color' => 'stone'])
@php
    $colors = ['stone' => 'bg-stone-100 text-stone-600', 'blue' => 'bg-blue-50 text-blue-800', 'amber' => 'bg-amber-50 text-amber-800'];
@endphp
<span {{ $attributes->class('inline-block rounded-full px-2 py-0.5 text-xs font-medium '.$colors[$color]) }}>{{ $slot }}</span>
