<x-layout.app title="Nieuw toernooi" width="max-w-3xl">
    <x-ui.page-header title="Nieuw toernooi" subtitle="Je speelt meerdere spellen. Wie het doel haalt, beslist het toernooi." :back="route('tournaments.index')" back-label="Toernooien" />

    <form method="POST" action="{{ route('tournaments.store') }}" class="space-y-6">
        @csrf
        <x-ui.card title="Het toernooi">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.input name="name" label="Naam" :value="$defaultName" maxlength="80" required class="sm:col-span-2" />
                <x-ui.input name="target_score" type="number" label="Toernooi eindigt bij (punten)" :value="400" min="50" max="5000" step="10"
                    hint="Je kan de doelscore tijdens het toernooi nog aanpassen." />
            </div>
        </x-ui.card>

        <x-play.rules-fields :friends="$friends" />

        <div class="flex justify-end gap-2">
            <x-ui.button variant="secondary" :href="route('tournaments.index')">Annuleren</x-ui.button>
            <x-ui.button>Toernooi starten</x-ui.button>
        </div>
    </form>
</x-layout.app>
