@php
    $o = config('site.owner');
    $a = config('site.authority');
@endphp
<x-layout.doc title="Privacybeleid" :description="'Hoe ' .
    config('site.name') .
    ' omgaat met je persoonsgegevens: welke gegevens we bewaren, waarom, hoelang en welke rechten je hebt.'" :updated="config('site.policies_updated')">

    <h2>1. Wie is verantwoordelijk?</h2>
    <p>{{ config('site.name') }} wordt beheerd door:</p>
    <p>{{ $o['name'] }}<br>E-mail: {{ $o['email'] }}</p>
    <p>Wij zijn verantwoordelijk voor de verwerking van je persoonsgegevens zoals beschreven in dit beleid, in
        overeenstemming met de Algemene Verordening Gegevensbescherming (AVG).</p>

    <h2>2. Welke gegevens verwerken we?</h2>
    <table>
        <thead>
            <tr>
                <th>Gegevens</th>
                <th>Waarvoor</th>
                <th>Grondslag</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Gebruikersnaam, e-mailadres en een versleuteld wachtwoord</td>
                <td>Een account aanmaken en inloggen. Je gebruikersnaam is zichtbaar voor andere gebruikers (vrienden
                    zoeken, spelers, ranking).</td>
                <td>Uitvoering van de overeenkomst (het gebruik van de site)</td>
            </tr>
            <tr>
                <td>Vriendschappen</td>
                <td>Spelers kiezen voor toernooien en spellen en de ranking filteren.</td>
                <td>Uitvoering van de overeenkomst</td>
            </tr>
            <tr>
                <td>Toernooien, spellen, scores en spelersnamen (ook van gasten)</td>
                <td>De stand bijhouden, terugkijken en de ranking opbouwen.</td>
                <td>Uitvoering van de overeenkomst</td>
            </tr>
            <tr>
                <td>IP-adres, browsergegevens en tijdstip van laatste activiteit (sessie)</td>
                <td>Je ingelogd houden en de site beveiligen tegen misbruik.</td>
                <td>Gerechtvaardigd belang (veiligheid en werking van de site)</td>
            </tr>
            <tr>
                <td>Je cookiekeuze</td>
                <td>Onthouden wat je over cookies hebt gekozen.</td>
                <td>Wettelijke verplichting / gerechtvaardigd belang</td>
            </tr>
        </tbody>
    </table>
    <p>We verwerken geen bijzondere categorieën van persoonsgegevens en we gebruiken je gegevens niet voor
        geautomatiseerde besluitvorming of direct marketing.</p>

    <h2>3. Gasten zonder account</h2>
    <p>Voor gasten bewaren we enkel de naam die de spelmaker invult en de scores van dat spel. Een live link toont die
        gegevens aan iedereen die de link of het spel-ID heeft. Deel de link dus alleen met mensen die mogen meekijken.
        De spelmaker kan een nieuwe link aanmaken, waarna de oude niet meer werkt.</p>

    <h2>4. Met wie delen we gegevens?</h2>
    <p>We verkopen je gegevens niet. We delen gegevens alleen als de wet dat verplicht.</p>

    <h2>5. Hoelang bewaren we gegevens?</h2>
    <ul>
        <li>Accountgegevens en spelgegevens: zolang je account bestaat. Vraag je verwijdering van je account aan, dan
            wissen we je gegevens, behalve wat we wettelijk moeten bewaren.</li>
        <li>Sessiegegevens: verwijderd na afloop van de sessie, uiterlijk na 2 uur inactiviteit.</li>
        <li>Cookiekeuze: 180 dagen.</li>
    </ul>

    <h2>6. Beveiliging</h2>
    <p>Wachtwoorden worden versleuteld opgeslagen en de verbinding met de site verloopt versleuteld. Toch is geen enkel
        systeem volledig onfeilbaar. Merk je een probleem op, laat het ons dan weten via {{ $o['email'] }}.</p>

    <h2>7. Jouw rechten</h2>
    <p>Je hebt het recht om je gegevens in te zien, te laten verbeteren of te laten wissen, de verwerking te beperken of
        er bezwaar tegen te maken, en je gegevens in een bruikbaar formaat op te vragen. Stuur daarvoor een e-mail naar
        {{ $o['email'] }}. We antwoorden binnen een maand en kunnen vragen om je identiteit te bevestigen.</p>
    <p>Ben je het niet eens met hoe we met je gegevens omgaan? Dan kun je een klacht indienen bij de <a
            href="{{ $a['url'] }}" rel="noopener">{{ $a['name'] }}</a>.</p>

    <h2>8. Cookies</h2>
    <p>Lees in ons <a href="{{ route('pages.cookies') }}">cookiebeleid</a> welke cookies we gebruiken en hoe je je keuze
        wijzigt.</p>

    <h2>9. Kinderen</h2>
    <p>De site is bedoeld voor een algemeen publiek. Ben je jonger dan 13, vraag dan eerst toestemming aan een ouder of
        voogd voordat je een account aanmaakt.</p>

    <h2>10. Wijzigingen</h2>
    <p>We kunnen dit beleid aanpassen. De datum bovenaan geeft aan wanneer het voor het laatst is bijgewerkt.</p>
</x-layout.doc>
