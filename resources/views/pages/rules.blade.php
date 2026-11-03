<x-layout.doc title="Spelregels van Triominos"
    description="De spelregels van Triominos in het kort: stenen, beurten, bijnemen en passen, punten en bonussen, en wanneer een spel of toernooi afgelopen is."
    :updated="config('site.policies_updated')">

    <x-ui.alert type="info" class="not-prose mb-6">
        Dit zijn de regels zoals dit scorebord ze telt. Triominos wordt thuis soms net anders gespeeld:
        kijk in de speldoos voor de officiële regels van jouw versie en spreek aan tafel af waar je van afwijkt.
    </x-ui.alert>

    <nav aria-label="Inhoud" class="mb-8 rounded-xl border border-stone-200 bg-white p-4 text-sm">
        <p class="mb-2 font-semibold text-stone-900">Inhoud</p>
        <ol class="list-decimal space-y-1 pl-5">
            @foreach (['doel' => 'Doel van het spel', 'materiaal' => 'Materiaal', 'start' => 'Het spel beginnen', 'beurt' => 'Een beurt', 'punten' => 'Punten en bonussen', 'bijnemen' => 'Bijnemen en passen', 'einde' => 'Einde van een spel', 'toernooi' => 'Toernooi of los spel', 'varianten' => 'Varianten'] as $id => $label)
                <li><a href="#{{ $id }}">{{ $label }}</a></li>
            @endforeach
        </ol>
    </nav>

    <h2 id="doel">Doel van het spel</h2>
    <p>Triominos is een legspel met driehoekige stenen. Je legt stenen tegen elkaar zodat de cijfers op de randen
        overeenkomen, en scoort punten voor elke steen en voor bijzondere zetten. Wie het eerst zijn stenen kwijt is,
        wint het spel.</p>

    <h2 id="materiaal">Materiaal</h2>
    <p>Er zijn 56 driehoekige stenen. Op elke hoek staat een cijfer van 0 tot en met 5, en elke combinatie komt precies
        één keer voor. Het spel is voor 2 tot 6 spelers.</p>

    <h2 id="start">Het spel beginnen</h2>
    <ol>
        <li>Alle stenen gaan met het cijfer naar beneden of in een zak op tafel zodat je de cijfers niet kan zien. Elke
            speler trekt een aantal stenen: <br><b>9</b> bij twee spelers; <br><b>7</b> bij drie of vier spelers;<br>
            <b>6</b>
            bij vijf of zes spelers.</li>
        <li>De speler met de hoogste drie-dezelfde-steen (bijvoorbeeld 5-5-5) begint. Heeft niemand zo'n steen, dan
            begint de speler met de steen met de hoogste waarde.</li>
        <li>Dit scorebord vraagt bij elk spel wie begint (de speler met het hoogste blokje) en in welke volgorde jullie
            aan tafel zitten, met de klok mee.</li>
    </ol>

    <h2 id="beurt">Een beurt</h2>
    <p>Je legt een steen aan een steen die al ligt. Elke zijde van jouw steen moet aansluiten bij dezelfde cijfers van
        de steen ernaast. Daarna is de volgende speler aan de beurt.</p>

    <h2 id="punten">Punten en bonussen</h2>
    <p>Elke steen die je legt is zoveel punten waard als de som van zijn drie cijfers. Een 3-4-1 is dus 8 punten. Bij de
        variant vermenigvuldigen tel je het product, waarbij een 0 als 1 telt.</p>
    <table>
        <thead>
            <tr>
                <th>Wat</th>
                <th>Punten</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Steen leggen</td>
                <td>som van de drie cijfers</td>
            </tr>
            <tr>
                <td>Eerste steen met drie dezelfde cijfers in een spel (0-0-0 tot 5-5-5)</td>
                <td>+10</td>
            </tr>
            <tr>
                <td>0-0-0 als allereerste steen van het spel</td>
                <td>de steen telt in totaal 40</td>
            </tr>
            <tr>
                <td>Brug sluiten (zoals in de speldoos beschreven)</td>
                <td>+40</td>
            </tr>
            <tr>
                <td>Zeshoek (zes stenen rond één punt)</td>
                <td>+50</td>
            </tr>
            <tr>
                <td>Twee zeshoeken met één steen</td>
                <td>+60</td>
            </tr>
            <tr>
                <td>Drie zeshoeken met één steen</td>
                <td>+70</td>
            </tr>
            <tr>
                <td>Steen bijnemen</td>
                <td>-5 per steen</td>
            </tr>
            <tr>
                <td>Passen</td>
                <td>-10</td>
            </tr>
        </tbody>
    </table>
    <p>Bonussen tellen op: een steen die een brug sluit én een zeshoek maakt, krijgt beide bonussen.</p>

    <h2 id="bijnemen">Bijnemen en passen</h2>
    <p>Kun je geen steen leggen, dan pak je een steen uit de pot. Dat kost 5 punten per steen. Je mag maximaal drie keer
        bijnemen in één beurt. Kun je daarna nog steeds niet leggen, dan pas je en verlies je nog eens 10 punten. In
        totaal is dat dus min 25 punten voor een beurt zonder te kunnen spelen. Past een steen die je bijneemt wel, dan
        leg je hem meteen.</p>

    <h2 id="einde">Einde van een spel</h2>
    <p>Een spel is afgelopen op één van twee manieren:</p>
    <ul>
        <li><strong>Stenen kwijt:</strong> een speler legt zijn laatste steen. Hij krijgt 25 punten plus de waarde van
            alle stenen die de anderen nog hebben.</li>
        <li><strong>Geblokkeerd:</strong> niemand kan nog leggen en de pot is leeg. De speler met de laagste restwaarde
            wint en krijgt het verschil: de stenen van de anderen minus zijn eigen stenen.</li>
    </ul>
    <p>Alleen de winnaar van een spel scoort de eindpunten.</p>

    <h2 id="toernooi">Toernooi of los spel</h2>
    <p>In een <strong>toernooi</strong> speel je meerdere spellen met dezelfde spelers. De punten van alle spellen
        tellen op. Zodra iemand de doelscore, standaard 400, haalt wordt het lopende spel nog
        uitgespeeld. Daarna wint de speler met de meeste punten. Je kunt de doelscore tijdens het toernooi nog
        aanpassen.</p>
    <p>Een <strong>los spel</strong> is één spel. Het is voorbij zodra iemand zijn stenen kwijt is of het spel geblokkeerd is,
        de speler met de meeste punten wint.</p>

    <h2 id="varianten">Varianten</h2>
    <ul>
        <li><strong>Vermenigvuldigen:</strong> een steen telt het product van zijn cijfers, een 0 telt als 1.</li>
        <li><strong>Tijdslimiet:</strong> elke beurt mag maximaal een afgesproken aantal seconden duren. Eerst volgen
            waarschuwingen, daarna puntenaftrek en eventueel een steen bijnemen.</li>
    </ul>

    <p class="mt-8">Klaar om te spelen? Lees de <a href="{{ route('pages.guide') }}">handleiding</a> of <a
            href="{{ route('register') }}">maak een account</a>.</p>
</x-layout.doc>
