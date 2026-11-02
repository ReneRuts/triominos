{{-- Everything for entering points in a running spel. Owns the "which player" state shared by its children. --}}
@props(['game', 'players', 'events', 'drawsInTurn'])
@php $current = $game->currentPlayer(); @endphp
<x-ui.card x-data="{ playerId: {{ $current?->id ?? $players->first()->id }} }">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <h2 class="font-semibold text-stone-900">Punten invullen</h2>
        <div class="flex items-center gap-2 text-sm">
            <label for="playerId" class="text-stone-500">Speler</label>
            <select id="playerId" x-model.number="playerId" class="rounded-lg border border-stone-300 px-3 py-1.5">
                @foreach ($players as $p)
                    <option value="{{ $p->id }}">{{ $p->name() }}{{ $current?->id === $p->id ? ' (aan de beurt)' : '' }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if ($errors->hasAny(['digit_a', 'digit_b', 'digit_c', 'hexagons', 'manual_points', 'player_id']))
        <x-ui.alert type="error" class="mb-4">
            {{ collect(['digit_a', 'digit_b', 'digit_c', 'hexagons', 'manual_points', 'player_id'])->map(fn ($k) => $errors->first($k))->filter()->first() }}
        </x-ui.alert>
    @endif

    @if ($game->turn_time_limit)
        <x-game.turn-timer :game="$game" :players="$players" />
    @endif

    <x-game.tile-form :game="$game" :events="$events" :draws-in-turn="$drawsInTurn" />
    <x-game.draw-pass :game="$game" :draws-in-turn="$drawsInTurn" />
</x-ui.card>
