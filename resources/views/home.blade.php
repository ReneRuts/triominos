<x-layout.app :title="auth()->check() ? 'Start' : null" robots="index,follow"
    :description="config('site.description')">

    @auth
        {{-- Logged in: start something or continue --}}
        <x-ui.page-header :title="'Hoi '.auth()->user()->username" subtitle="Wat gaan we spelen?" />

        <div class="grid gap-4 md:grid-cols-2">
            <a href="{{ route('tournaments.create') }}" class="group rounded-2xl bg-blue-800 p-6 text-white shadow-sm transition hover:bg-blue-700">
                <h2 class="text-xl font-semibold">Toernooi starten</h2>
                <p class="mt-2 text-sm text-blue-100">Met vaste spelers meerdere spellen na elkaar. De punten tellen op tot iemand het doel haalt.</p>
                <span class="mt-4 inline-block text-sm font-medium underline underline-offset-4">Nieuw toernooi &rarr;</span>
            </a>
            <a href="{{ route('games.create') }}" class="group rounded-2xl border border-stone-200 bg-white p-6 shadow-sm transition hover:border-blue-500">
                <h2 class="text-xl font-semibold text-stone-900">Los spel spelen</h2>
                <p class="mt-2 text-sm text-stone-600">Eén spel, geen toernooi. Zodra iemand zijn stenen kwijt is of het spel vastzit, is het klaar.</p>
                <span class="mt-4 inline-block text-sm font-medium text-blue-700 underline underline-offset-4">Nieuw spel &rarr;</span>
            </a>
        </div>

        @if ($activeTournaments->isNotEmpty() || $activeGames->isNotEmpty())
            <h2 class="mb-3 mt-10 text-sm font-medium uppercase tracking-wide text-stone-500">Verder spelen</h2>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($activeTournaments as $tournament) <x-tournament.card :tournament="$tournament" /> @endforeach
                @foreach ($activeGames as $game) <x-game.card :game="$game" /> @endforeach
            </div>
        @endif

        <div class="mt-10 grid gap-4 md:grid-cols-3">
            <x-ui.card title="Spelregels"><p class="text-sm text-stone-600">Hoe Triominos werkt en hoe dit scorebord telt.</p>
                <a class="mt-3 inline-block text-sm font-medium text-blue-700 underline" href="{{ route('pages.rules') }}">Lees de spelregels</a></x-ui.card>
            <x-ui.card title="Handleiding"><p class="text-sm text-stone-600">Stap voor stap een toernooi of spel bijhouden.</p>
                <a class="mt-3 inline-block text-sm font-medium text-blue-700 underline" href="{{ route('pages.guide') }}">Open de handleiding</a></x-ui.card>
            <x-ui.card title="Ranking"><p class="text-sm text-stone-600">Wie wint er het vaakst? Bekijk de stand met vrienden.</p>
                <a class="mt-3 inline-block text-sm font-medium text-blue-700 underline" href="{{ route('ranking.index') }}">Bekijk de ranking</a></x-ui.card>
        </div>
    @else
        {{-- Guest: explain, then offer login or live follow --}}
        <section class="grid items-center gap-10 py-6 lg:grid-cols-5">
            <div class="lg:col-span-3">
                <h1 class="text-4xl font-semibold tracking-tight text-stone-900 sm:text-5xl">Het scorebord voor je Triominos-avond</h1>
                <p class="mt-5 max-w-xl text-lg leading-relaxed text-stone-600">
                    Houd de punten bij van één spel of van een heel toernooi. Bonussen, bijnemen en passen
                    worden voor je uitgerekend. Wie niet aan tafel zit, kijkt live mee.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <x-ui.button size="lg" :href="route('register')">Gratis account maken</x-ui.button>
                    <x-ui.button size="lg" variant="secondary" :href="route('login')">Inloggen</x-ui.button>
                </div>
            </div>

            <x-ui.card id="live" title="Een spel live volgen" class="lg:col-span-2">
                <p class="mb-4 text-sm text-stone-500">Geen account nodig. Plak de link die je van de spelmaker kreeg, of typ het spel-ID.</p>
                <form method="POST" action="{{ route('join') }}" class="space-y-3">
                    @csrf
                    <x-ui.input name="code" label="Link of spel-ID" placeholder="ABCD-2345 of https://..." autocomplete="off" required />
                    <x-ui.button variant="secondary" class="w-full">Live meekijken</x-ui.button>
                </form>
            </x-ui.card>
        </section>

        <section class="mt-14 grid gap-6 md:grid-cols-3">
            <div>
                <h2 class="font-semibold text-stone-900">Toernooien</h2>
                <p class="mt-2 text-sm leading-relaxed text-stone-600">Meerdere spellen met dezelfde spelers. Wie als eerste het doel haalt, beslist het toernooi op totaal aantal punten.</p>
            </div>
            <div>
                <h2 class="font-semibold text-stone-900">Losse spellen</h2>
                <p class="mt-2 text-sm leading-relaxed text-stone-600">Maar tijd voor één potje? Speel een los spel, ook met gasten zonder account.</p>
            </div>
            <div>
                <h2 class="font-semibold text-stone-900">Ranking en vrienden</h2>
                <p class="mt-2 text-sm leading-relaxed text-stone-600">Voeg vrienden toe en zie wie de meeste toernooien en spellen wint.</p>
            </div>
        </section>

        <section class="mt-14">
            <h2 class="text-2xl font-semibold tracking-tight text-stone-900">Zo werkt het</h2>
            <ol class="mt-6 grid gap-4 md:grid-cols-3">
                @foreach ([
                    ['1', 'Kies je spelers', 'Vrienden met een account, of gasten die je gewoon een naam geeft.'],
                    ['2', 'Vul de stenen in', 'Typ de drie cijfers van elke steen. Bonussen, bijnemen en passen rekent de site uit.'],
                    ['3', 'Sluit het spel af', 'Geef aan wie zijn stenen kwijt is. De winnaar en de stand staan meteen vast.'],
                ] as [$n, $title, $text])
                    <li class="rounded-2xl border border-stone-200 bg-white p-5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-800">{{ $n }}</span>
                        <h3 class="mt-3 font-semibold text-stone-900">{{ $title }}</h3>
                        <p class="mt-1 text-sm leading-relaxed text-stone-600">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
        </section>

        <section class="mt-14 grid gap-4 md:grid-cols-2">
            <x-ui.card title="Nieuw met Triominos?">
                <p class="text-sm leading-relaxed text-stone-600">Lees hoe het spel werkt, hoe je punten scoort en wanneer een spel afgelopen is.</p>
                <x-ui.button class="mt-4" variant="secondary" :href="route('pages.rules')">Spelregels</x-ui.button>
            </x-ui.card>
            <x-ui.card title="Hoe gebruik ik dit scorebord?">
                <p class="text-sm leading-relaxed text-stone-600">Een stap-voor-stap handleiding van account tot eindstand.</p>
                <x-ui.button class="mt-4" variant="secondary" :href="route('pages.guide')">Handleiding</x-ui.button>
            </x-ui.card>
        </section>

        <section class="mt-14">
            <h2 class="text-2xl font-semibold tracking-tight text-stone-900">Veelgestelde vragen</h2>
            <div class="mt-4 divide-y divide-stone-200 rounded-2xl border border-stone-200 bg-white">
                @foreach ([
                    ['Is het gratis?', 'Ja. Je maakt een account aan en kan meteen spelen.'],
                    ['Moeten mijn medespelers een account hebben?', 'Nee. Gasten krijgen een naam en volgen de stand via een link. Alleen spelers met een account komen in de ranking.'],
                    ['Wat is het verschil tussen een toernooi en een los spel?', 'Een toernooi bestaat uit meerdere spellen: de punten tellen op en wie het doel haalt, beslist het toernooi. Een los spel is één spel zonder doelscore.'],
                    ['Kan iemand anders meekijken?', 'Ja, via de live link of het korte spel-ID. Meekijkers kunnen niets aanpassen.'],
                ] as [$q, $a])
                    <details class="group px-5 py-4">
                        <summary class="cursor-pointer font-medium text-stone-900">{{ $q }}</summary>
                        <p class="mt-2 text-sm leading-relaxed text-stone-600">{{ $a }}</p>
                    </details>
                @endforeach
            </div>
        </section>
    @endauth
</x-layout.app>
