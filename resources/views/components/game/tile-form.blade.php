@props(['game', 'events', 'drawsInTurn'])
@php
    $tiles = $events->where('type', \App\Enums\ScoreEventType::Tile);
    $hasTriple = $tiles->contains(fn ($e) => (int) $e->digit_a === (int) $e->digit_b && (int) $e->digit_b === (int) $e->digit_c);
@endphp
<form method="POST" action="{{ route('events.store', $game) }}"
    x-data="tileForm(@js(['mode' => $game->mode->value, 'firstStone' => $tiles->isEmpty(), 'tripleDone' => $hasTriple]))"
    x-init="$nextTick(() => $refs.a.focus())">
    @csrf
    <input type="hidden" name="type" value="tile">
    <input type="hidden" name="player_id" :value="playerId">

    <div class="flex flex-wrap items-end gap-x-6 gap-y-4">
        {{-- Phone: digits and button share one row. From sm up the wrapper disappears and the button moves to the end of the row. --}}
        <div class="flex items-end gap-3 sm:contents">
            <div>
                <span class="mb-1 block text-sm font-medium text-stone-700">Cijfers van de steen</span>
                <div class="flex gap-2">
                    @foreach (['a', 'b', 'c'] as $d)
                        <input type="text" name="digit_{{ $d }}" x-model="{{ $d }}" x-ref="{{ $d }}" maxlength="1"
                            inputmode="numeric" pattern="[0-5]" placeholder="-" autocomplete="off" aria-label="Cijfer {{ strtoupper($d) }}"
                            @focus="$event.target.select()" @click="$event.target.select()"
                            @input="setDigit('{{ $d }}', $event)" @keydown.backspace="back('{{ $d }}', $event)"
                            class="h-12 w-12 rounded-lg border border-stone-300 text-center text-lg font-semibold focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-200 sm:w-14">
                    @endforeach
                </div>
            </div>

            <x-ui.button class="shrink-0 whitespace-nowrap sm:order-last sm:ml-auto" x-bind:disabled="!valid()">Steen noteren</x-ui.button>
        </div>

        <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="bridge" value="1" x-model="bridge" class="rounded border-stone-300 text-blue-700"> Brug (+40)
            </label>
            <label class="flex items-center gap-2">Zeshoeken
                <select name="hexagons" x-model="hexagons" class="rounded-lg border border-stone-300 px-2 py-1">
                    <option value="0">0</option>
                    <option value="1">1 (+50)</option>
                    <option value="2">2 (+60)</option>
                    <option value="3">3 (+70)</option>
                </select>
            </label>
            <label class="flex items-center gap-2">Handmatig totaal
                <input type="number" name="manual_points" x-model="manual" placeholder="auto" title="Laat leeg om de site te laten rekenen"
                    class="w-24 rounded-lg border border-stone-300 px-2 py-1">
            </label>
        </div>
    </div>

    <div class="mt-4 rounded-xl bg-stone-50 p-3" x-show="lines().length || manual !== ''" x-cloak>
        <template x-for="l in lines()" :key="l.label">
            <div class="flex justify-between text-sm text-stone-600">
                <span x-text="l.label"></span>
                <span class="tabular-nums" x-text="(l.points >= 0 ? '+' : '') + l.points"></span>
            </div>
        </template>
        @if ($drawsInTurn > 0)
            <div class="flex justify-between text-sm text-stone-600">
                <span>Bijgenomen in deze beurt ({{ $drawsInTurn }}x)</span><span class="tabular-nums">-{{ $drawsInTurn * 5 }}</span>
            </div>
        @endif
        <div class="mt-1 flex justify-between border-t border-stone-200 pt-1 font-semibold">
            <span>Totaal <span class="font-normal text-stone-500" x-show="manual !== ''">(handmatig)</span>@if ($drawsInTurn > 0)<span class="font-normal text-stone-500"> voor de steen</span>@endif</span>
            <span class="tabular-nums" x-text="total()"></span>
        </div>
    </div>
</form>