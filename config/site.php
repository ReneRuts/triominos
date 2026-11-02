<?php

/*
| Site-wide settings: SEO defaults, footer and the legal pages (privacy / cookies).
| Everything in [BRACKETS] is a placeholder: fill it in before going live.
*/

return [
    'name' => env('SITE_NAME', 'Triominos Scorebord'),
    'tagline' => 'Het scorebord voor je Triominos-avond',
    'description' => 'Houd de score van je Triominos-spel bij: alleen of in een toernooi met vrienden. '
        .'Met spelregels, handleiding, ranking en live meekijken voor wie niet aan tafel zit.',
    'keywords' => 'triominos, triominos scorebord, triominos punten, triominos spelregels, triominos score bijhouden, triominos toernooi',
    'url' => env('APP_URL', 'http://localhost'),
    'locale' => 'nl_BE',
    'theme_color' => '#1f7a6c',
    'og_image' => 'og-image.png', // 1200x630, put it in /public

    'owner' => [
        'name' => '[NAAM VERANTWOORDELIJKE]',
        'address' => '[STRAAT EN NUMMER, POSTCODE GEMEENTE, LAND]',
        'email' => '[E-MAILADRES]',
        'company_number' => '[ONDERNEMINGSNUMMER, INDIEN VAN TOEPASSING]',
    ],
    'hosting' => '[NAAM HOSTINGPARTIJ EN LAND]',

    'policies_updated' => '2 november 2026',
    'authority' => [
        'name' => 'Gegevensbeschermingsautoriteit (GBA)',
        'url' => 'https://www.gegevensbeschermingsautoriteit.be',
    ],
];
