<x-layout.app title="Losse spellen">
    <x-ui.page-header title="Mijn losse spellen" subtitle="Eén spel, zonder toernooi. Spellen van een toernooi vind je bij dat toernooi.">
        <x-ui.button :href="route('games.create')">Nieuw los spel</x-ui.button>
    </x-ui.page-header>

    <h2 class="mb-3 text-sm font-medium uppercase tracking-wide text-stone-500">Lopend</h2>
    @if ($active->isEmpty())
        <x-ui.empty>Geen lopende spellen.</x-ui.empty>
    @else
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($active as $game) <x-game.card :game="$game" /> @endforeach
        </div>
    @endif

    <h2 class="mb-3 mt-10 text-sm font-medium uppercase tracking-wide text-stone-500">Afgerond</h2>
    <x-ui.card :padding="false" class="overflow-hidden">
        @forelse ($finished as $game)
            <x-game.row :game="$game" />
        @empty
            <p class="px-5 py-4 text-sm text-stone-500">Nog geen afgeronde spellen.</p>
        @endforelse
    </x-ui.card>
</x-layout.app>
