<x-layout.app :title="$snapshot['game']['name'].' (live)'" robots="noindex,nofollow" :minimal="true" width="max-w-3xl">
    <div x-data="liveGame(@js(route('live.data', $token)), @js($snapshot), @js(url('/claim/'.$token)))">

        <div class="mb-4">
            <h1 class="text-2xl font-semibold tracking-tight text-stone-900" x-text="s.game.name"></h1>
            <p class="text-sm text-stone-500">
                <span x-text="s.game.mode === 'multiply' ? 'Vermenigvuldigen' : 'Optellen'"></span>
                <template x-if="isTournament"><span> · toernooi · doel <span x-text="s.game.target_score"></span> punten</span></template>
                <template x-if="!isTournament"><span> · los spel</span></template>
                <template x-if="!finished && s.current_game"><span> · <span x-text="s.current_game"></span> bezig</span></template>
            </p>
        </div>

        <div x-show="error" x-cloak class="mb-3 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-800">Verbinding kwijt, we proberen opnieuw...</div>

        <div x-show="finished" x-cloak class="mb-4 rounded-2xl border border-blue-200 bg-blue-50 p-4 text-blue-900">
            <p class="font-semibold">Afgelopen. Winnaar: <span x-text="s.players.filter(p => p.is_winner).map(p => p.username + ' (' + p.total + ')').join(' & ')"></span></p>
            <p class="mt-1 text-sm">Dit is de eindstand, het scherm ververst niet meer.</p>

            <template x-if="s.players.some(p => p.is_guest)">
                <div class="mt-3 border-t border-blue-200 pt-3 text-sm">
                    <p class="mb-2">Speelde je mee als gast? Maak een account en dit komt in je ranking.</p>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="p in s.players.filter(p => p.is_guest)" :key="p.id">
                            <a :href="claimBase + '/' + p.id" class="rounded-lg bg-blue-700 px-3 py-1.5 font-medium text-white hover:bg-blue-800" x-text="'Ik ben ' + p.username"></a>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        <div x-show="!finished && s.game.target_reached" x-cloak class="mb-4 rounded-lg bg-blue-50 px-3 py-2 text-sm text-blue-900">
            Het doel is bereikt. Nog even het spel uitspelen.
        </div>

        {{-- Totals as cards --}}
        <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
            <template x-for="p in s.players" :key="p.id">
                <div class="rounded-2xl border bg-white p-4" :class="p.is_current && !finished ? 'border-blue-500 ring-2 ring-blue-100' : 'border-stone-200'">
                    <div class="flex items-center gap-1 text-sm text-stone-500">
                        <span x-show="p.is_current && !finished" class="inline-block h-2 w-2 rounded-full bg-blue-500"></span>
                        <span x-text="p.username"></span>
                    </div>
                    <div class="text-3xl font-bold tabular-nums" :class="s.leaders.includes(p.username) ? 'text-blue-700' : ''" x-text="p.total"></div>
                    <div class="mt-2 h-1.5 rounded-full bg-stone-100" x-show="s.game.target_score">
                        <div class="h-1.5 rounded-full bg-blue-500" :style="'width:' + progress(p) + '%'"></div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Spellen table --}}
        <section class="mb-4 overflow-x-auto rounded-2xl border border-stone-200 bg-white">
            <table class="w-full table-fixed text-sm" :style="'min-width:' + (7 + s.players.length * 5.5) + 'rem'">
                <colgroup>
                    <col style="width: 7rem">
                    <template x-for="p in s.players" :key="p.id"><col></template>
                </colgroup>
                <thead>
                    <tr class="border-b border-stone-200 text-left">
                        <th class="px-4 py-2 text-xs font-medium uppercase tracking-wide text-stone-500">Spel</th>
                        <template x-for="p in s.players" :key="p.id"><th class="px-3 py-2 text-right font-semibold"><div class="truncate" :title="p.username" x-text="p.username"></div></th></template>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="g in s.games" :key="g.id">
                        <tr class="border-b border-stone-100 last:border-0">
                            <td class="truncate px-4 py-2 text-stone-500" x-text="g.title + (g.status === 'active' ? ' (bezig)' : '')"></td>
                            <template x-for="p in s.players" :key="p.id">
                                <td class="px-3 py-2 text-right tabular-nums" :class="g.winner_player_id === p.id ? 'font-semibold text-blue-700' : ''" x-text="g.totals[p.id]"></td>
                            </template>
                        </tr>
                    </template>
                </tbody>
            </table>
        </section>

        {{-- Latest turns, with every bonus and penalty --}}
        <section class="overflow-hidden rounded-2xl border border-stone-200 bg-white">
            <h2 class="border-b border-stone-100 px-4 py-3 font-semibold text-stone-900">Laatste beurten</h2>
            <template x-if="s.recent.length === 0"><p class="px-4 py-3 text-sm text-stone-500">Nog niets genoteerd.</p></template>
            <ul>
                <template x-for="t in s.recent" :key="t.id">
                    <li class="border-b border-stone-100 px-4 py-3 text-sm last:border-0">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <span class="font-medium" x-text="t.player"></span>
                                <span class="text-xs text-stone-400" x-text="' · ' + t.game + ' · ' + t.at"></span>
                                <span x-show="t.open" class="text-xs text-amber-600"> · beurt bezig</span>
                            </div>
                            <span class="whitespace-nowrap font-semibold tabular-nums" :class="t.total < 0 ? 'text-rose-600' : 'text-blue-700'" x-text="(t.total > 0 ? '+' : '') + t.total"></span>
                        </div>
                        <template x-for="(l, i) in t.lines" :key="i">
                            <div class="flex justify-between gap-3 text-xs text-stone-500">
                                <span x-text="l.text"></span>
                                <span class="tabular-nums" x-text="(l.points > 0 ? '+' : '') + l.points"></span>
                            </div>
                        </template>
                    </li>
                </template>
            </ul>
        </section>

        <p class="mt-4 text-center text-xs text-stone-400" x-text="finished ? 'Afgelopen. Alleen kijken.' : 'Dit scherm ververst automatisch. Alleen kijken.'"></p>
    </div>
</x-layout.app>
