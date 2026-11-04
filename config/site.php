<?php

/*
| Site-wide settings: SEO defaults, footer and the legal pages (privacy / cookies).
| Everything in [BRACKETS] is a placeholder: fill it in before going live.
*/

return [
    'name' => env('APP_NAME', 'Triominos Scorebord'),
    'tagline' => 'Het scorebord voor je Triominos-avonden',
    'description' => 'Houd de score van je Triominos-spel bij: alleen of in een toernooi met vrienden. '
        . 'Met spelregels, handleiding, ranking en live meekijken voor wie niet aan tafel zit.',
    'keywords' => 'triominos, triominos scorebord, triominos punten, triominos spelregels, triominos score bijhouden, triominos toernooi',
    'url' => env('APP_URL', 'https://localhost'),
    'locale' => 'nl_BE',
    'theme_color' => '#1f7a6c',
    'og_image' => 'og-image.png',

    'owner' => [
        'name' => 'René Ruts',
        'email' => 'rene.ruts2@gmail.com',
    ],

    'policies_updated' => '3 november 2026',
    'authority' => [
        'name' => 'Gegevensbeschermingsautoriteit (GBA)',
        'url' => 'https://www.gegevensbeschermingsautoriteit.be',
    ],
];
