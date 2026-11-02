{{-- Normal page: header + content area. Pass :minimal="true" for a header without the menu (live view). --}}
@props([
    'title' => null,
    'description' => null,
    'robots' => 'noindex,follow',
    'canonical' => null,
    'image' => null,
    'type' => 'website',
    'minimal' => false,
    'width' => 'max-w-6xl',
])
<x-layout.base :title="$title" :description="$description" :robots="$robots" :canonical="$canonical" :image="$image" :type="$type">
    <x-layout.header :minimal="$minimal" />

    <main id="main" class="mx-auto w-full {{ $width }} flex-1 px-4 py-8">
        @if (session('status'))
            <x-ui.alert type="success" class="mb-6">{{ session('status') }}</x-ui.alert>
        @endif

        @if (isset($errors) && $errors->has('game'))
            <x-ui.alert type="error" class="mb-6">{{ $errors->first('game') }}</x-ui.alert>
        @endif

        {{ $slot }}
    </main>
</x-layout.base>
