@php
    $players = $game->players;
    $subtitle = $game->mode->label()
        .($tournament ? ' · onderdeel van een toernooi' : ' · '.($game->is_public ? 'openbaar in de ranking' : 'privé'))
        .($game->turn_time_limit ? ' · '.$game->turn_time_limit.'s per beurt, '.$game->warnings_allowed.' waarschuwing(en)' : '');
    $canReopen = $isCreator && ! $game->isActive() && ($game->isStandalone()
        || ($tournament->isActive() && $tournament->lastGame()?->id === $game->id && ! $tournament->activeGame()));
@endphp
<x-layout.app :title="$game->title()">
    <div @if ($game->isActive()) x-data="gameWatcher(@js(route('games.version', $game)), @js($game->version()))" @endif>

        @if ($game->isActive())
            <div x-show="stale" x-cloak class="mb-4 flex items-center justify-between rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                Iemand anders heeft iets aangepast.
                <x-ui.button type="button" size="sm" onclick="window.location.reload()">Ververs</x-ui.button>
            </div>
        @endif

        <x-ui.page-header :title="$game->title()" :subtitle="$subtitle"
            :back="$tournament ? route('tournaments.show', $tournament) : route('games.index')"
            :back-label="$tournament ? $tournament->name : 'Losse spellen'">
            @if ($canReopen)
                <form method="POST" action="{{ route('games.reopen', $game) }}">@csrf
                    <x-ui.button size="sm">Spel heropenen</x-ui.button>
                </form>
            @endif
            @if ($game->isStandalone())
                @can('manage', $game)
                    <x-ui.visibility-form :action="route('games.visibility', $game)" :is-public="$game->is_public" />
                @endcan
            @endif
            @can('delete', $game)
                <form method="POST" action="{{ route('games.destroy', $game) }}">@csrf @method('DELETE')
                    <x-ui.confirm-button label="Verwijder spel" confirm="Alles wissen?" variant="ghost" />
                </form>
            @endcan
        </x-ui.page-header>

        @if (! $game->isActive())
            <div class="mb-4 rounded-2xl border border-blue-200 bg-blue-50 p-5">
                <p class="text-sm text-blue-800">Afgerond op {{ $game->finished_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }} · {{ $game->ended_by?->label() }}</p>
                <p class="text-lg font-semibold text-blue-900">Winnaar: {{ $game->winner?->name() }}</p>
            </div>
        @endif

        <x-game.scoreboard :snapshot="$snapshot" />

        <div class="mt-6 space-y-6">
            @if ($needsSetup)
                @if ($canScore)
                    <x-game.setup-form :action="route('games.start', $game)" :players="$players" :needs-order="true"
                        title="Zitvolgorde en beginspeler" submit="Spel starten" />
                @else
                    <x-ui.alert>Dit spel is nog niet gestart.</x-ui.alert>
                @endif
            @endif

            @if ($canScore && $game->isActive() && $game->isStarted())
                <x-game.score-entry :game="$game" :players="$players" :events="$events" :draws-in-turn="$drawsInTurn" />
                <x-game.finish-form :game="$game" :players="$players" />
            @endif

            @if ($game->isStarted())
                <x-game.turns-list :turns="$turns" :game="$game" :title="'Verloop van '.$game->title()" :editable="$canScore && $game->isActive()" :is-creator="$isCreator" />
            @endif

            @if ($game->isStandalone() && $game->isActive() && ($canScore || $isCreator))
                <x-game.share-link :model="$game" :regenerate="$isCreator ? route('games.link', $game) : null" />
            @endif
        </div>
    </div>
</x-layout.app>