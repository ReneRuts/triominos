{{-- Actions for the creator. One confirmation at a time, so there are never more than three buttons. --}}
@props(['tournament'])
<div x-data="{ pending: null }" class="flex flex-wrap items-center gap-2">
    <div x-show="pending === null" class="flex flex-wrap items-center gap-2">
        @if ($tournament->isActive())
            <x-ui.button type="button" variant="secondary" size="sm" @click="pending = 'finish'">Toernooi afronden</x-ui.button>
        @endif
        <x-ui.visibility-form :action="route('tournaments.visibility', $tournament)" :is-public="$tournament->is_public" />
        <x-ui.button type="button" variant="ghost" size="sm" class="text-rose-600 hover:bg-rose-50 hover:text-rose-700" @click="pending = 'delete'">Toernooi verwijderen</x-ui.button>
    </div>

    @if ($tournament->isActive())
        <form x-show="pending === 'finish'" x-cloak method="POST" action="{{ route('tournaments.finish', $tournament) }}" class="flex flex-wrap items-center gap-2">
            @csrf
            <span class="text-sm text-stone-600">Toernooi afronden en de winnaar vastleggen?</span>
            <x-ui.button size="sm">Ja, afronden</x-ui.button>
            <x-ui.button type="button" variant="secondary" size="sm" @click="pending = null">Annuleren</x-ui.button>
        </form>
    @endif

    <form x-show="pending === 'delete'" x-cloak method="POST" action="{{ route('tournaments.destroy', $tournament) }}" class="flex flex-wrap items-center gap-2">
        @csrf @method('DELETE')
        <span class="text-sm text-stone-600">Het hele toernooi met alle spellen en scores wordt gewist.</span>
        <x-ui.button variant="danger" size="sm">Ja, verwijder alles</x-ui.button>
        <x-ui.button type="button" variant="secondary" size="sm" @click="pending = null">Annuleren</x-ui.button>
    </form>
</div>