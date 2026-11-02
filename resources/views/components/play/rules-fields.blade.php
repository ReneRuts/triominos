{{-- Who plays and how it is scored. Shared by "nieuw toernooi" and "nieuw los spel". --}}
@props(['friends'])
<div class="space-y-6"
    x-data="{ timeLimit: {{ old('time_limit_enabled') ? 'true' : 'false' }}, guests: @js(array_values(array_filter((array) old('guests', []), fn ($n) => $n !== null && $n !== ''))) }">

    <x-ui.card title="Spelers">
        <p class="mb-3 text-sm text-stone-500">Jij speelt altijd mee. Kies vrienden en/of voeg gasten zonder account toe (samen maximaal 5 medespelers).</p>

        @if ($friends->isEmpty())
            <p class="mb-3 text-sm text-stone-500">Je hebt nog geen vrienden. <a href="{{ route('friends.index') }}" class="font-medium text-blue-700 underline">Voeg er een toe</a> of speel met gasten.</p>
        @endif

        <div class="grid gap-2 sm:grid-cols-2">
            @foreach ($friends as $friend)
                <x-ui.checkbox name="players[]" :value="$friend->id" :label="$friend->username" :boxed="true"
                    :checked="in_array($friend->id, array_map('intval', (array) old('players', [])))" />
            @endforeach
        </div>
        @error('players') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror

        <div class="mt-5">
            <span class="mb-1 block text-sm font-medium text-stone-700">Gasten (zonder account)</span>
            <p class="mb-2 text-xs text-stone-500">Een gast kan niet inloggen. Hij of zij volgt de stand live met de link die je na het starten krijgt, en telt niet mee in de ranking.</p>
            <template x-for="(g, i) in guests" :key="i">
                <div class="mb-2 flex gap-2">
                    <input type="text" name="guests[]" x-model="guests[i]" maxlength="40" placeholder="Naam van de gast" aria-label="Naam van de gast"
                        class="min-w-0 flex-1 rounded-lg border border-stone-300 px-3 py-2 text-sm">
                    <button type="button" @click="guests.splice(i, 1)" class="rounded-lg border border-stone-300 px-3 text-sm text-stone-500 hover:bg-rose-50 hover:text-rose-600" aria-label="Verwijder gast">&times;</button>
                </div>
            </template>
            <button type="button" @click="guests.push('')" class="text-sm font-medium text-blue-700 hover:underline">+ Gast toevoegen</button>
            @error('guests') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>
    </x-ui.card>

    <x-ui.card title="Puntentelling">
        <div class="grid gap-3 sm:grid-cols-2">
            <x-ui.radio-card name="mode" value="add" title="Optellen (standaard)" description="3 + 4 + 1 = 8 punten" :checked="old('mode', 'add') === 'add'" />
            <x-ui.radio-card name="mode" value="multiply" title="Vermenigvuldigen" description="3 × 4 × 1 = 12 punten. Een 0 telt als 1." :checked="old('mode') === 'multiply'" />
        </div>
        <x-ui.checkbox name="is_public" class="mt-4" label="Meetellen in de openbare ranking"
            hint="Uitgevinkt is het privé: alleen de spelers zien de punten, ook in de ranking."
            :checked="old('_token') ? (bool) old('is_public') : true" />
    </x-ui.card>

    <x-ui.card>
        <x-ui.checkbox name="time_limit_enabled" x-model="timeLimit" label="Tijdslimiet per beurt"
            hint="Een speler heeft een beperkte tijd om te spelen. Daarna volgen eerst waarschuwingen en dan de gevolgen die je hieronder instelt."
            :checked="(bool) old('time_limit_enabled')" />

        <div class="mt-4 grid gap-4 sm:grid-cols-2" x-show="timeLimit" x-cloak>
            <x-ui.input name="turn_time_limit" type="number" label="Seconden per beurt" min="10" max="900" :value="60" />
            <x-ui.select name="warnings_allowed" label="Waarschuwingen per speler">
                @foreach ([0, 1, 2] as $n)
                    <option value="{{ $n }}" @selected((int) old('warnings_allowed', 1) === $n)>{{ $n }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.input name="timeout_penalty_points" type="number" label="Punten aftrekken als de tijd om is" min="0" max="100" :value="5" hint="0 = geen puntenaftrek." />
            <x-ui.checkbox name="timeout_draw_tile" class="self-end pb-2" label="Speler moet een steen bijnemen" hint="Telt als steen gepakt (-5)."
                :checked="old('_token') ? (bool) old('timeout_draw_tile') : true" />
        </div>
    </x-ui.card>
</div>
