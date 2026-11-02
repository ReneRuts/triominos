@props(['type' => 'info'])
@php
    $styles = [
        'info' => 'border-blue-200 bg-blue-50 text-blue-900',
        'success' => 'border-blue-200 bg-blue-50 text-blue-900',
        'error' => 'border-rose-200 bg-rose-50 text-rose-800',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-900',
    ];
@endphp
<div role="{{ $type === 'error' ? 'alert' : 'status' }}" {{ $attributes->class('rounded-lg border px-4 py-3 text-sm '.$styles[$type]) }}>{{ $slot }}</div>
