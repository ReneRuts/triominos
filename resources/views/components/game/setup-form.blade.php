{{-- Who has the highest stone (always), and the seating order (first spel only). --}}
@props(['action', 'players', 'needsOrder' => false, 'submit' => 'Start spel', 'title' => 'Wie begint?'])
<x-ui.card :title="$title">
    <form method="POST" action="{{ $action }}" class="space-y-5"
        @if ($needsOrder) x-data="seating(@js($players->map(fn ($p) => ['id' => $p->id, 'name' => $p->name()])->values()))" @endif>
        @csrf

        @if ($needsOrder)
            @error('order') <x-ui.alert type="error">{{ $message }}</x-ui.alert> @enderror
            <div>
                <p class="mb-3 text-sm text-stone-500">Zet de spelers in de volgorde waarin ze aan tafel zitten, met de klok mee. In die volgorde gaan de beurten en staan de kolommen in de score-tabel.</p>
                <ol class="space-y-2">
                    <template x-for="(p, i) in list" :key="p.id">
                        <li class="flex items-center gap-2 rounded-lg border border-stone-200 px-3 py-2 text-sm">
                            <span class="w-5 text-stone-400" x-text="i + 1"></span>
                            <span class="min-w-0 flex-1 truncate font-medium" x-text="p.name"></span>
                            <input type="hidden" name="order[]" :value="p.id">
                            <button type="button" @click="up(i)" :disabled="i === 0" class="rounded border border-stone-300 px-2 py-1 disabled:opacity-30" aria-label="Omhoog">&uarr;</button>
                            <button type="button" @click="down(i)" :disabled="i === list.length - 1" class="rounded border border-stone-300 px-2 py-1 disabled:opacity-30" aria-label="Omlaag">&darr;</button>
                        </li>
                    </template>
                </ol>
            </div>
        @endif

        <x-ui.select name="starting_player_id" label="Wie begint? (de speler met het hoogste blokje)" required class="sm:w-72">
            <option value="">Kies een speler</option>
            @foreach ($players as $p)
                <option value="{{ $p->id }}" @selected((int) old('starting_player_id') === $p->id)>{{ $p->name() }}</option>
            @endforeach
        </x-ui.select>

        <x-ui.button>{{ $submit }}</x-ui.button>
    </form>
</x-ui.card>
