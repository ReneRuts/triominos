@props(['title' => null, 'padding' => true])
<section {{ $attributes->class('rounded-2xl border border-stone-200 bg-white shadow-sm') }}>
    @if ($title)
        <h2 class="border-b border-stone-100 px-5 py-3 font-semibold text-stone-900">{{ $title }}</h2>
    @endif
    <div @class(['p-5' => $padding])>{{ $slot }}</div>
</section>
