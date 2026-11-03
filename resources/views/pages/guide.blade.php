<x-layout.doc title="Handleiding: zo houd je de score bij"
    description="Stap voor stap een Triominos-toernooi of los spel bijhouden: spelers kiezen, stenen invullen, een spel afsluiten, gasten laten meekijken en de ranking bekijken."
    :updated="config('site.policies_updated')">

    <p>Deze handleiding loopt van account tot eindstand. Weet je nog niet hoe Triominos werkt? Lees dan eerst de <a href="{{ route('pages.rules') }}">spelregels</a>.</p>

    <h2>Eerst: toernooi of los spel?</h2>
    <table>
        <thead><tr><th></th><th>Toernooi</th><th>Los spel</th></tr></thead>
        <tbody>
            <tr><td>Aantal spellen</td><td>Meerdere</td><td>Eén</td></tr>
            <tr><td>Doelscore</td><td>Ja, bijvoorbeeld 400</td><td>Nee</td></tr>
            <tr><td>Winnaar</td><td>Meeste punten over alle spellen</td><td>Winnaar van dat spel</td></tr>
            <tr><td>Ranking</td><td>Ranking van toernooien</td><td>Ranking van losse spellen</td></tr>
        </tbody>
    </table>

    <h2>1. Account maken en vrienden toevoegen</h2>
    <p><a href="{{ route('register') }}">Maak een account</a> met een gebruikersnaam en e-mailadres. Voeg bij <em>Vrienden</em> je medespelers toe op gebruikersnaam; zij moeten het verzoek accepteren. Spelers zonder account kun je later als gast toevoegen.</p>

    <h2>2. Een toernooi of los spel starten</h2>
    <ol>
        <li>Kies <em>Toernooi starten</em> of <em>Los spel starten</em> (<a href="{{ route('pages.rules') }}#toernooi"><em>meer over toernooien of losse spellen</em></a>).</li>
        <li>Selecteer vrienden en voeg eventueel gasten toe. Je speelt altijd zelf mee, samen met maximaal 5 anderen.</li>
        <li>Kies optellen of vermenigvuldigen en stel bij een toernooi de doelscore in.</li>
        <li>Zet desnoods een tijdslimiet per beurt aan.</li>
    </ol>

    <h2>3. Zitvolgorde en beginspeler</h2>
    <p>Zet de spelers in de volgorde waarin ze aan tafel zitten, met de klok mee, en kies wie begint (<a href="{{ route('pages.rules') }}#start"><em>wie mag beginnen?</em></a>). De site houdt daarna bij wie aan de beurt is. Bij een toernooi doe je dit één keer; bij elk volgend spel kies je alleen nog wie begint.</p>

    <h2>4. Stenen invullen</h2>
    <p>Tijdens het spel vul je de scores in via het formulier op de spelpagina. Bekijk de afbeelding hieronder voor een voorbeeld van een brug en een zeshoek.</p>
    
    <figure class="my-6">
        <img src="{{ asset('images/brug-zeshoek-uitleg.webp') }}" alt="Voorbeeld van een brug maken en een zeshoek sluiten" class="w-full max-w-3xl rounded-lg shadow-md">
        <figcaption class="text-sm text-stone-500 mt-2">Een brug sluiten levert 40 bonuspunten op, een zeshoek 50.</figcaption>
    </figure>

    <ul>
        <li><strong>Steen gelegd?</strong> Typ de drie cijfers in. De app berekent automatisch de basiswaarde.</li>
        <li><strong>Bonus?</strong> Vink <em>Brug</em> aan of kies het aantal <em>zeshoeken</em> als de gelegde steen deze figuur sluit (<a href="{{ route('pages.rules') }}#punten"><em>zie Punten en bonussen</em></a>).</li>
        <li><strong>Niet leggen?</strong> Klik op <em>Steen pakken</em>. Let op: na drie keer pakken in dezelfde beurt volgt automatisch <em>Passen</em> (<a href="{{ route('pages.rules') }}#bijnemen"><em>lees meer over bijnemen en passen</em></a>).</li>
        <li><strong>Huisregels?</strong> Klopt de automatische telling niet met jullie eigen spelregels aan tafel? Vul dan simpelweg het gewenste aantal in bij het veld <em>handmatig totaal</em>.</li>
        <li><strong>Foutje?</strong> Verwijder de ingevulde score. De app berekent de bonussen van alle latere stenen dan volautomatisch opnieuw!</li>
    </ul>

    <h2>5. Een spel afsluiten</h2>
    <p>Is een speler zijn stenen kwijt, of is het spel geblokkeerd? Kies <em>Het spel is afgelopen</em>, geef de winnaar aan en vul de waarde in van de stenen die de verliezers nog op hun plankje hebben (<a href="{{ route('pages.rules') }}#einde"><em>zie Einde van een spel</em></a>). De eindpunten worden direct berekend.</p>

    <h2>6. Een toernooi afronden</h2>
    <p>Haalt iemand de doelscore? Dan speel je het lopende spel nog uit. Daarna kan de spelmaker het toernooi afronden: de speler met de meeste punten over alle spellen wint. Willen jullie toch verder spelen? Verhoog dan simpelweg de doelscore.</p>

    <h2>7. Gasten live laten meekijken</h2>
    <p>Bij elk lopend toernooi of los spel vind je een link en een kort spel-ID (zoals ABCD-2345). Wie die invult op de <a href="{{ route('home') }}">startpagina</a> ziet de stand live, zonder account en zonder iets te kunnen aanpassen. Een gast die meespeelde kan achteraf een account maken en het afgeronde spel alsnog aan zichzelf koppelen.</p>

    <h2>8. De ranking</h2>
    <p>De ranking toont wie de meeste toernooien en losse spellen won. Filter op iedereen, je vrienden of een zelfgekozen groep. Een privé toernooi of spel telt alleen mee voor de spelers zelf.</p>

    <h2>Veelvoorkomende vragen</h2>
    <h3>Kan ik een score vastzetten?</h3>
    <p>Ja, de spelmaker kan een score vastzetten. Die kan dan niet meer worden verwijderd tot ze weer is losgemaakt.</p>
    <h3>Kan ik een afgesloten spel heropenen?</h3>
    <p>De spelmaker kan het laatst afgesloten spel van een toernooi heropenen zolang er geen ander spel loopt. Een afgerond los spel kan ook altijd heropend worden.</p>
    <h3>Wie ziet mijn spellen?</h3>
    <p>Lopende spellen zijn alleen zichtbaar voor de spelers en de spelmaker. Afgeronde openbare spellen zijn zichtbaar voor iedereen met een account.</p>
</x-layout.doc>