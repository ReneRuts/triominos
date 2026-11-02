<x-layout.app title="Toernooien">
    <x-ui.page-header title="Mijn toernooien" subtitle="Meerdere spellen met dezelfde spelers. De punten tellen op.">
        <x-ui.button :href="route('tournaments.create')">Nieuw toernooi</x-ui.button>
    </x-ui.page-header>

    <h2 class="mb-3 text-sm font-medium uppercase tracking-wide text-stone-500">Lopend</h2>
    @if ($active->isEmpty())
        <x-ui.empty>Geen lopende toernooien. Start een nieuw toernooi om punten bij te houden.</x-ui.empty>
    @else
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($active as $tournament) <x-tournament.card :tournament="$tournament" /> @endforeach
        </div>
    @endif

    <h2 class="mb-3 mt-10 text-sm font-medium uppercase tracking-wide text-stone-500">Afgerond</h2>
    <x-ui.card :padding="false" class="overflow-hidden">
        @forelse ($finished as $tournament)
            <x-tournament.row :tournament="$tournament" />
        @empty
            <p class="px-5 py-4 text-sm text-stone-500">Nog geen afgeronde toernooien.</p>
        @endforelse
    </x-ui.card>
</x-layout.app>
