@php
    $players = $tournament->players;
    $nextNumber = ($lastGame?->number ?? 0) + 1;
    $subtitle = $tournament->mode->label().' · doel '.$tournament->target_score.' punten · '
        .($tournament->is_public ? 'openbaar in de ranking' : 'privé')
        .($tournament->turn_time_limit ? ' · '.$tournament->turn_time_limit.'s per beurt, '.$tournament->warnings_allowed.' waarschuwing(en)' : '');
@endphp
<x-layout.app :title="$tournament->name">
    <div @if ($tournament->isActive()) x-data="gameWatcher(@js(route('tournaments.version', $tournament)), @js($snapshot['version']))" @endif>

        @if ($tournament->isActive())
            <div x-show="stale" x-cloak class="mb-4 flex items-center justify-between rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                Iemand anders heeft iets aangepast.
                <x-ui.button type="button" size="sm" onclick="window.location.reload()">Ververs</x-ui.button>
            </div>
        @endif

        <x-ui.page-header :title="$tournament->name" :subtitle="$subtitle" :back="route('tournaments.index')" back-label="Toernooien">
            @if ($isCreator)
                <x-tournament.header-actions :tournament="$tournament" />
            @endif
        </x-ui.page-header>
        <p class="-mt-4 mb-6 text-xs text-stone-400">Spelmaker: {{ $tournament->creator->username }}</p>

        @if ($tournament->isActive() && $tournament->target_reached_at)
            <x-ui.alert class="mb-4">Iemand heeft {{ $tournament->target_score }} punten gehaald. Speel het spel uit, daarna wint wie de meeste punten heeft.</x-ui.alert>
        @endif

        @if (! $tournament->isActive())
            <div class="mb-4 rounded-2xl border border-blue-200 bg-blue-50 p-5">
                <p class="text-sm text-blue-800">Afgerond op {{ $tournament->finished_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p>
                <p class="text-lg font-semibold text-blue-900">
                    Winnaar: {{ $players->where('is_winner', true)->map(fn ($p) => $p->name().' ('.$p->final_points.')')->implode(' & ') }}
                </p>
            </div>
        @endif

        <x-game.scoreboard :snapshot="$snapshot" />

        @if ($canPlay)
            <form method="POST" action="{{ route('tournaments.target', $tournament) }}" class="mt-3 flex flex-wrap items-center justify-end gap-2 text-sm">
                @csrf
                <label for="target_score" class="text-stone-500">Doelscore</label>
                <input id="target_score" type="number" name="target_score" min="50" max="5000" step="10" value="{{ old('target_score', $tournament->target_score) }}"
                    class="w-24 rounded-lg border border-stone-300 px-2 py-1.5">
                <x-ui.button variant="secondary" size="sm">Opslaan</x-ui.button>
                @error('target_score') <span class="w-full text-right text-rose-600">{{ $message }}</span> @enderror
            </form>
        @endif

        <div class="mt-6 space-y-6">
            {{-- A spel is running: go play --}}
            @if ($activeGame)
                <x-ui.card>
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="font-semibold text-stone-900">{{ $activeGame->title() }} loopt</h2>
                            <p class="text-sm text-stone-500">Aan de beurt: {{ $activeGame->currentPlayer()?->name() }}</p>
                        </div>
                        <x-ui.button :href="route('games.show', $activeGame)">Ga naar het spel</x-ui.button>
                    </div>
                </x-ui.card>

            {{-- No spel running: start the next one --}}
            @elseif ($tournament->isActive() && $canPlay)
                @if ($tournament->target_reached_at)
                    <x-ui.alert>Het doel is bereikt, dus er komen geen nieuwe spellen meer. Verhoog de doelscore om verder te spelen, of laat de spelmaker het toernooi afronden om de winnaar vast te leggen.</x-ui.alert>
                @else
                    <x-game.setup-form :action="route('tournaments.games.store', $tournament)" :players="$players" :needs-order="$firstGame"
                        :submit="'Start spel '.$nextNumber" :title="$firstGame ? 'Zitvolgorde en beginspeler' : 'Nieuw spel'" />
                @endif

                @if ($isCreator && $lastGame && $lastGame->status === \App\Enums\Status::Finished)
                    <form method="POST" action="{{ route('games.reopen', $lastGame) }}">@csrf
                        <button class="text-sm text-stone-500 hover:underline">{{ $lastGame->title() }} heropenen</button>
                    </form>
                @endif
            @endif

            @php $finishedGames = $tournament->games->where('status', \App\Enums\Status::Finished)->sortByDesc('number'); @endphp
            @if ($finishedGames->isNotEmpty())
                <x-ui.card title="Gespeelde spellen" :padding="false" class="overflow-hidden">
                    @foreach ($finishedGames as $g)
                        <a href="{{ route('games.show', $g) }}" class="flex items-center justify-between gap-3 border-b border-stone-100 px-5 py-3 text-sm last:border-0 hover:bg-stone-50">
                            <span class="font-medium">{{ $g->title() }}</span>
                            <span class="truncate text-stone-500">Winnaar {{ $players->firstWhere('id', $g->winner_player_id)?->name() }} · {{ $g->ended_by?->label() }}</span>
                        </a>
                    @endforeach
                </x-ui.card>
            @endif

            @if ($tournament->isActive() && ($canPlay || $isCreator))
                <x-game.share-link :model="$tournament" :regenerate="$isCreator ? route('tournaments.link', $tournament) : null" />
            @endif
        </div>
    </div>
</x-layout.app>