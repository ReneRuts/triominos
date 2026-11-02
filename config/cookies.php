<?php

use Illuminate\Support\Str;

/*
| Cookie consent. There are two levels: "necessary" (always) and "all" (after Accepteren).
| The cookie policy page lists what is in this file, so keep it up to date when you add scripts.
|
| Scripts that need consent go in resources/views/components/cookie/scripts.blade.php
*/

return [
    'name' => 'cookie_consent',
    'version' => 1,   // raise this number to ask everybody again (e.g. after adding a new tracker)
    'days' => 180,

    // Strictly necessary: no consent needed.
    'necessary' => [
        [
            'name' => Str::slug((string) env('APP_NAME', 'laravel')).'-session',
            'purpose' => 'Houdt je sessie bij zodat je ingelogd blijft terwijl je de site gebruikt.',
            'duration' => 'Tot je de browser sluit of na '.((int) env('SESSION_LIFETIME', 120) / 60).' uur inactiviteit',
        ],
        [
            'name' => 'XSRF-TOKEN',
            'purpose' => 'Beveiliging: beschermt formulieren tegen misbruik door andere websites.',
            'duration' => 'Dezelfde duur als de sessie',
        ],
        [
            'name' => 'remember_web_*',
            'purpose' => 'Alleen als je "Ingelogd blijven" aanvinkt: onthoudt dat je bent ingelogd.',
            'duration' => '5 jaar of tot je uitlogt',
        ],
        [
            'name' => 'cookie_consent',
            'purpose' => 'Onthoudt je keuze over cookies.',
            'duration' => '180 dagen',
        ],
    ],

    // Optional cookies set by scripts you add. Fill this in so the cookie page stays correct, e.g.:
    // ['name' => '_ga', 'provider' => 'Google', 'purpose' => 'Statistieken', 'duration' => '2 jaar'],
    'optional' => [],
];
