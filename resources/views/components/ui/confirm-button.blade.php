{{-- Two-step button for destructive actions. Place it inside a <form>. --}}
@props(['label', 'confirm' => 'Zeker weten?', 'variant' => 'secondary', 'size' => 'sm'])
<span x-data="{ sure: false }" class="contents">
    <x-ui.button type="button" :variant="$variant" :size="$size" x-show="!sure" @click="sure = true">{{ $label }}</x-ui.button>
    <x-ui.button type="submit" variant="danger" :size="$size" x-show="sure" x-cloak>{{ $confirm }}</x-ui.button>
    <x-ui.button type="button" variant="secondary" :size="$size" x-show="sure" x-cloak @click="sure = false">Annuleren</x-ui.button>
</span>
