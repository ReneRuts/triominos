<x-layout.app title="Nieuw los spel" width="max-w-3xl">
    <x-ui.page-header title="Nieuw los spel" subtitle="Eén spel. Het is voorbij zodra iemand zijn stenen kwijt is of het spel vastzit." :back="route('games.index')" back-label="Losse spellen" />

    <form method="POST" action="{{ route('games.store') }}" class="space-y-6">
        @csrf
        <x-ui.card title="Het spel">
            <x-ui.input name="name" label="Naam (optioneel)" maxlength="80" placeholder="Bijvoorbeeld: Vrijdagavond" />
        </x-ui.card>

        <x-play.rules-fields :friends="$friends" />

        <div class="flex justify-end gap-2">
            <x-ui.button variant="secondary" :href="route('games.index')">Annuleren</x-ui.button>
            <x-ui.button>Spel starten</x-ui.button>
        </div>
    </form>
</x-layout.app>
