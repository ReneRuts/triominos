{{-- A finished toernooi in a list. --}}
@props(['tournament'])
<a href="{{ route('tournaments.show', $tournament) }}" class="block border-b border-stone-100 px-5 py-3 text-sm last:border-0 hover:bg-stone-50">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div>
            <span class="font-medium text-stone-900">{{ $tournament->name }}</span>
            <span class="text-stone-400">· {{ $tournament->finished_at?->timezone(config('app.timezone'))->format('d/m/Y') }}</span>
            <x-ui.badge>{{ $tournament->mode->label() }}</x-ui.badge>
        </div>
        <span class="text-stone-600">Winnaar: <span class="font-medium">{{ $tournament->players->where('is_winner', true)->map(fn ($p) => $p->name())->implode(' & ') }}</span></span>
    </div>
    <p class="mt-1 text-xs text-stone-500">{{ $tournament->players->sortByDesc('final_points')->map(fn ($p) => $p->name().' '.$p->final_points)->implode(' · ') }}</p>
</a>
