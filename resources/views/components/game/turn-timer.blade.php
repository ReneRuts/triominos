@props(['game', 'players'])
<div class="mb-5 rounded-xl border border-stone-200 bg-stone-50 p-4" x-data="turnTimer({{ $game->turn_time_limit }})">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <span class="text-xs uppercase tracking-wide text-stone-500">Tijd voor de beurt</span>
            <div class="text-3xl font-bold tabular-nums" :class="expired ? 'text-rose-600' : (left <= 10 && running ? 'text-amber-600' : '')" x-text="label"></div>
        </div>
        <div class="flex gap-2">
            <x-ui.button type="button" size="sm" @click="start()">Start</x-ui.button>
            <x-ui.button type="button" variant="secondary" size="sm" @click="reset()">Opnieuw</x-ui.button>
        </div>
    </div>

    <div x-show="expired" x-cloak class="mt-3 flex flex-wrap items-center justify-between gap-2 rounded-lg bg-rose-50 px-3 py-2 text-sm text-rose-800">
        <span>De tijd is om. Noteer het gevolg voor de gekozen speler.</span>
        <form method="POST" action="{{ route('events.timeout', $game) }}" @submit="reset()">
            @csrf
            <input type="hidden" name="player_id" :value="playerId">
            <x-ui.button variant="danger" size="sm">Tijd om noteren</x-ui.button>
        </form>
    </div>

    <p class="mt-3 text-xs text-stone-500">
        Waarschuwingen gebruikt:
        @foreach ($players as $p){{ $p->name() }} {{ $p->warnings_used }}/{{ $game->warnings_allowed }}@if (! $loop->last), @endif @endforeach
        @if ($game->timeout_penalty_points) · daarna -{{ $game->timeout_penalty_points }} punten @endif
        @if ($game->timeout_draw_tile) · en een steen bijnemen @endif
    </p>
</div>
