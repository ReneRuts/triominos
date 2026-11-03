{{-- Live link + short game ID for guests, for a toernooi or a standalone spel. --}}
@props(['model', 'regenerate' => null])
@php $url = route('live.show', $model->share_token); @endphp
<x-ui.card title="Live meekijken voor gasten" x-data="copyLink">
    <p class="mb-3 text-sm text-stone-500">
        Wie deze link heeft, of het spel-ID <span class="font-mono font-semibold text-stone-700">{{ $model->joinCodeLabel() }}</span>
        invult op de startpagina, kan de punten live volgen zonder account. Alleen kijken, niet aanpassen.
        Zodra het afgerond is stopt het live volgen en blijft enkel de eindstand staan.
    </p>
    <div class="flex flex-wrap gap-2">
        <input x-ref="url" readonly value="{{ $url }}" onclick="this.select()" aria-label="Live link"
            class="min-w-0 flex-1 rounded-lg border border-stone-300 bg-stone-50 px-3 py-2 text-sm">
        <x-ui.button type="button" @click="copy()" x-text="copied ? 'Gekopieerd' : 'Kopieer'" />
        @if ($regenerate)
            <form method="POST" action="{{ $regenerate }}" onsubmit="return confirm('De oude link werkt dan niet meer. Doorgaan?')">@csrf
                <x-ui.button variant="secondary">Nieuwe link</x-ui.button>
            </form>
        @endif
    </div>
</x-ui.card>