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
        <li>Kies <em>Toernooi starten</em> of <em>Los spel starten</em>.</li>
        <li>Selecteer vrienden en voeg eventueel gasten toe. Je speelt altijd zelf mee, samen met maximaal 5 anderen.</li>
        <li>Kies optellen of vermenigvuldigen en stel bij een toernooi de doelscore in.</li>
        <li>Zet desnoods een tijdslimiet per beurt aan.</li>
    </ol>

    <h2>3. Zitvolgorde en beginspeler</h2>
    <p>Zet de spelers in de volgorde waarin ze aan tafel zitten, met de klok mee, en kies wie begint. De site houdt daarna bij wie aan de beurt is. Bij een toernooi doe je dit één keer; bij elk volgend spel kies je alleen nog wie begint.</p>

    <h2>4. Stenen invullen</h2>
    <ul>
        <li>Typ de drie cijfers van de steen die gelegd wordt. Je ziet meteen hoe de punten tot stand komen.</li>
        <li>Vink <em>Brug</em> aan of kies het aantal zeshoeken als dat van toepassing is.</li>
        <li>Kan iemand niet leggen? Kies <em>Steen pakken</em>. Na drie keer volgt <em>Passen</em>.</li>
        <li>Klopt de automatische telling niet met jullie huisregels? Vul dan een handmatig totaal in.</li>
        <li>Verkeerd ingevuld? Verwijder de score; de bonussen van latere stenen worden opnieuw berekend.</li>
    </ul>

    <h2>5. Een spel afsluiten</h2>
    <p>Is een speler zijn stenen kwijt, of zit het spel vast? Kies <em>Het spel is afgelopen</em>, geef de winnaar aan en vul de waarde van de stenen die nog over zijn. De eindpunten worden automatisch bijgeschreven.</p>

    <h2>6. Een toernooi afronden</h2>
    <p>Haalt iemand de doelscore, dan speel je het lopende spel nog uit. Daarna kan de spelmaker het toernooi afronden: de speler met de meeste punten wint. Wil je toch verder spelen? Verhoog dan de doelscore.</p>

    <h2>7. Gasten live laten meekijken</h2>
    <p>Bij elk lopend toernooi of los spel vind je een link en een kort spel-ID (zoals ABCD-2345). Wie die invult op de <a href="{{ route('home') }}">startpagina</a> ziet de stand live, zonder account en zonder iets te kunnen aanpassen. Een gast die meespeelde kan achteraf een account maken en het afgeronde spel aan zichzelf koppelen.</p>

    <h2>8. De ranking</h2>
    <p>De ranking toont wie de meeste toernooien en losse spellen won. Filter op iedereen, je vrienden of een zelfgekozen groep. Een privé toernooi of spel telt alleen mee voor de spelers zelf.</p>

    <h2>Veelvoorkomende vragen</h2>
    <h3>Kan ik een score vastzetten?</h3>
    <p>Ja, de spelmaker kan een score vastzetten. Die kan dan niet meer worden verwijderd tot ze weer is losgemaakt.</p>
    <h3>Kan ik een afgesloten spel heropenen?</h3>
    <p>De spelmaker kan het laatst afgesloten spel van een toernooi heropenen zolang er geen ander spel loopt. Een afgerond los spel kan ook heropend worden.</p>
    <h3>Wie ziet mijn spellen?</h3>
    <p>Lopende spellen zijn alleen zichtbaar voor de spelers en de spelmaker. Afgeronde openbare spellen zijn zichtbaar voor iedereen met een account.</p>
</x-layout.doc>
