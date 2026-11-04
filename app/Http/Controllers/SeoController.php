<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

/** robots.txt and sitemap.xml.*/
class SeoController extends Controller
{
    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /live/',
            'Disallow: /claim/',
            'Disallow: /tournaments',
            'Disallow: /games',
            'Disallow: /friends',
            'Disallow: /ranking',
            'Disallow: /login',
            'Disallow: /register',
            '',
            'Sitemap: '.route('sitemap'),
        ];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain']);
    }

    public function sitemap(): Response
    {
        $pages = [
            ['route' => 'home', 'priority' => '1.0'],
            ['route' => 'pages.rules', 'priority' => '0.9'],
            ['route' => 'pages.guide', 'priority' => '0.8'],
            ['route' => 'pages.privacy', 'priority' => '0.3'],
            ['route' => 'pages.cookies', 'priority' => '0.3'],
        ];

        return response()->view('sitemap', ['pages' => $pages])->header('Content-Type', 'application/xml');
    }
}