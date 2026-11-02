{{-- Long-form public page (rules, guide, privacy, cookies): indexable, narrow, readable. --}}
@props(['title', 'description' => null, 'updated' => null])
<x-layout.app :title="$title" :description="$description" robots="index,follow" width="max-w-3xl" type="article">
    <h1 class="text-3xl font-semibold tracking-tight text-stone-900">{{ $title }}</h1>
    @if ($updated)
        <p class="mt-1 text-sm text-stone-500">Laatst bijgewerkt: {{ $updated }}</p>
    @endif
    <article class="doc mt-6">{{ $slot }}</article>
</x-layout.app>
