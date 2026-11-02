<x-layout.doc title="Cookiebeleid"
    :description="'Welke cookies '.config('site.name').' gebruikt, waarvoor, hoelang ze blijven staan en hoe je je keuze aanpast.'"
    :updated="config('site.policies_updated')">

    <h2>Wat zijn cookies?</h2>
    <p>Cookies zijn kleine bestanden die een website op je toestel opslaat. Ze zorgen er bijvoorbeeld voor dat je ingelogd blijft of dat de site jouw keuzes onthoudt.</p>

    <h2>Jouw keuze</h2>
    <p>Bij je eerste bezoek kies je uit drie knoppen: <strong>Weigeren</strong>, <strong>Alleen noodzakelijk</strong> en <strong>Accepteren</strong>. Weigeren en alleen noodzakelijk betekent dat we enkel de cookies gebruiken die nodig zijn om de site te laten werken. Met Accepteren sta je daarnaast optionele cookies toe.</p>
    <p>Je keuze kun je op elk moment wijzigen: <button type="button" class="font-medium text-blue-700 underline underline-offset-2" onclick="window.dispatchEvent(new CustomEvent('cookie-settings'))">open de cookie-instellingen</button>. Je kunt cookies ook verwijderen via de instellingen van je browser.</p>

    <h2>Noodzakelijke cookies</h2>
    <p>Deze cookies zijn nodig om de site te laten werken. Daarvoor vragen we geen toestemming.</p>
    <table>
        <thead><tr><th>Naam</th><th>Doel</th><th>Bewaartermijn</th></tr></thead>
        <tbody>
            @foreach (config('cookies.necessary') as $cookie)
                <tr><td><code>{{ $cookie['name'] }}</code></td><td>{{ $cookie['purpose'] }}</td><td>{{ $cookie['duration'] }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <h2>Optionele cookies</h2>
    @if (count(config('cookies.optional')))
        <p>Deze cookies plaatsen we alleen als je Accepteren kiest.</p>
        <table>
            <thead><tr><th>Naam</th><th>Aanbieder</th><th>Doel</th><th>Bewaartermijn</th></tr></thead>
            <tbody>
                @foreach (config('cookies.optional') as $cookie)
                    <tr><td><code>{{ $cookie['name'] }}</code></td><td>{{ $cookie['provider'] ?? '-' }}</td><td>{{ $cookie['purpose'] }}</td><td>{{ $cookie['duration'] }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Op dit moment plaatsen we geen optionele cookies. Voegen we die later toe, dan vragen we opnieuw om je keuze.</p>
    @endif

    <h2>Vragen?</h2>
    <p>Lees ons <a href="{{ route('pages.privacy') }}">privacybeleid</a> of mail naar {{ config('site.owner.email') }}.</p>
</x-layout.doc>
