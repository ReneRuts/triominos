<x-layout.app title="Ranking">
    <x-ui.page-header title="Ranking" subtitle="Wie wint het vaakst? Toernooien en losse spellen hebben elk hun eigen ranking." />

    <form method="GET" action="{{ route('ranking.index') }}" x-data="{ scope: '{{ $scope }}' }">
        <x-ui.card class="mb-6">
            <div class="flex flex-wrap gap-2">
                @foreach (['global' => 'Iedereen', 'friends' => 'Mijn vrienden', 'selected' => 'Kies vrienden'] as $value => $label)
                    <label class="cursor-pointer rounded-lg border px-4 py-2 text-sm"
                        :class="scope === '{{ $value }}' ? 'border-blue-600 bg-blue-50 text-blue-800' : 'border-stone-200 text-stone-600 hover:bg-stone-50'">
                        <input type="radio" name="scope" value="{{ $value }}" x-model="scope" class="sr-only"
                            @change="$nextTick(() => { if (scope !== 'selected') $el.form.submit() })">
                        {{ $label }}
                    </label>
                @endforeach
            </div>

            <div x-show="scope === 'selected'" x-cloak class="mt-4">
                @if ($friends->isEmpty())
                    <p class="text-sm text-stone-500">Je hebt nog geen vrienden om te kiezen.</p>
                @else
                    <div class="grid gap-2 sm:grid-cols-3">
                        <input type="hidden" name="me" value="0">
                        <x-ui.checkbox name="me" :label="'Ikzelf ('.auth()->user()->username.')'" :boxed="true" :checked="$includeMe" />
                        @foreach ($friends as $friend)
                            <x-ui.checkbox name="friends[]" :value="$friend->id" :label="$friend->username" :boxed="true" :checked="in_array($friend->id, $selected)" />
                        @endforeach
                    </div>
                    <x-ui.button class="mt-3">Toon ranking</x-ui.button>
                @endif
            </div>
        </x-ui.card>
    </form>

    <div class="space-y-6">
        <x-ranking.table title="Toernooien" :rows="$tournamentRanking" empty="Nog geen afgeronde toernooien in deze selectie." />
        <x-ranking.table title="Losse spellen" :rows="$gameRanking" empty="Nog geen afgeronde losse spellen in deze selectie." />
    </div>

    <h2 class="mb-3 mt-10 text-sm font-medium uppercase tracking-wide text-stone-500">Alle toernooien terugkijken</h2>
    <x-ui.card :padding="false" class="overflow-hidden">
        @forelse ($tournaments as $tournament)
            <x-tournament.row :tournament="$tournament" />
        @empty
            <p class="px-5 py-4 text-sm text-stone-500">Geen afgeronde toernooien.</p>
        @endforelse
    </x-ui.card>
    <div class="mt-4">{{ $tournaments->links() }}</div>

    <h2 class="mb-3 mt-10 text-sm font-medium uppercase tracking-wide text-stone-500">Alle losse spellen terugkijken</h2>
    <x-ui.card :padding="false" class="overflow-hidden">
        @forelse ($games as $game)
            <x-game.row :game="$game" />
        @empty
            <p class="px-5 py-4 text-sm text-stone-500">Geen afgeronde losse spellen.</p>
        @endforelse
    </x-ui.card>
    <div class="mt-4">{{ $games->links() }}</div>
</x-layout.app>
