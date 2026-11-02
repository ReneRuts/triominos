{{-- End of a spel: somebody emptied their stones, or the game is blocked. --}}
@props(['game', 'players'])
<x-ui.card x-data="{ open: {{ $errors->has('game') || $errors->has('winner_player_id') ? 'true' : 'false' }}, endedBy: '{{ old('ended_by', 'emptied') }}', winner: '{{ old('winner_player_id', '') }}' }">
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-stone-900">Spel afsluiten</h2>
        <button type="button" @click="open = !open" class="text-sm font-medium text-blue-700 hover:underline" x-text="open ? 'Sluiten' : 'Het spel is afgelopen'"></button>
    </div>

    <form method="POST" action="{{ route('games.finish', $game) }}" x-show="open" x-cloak class="mt-4 space-y-4">
        @csrf
        @error('winner_player_id') <x-ui.alert type="error">{{ $message }}</x-ui.alert> @enderror

        <div class="grid gap-3 sm:grid-cols-2">
            <x-ui.radio-card name="ended_by" value="emptied" x-model="endedBy" title="Een speler is al zijn stenen kwijt"
                description="25 bonus + waarde van de stenen die de anderen over hebben." />
            <x-ui.radio-card name="ended_by" value="blocked" x-model="endedBy" title="Spel is geblokkeerd"
                description="Laagste waarde wint: stenen van de anderen min je eigen stenen." />
        </div>

        <x-ui.select name="winner_player_id" label="Winnaar van het spel" x-model="winner" required class="sm:w-64">
            <option value="">Kies een speler</option>
            @foreach ($players as $p) <option value="{{ $p->id }}">{{ $p->name() }}</option> @endforeach
        </x-ui.select>

        <div>
            <span class="mb-1 block text-sm font-medium text-stone-700">Waarde van de stenen die nog over zijn</span>
            <div class="grid gap-2 sm:grid-cols-3">
                @foreach ($players as $p)
                    <label class="flex items-center justify-between gap-2 rounded-lg border border-stone-200 px-3 py-2 text-sm">
                        <span class="min-w-0 truncate" title="{{ $p->name() }}">{{ $p->name() }}</span>
                        <input type="number" name="remaining[{{ $p->id }}]" min="0" value="{{ old('remaining.'.$p->id, 0) }}"
                            :disabled="endedBy === 'emptied' && winner === '{{ $p->id }}'"
                            class="w-20 shrink-0 rounded border border-stone-300 px-2 py-1 text-right disabled:bg-stone-100">
                    </label>
                @endforeach
            </div>
            <p class="mt-1 text-xs text-stone-500">Tel de cijfers op de stenen op (ook bij vermenigvuldigen). De winnaar van een lege hand heeft 0 over.</p>
        </div>

        <x-ui.button>Spel afsluiten</x-ui.button>
    </form>
</x-ui.card>
