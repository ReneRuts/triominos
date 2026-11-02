{{-- A running standalone spel in a list. --}}
@props(['game'])
<a href="{{ route('games.show', $game) }}" class="block rounded-2xl border border-stone-200 bg-white p-4 shadow-sm transition hover:border-blue-500">
    <div class="flex items-start justify-between gap-2">
        <h3 class="font-medium text-stone-900">{{ $game->title() }}</h3>
        <x-ui.badge color="blue">{{ $game->mode->label() }}</x-ui.badge>
    </div>
    <p class="mt-1 text-sm text-stone-500">{{ $game->players->map(fn ($p) => $p->name())->implode(', ') }}</p>
    <p class="mt-2 text-xs text-stone-400">Gestart {{ $game->created_at->diffForHumans() }}</p>
</a>
