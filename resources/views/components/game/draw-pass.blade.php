@props(['game', 'drawsInTurn'])
<div class="mt-5 flex flex-wrap items-center gap-2 border-t border-stone-100 pt-4">
    <span class="mr-2 text-sm text-stone-500">Kan niet spelen? Bijgenomen: <span class="font-semibold">{{ $drawsInTurn }}/3</span></span>

    <form method="POST" action="{{ route('events.store', $game) }}">
        @csrf
        <input type="hidden" name="type" value="{{ $drawsInTurn < 3 ? 'draw' : 'pass' }}">
        <input type="hidden" name="player_id" :value="playerId">
        <x-ui.button variant="secondary">{{ $drawsInTurn < 3 ? 'Steen pakken (-5)' : 'Passen (-10)' }}</x-ui.button>
    </form>

    @if ($drawsInTurn >= 3)
        <span class="text-xs text-stone-400">3 stenen bijgenomen: speel een steen of pas.</span>
    @endif
</div>
