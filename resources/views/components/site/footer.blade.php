<footer class="mt-16 border-t border-stone-200 bg-white">
    <div class="mx-auto grid max-w-6xl grid-cols-2 gap-8 px-4 py-12 sm:grid-cols-2 lg:grid-cols-4">

        <div class="col-span-2 lg:col-span-2">
            <x-site.logo size="16" />
            <p class="mt-4 max-w-sm text-sm leading-relaxed text-stone-600">
                Een gratis scorebord voor Triominos. Houd de punten bij van een los spel of een heel toernooi,
                en laat wie niet aan tafel zit live meekijken.
            </p>
        </div>

        <nav aria-label="Spelen">
            <h2 class="text-sm font-semibold text-stone-900">Spelen</h2>
            <ul class="mt-4 space-y-2 text-sm text-stone-600">
                <li><a class="hover:text-blue-700" href="{{ route('tournaments.create') }}">Toernooi starten</a></li>
                <li><a class="hover:text-blue-700" href="{{ route('games.create') }}">Los spel starten</a></li>
                <li><a class="hover:text-blue-700" href="{{ route('ranking.index') }}">Ranking</a></li>
                <li><a class="hover:text-blue-700" href="{{ route('home') }}#live">Live meekijken</a></li>
            </ul>
        </nav>

        <nav aria-label="Informatie">
            <h2 class="text-sm font-semibold text-stone-900">Informatie</h2>
            <ul class="mt-4 space-y-2 text-sm text-stone-600">
                <li><a class="hover:text-blue-700" href="{{ route('pages.rules') }}">Spelregels</a></li>
                <li><a class="hover:text-blue-700" href="{{ route('pages.guide') }}">Handleiding</a></li>
                <li><a class="hover:text-blue-700" href="{{ route('pages.privacy') }}">Privacybeleid</a></li>
                <li><a class="hover:text-blue-700" href="{{ route('pages.cookies') }}">Cookiebeleid</a></li>
                <li>
                    <button type="button" class="hover:text-blue-700"
                        onclick="window.dispatchEvent(new CustomEvent('cookie-settings'))">Cookie-instellingen</button>
                </li>
            </ul>
        </nav>
    </div>

    <div class="border-t border-stone-100">
        <div
            class="mx-auto flex max-w-6xl flex-col gap-2 px-4 py-5 text-xs text-stone-500 sm:flex-row sm:justify-between">
            <p class="text-center md:text-left">Gemaakt door <a class="hover:underline" href="https://reneruts.be"
                    target="_blank">René Ruts</a></p>
            <p class="text-center md:text-left">&copy; {{ date('Y') }} {{ config('site.name') }}. Niet verbonden aan
                de makers van Triominos.</p>
            <p class="text-center md:text-left">Triominos is een merk van zijn eigenaar. Deze site is een onafhankelijk
                hulpmiddel.</p>
        </div>
    </div>
</footer>
