{{-- Everything search engines and social networks read: title, description, canonical, Open Graph, Twitter, JSON-LD. --}}
@props(['title' => null, 'description' => null, 'robots' => 'noindex,follow', 'canonical' => null, 'image' => null, 'type' => 'website'])
@php
    $site = config('site');
    $fullTitle = $title ? $title.' · '.$site['name'] : $site['name'].' · '.$site['tagline'];
    $description = $description ?: $site['description'];
    $canonical = $canonical ?: url()->current();
    $image = asset($image ?: $site['og_image']);

    $jsonLd = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebSite',
                '@id' => url('/').'#website',
                'url' => url('/'),
                'name' => $site['name'],
                'description' => $site['description'],
                'inLanguage' => 'nl',
            ],
            [
                '@type' => 'WebApplication',
                'name' => $site['name'],
                'url' => url('/'),
                'applicationCategory' => 'GameApplication',
                'operatingSystem' => 'Any',
                'description' => $site['description'],
                'inLanguage' => 'nl',
                'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'EUR'],
            ],
        ],
    ];
@endphp
<title>{{ $fullTitle }}</title>
<meta name="description" content="{{ $description }}">
<meta name="keywords" content="{{ $site['keywords'] }}">
<meta name="robots" content="{{ $robots }}">
<meta name="theme-color" content="{{ $site['theme_color'] }}">
<link rel="canonical" href="{{ $canonical }}">
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.ico') }}">
<link rel="sitemap" type="application/xml" href="{{ route('sitemap') }}">

<meta property="og:site_name" content="{{ $site['name'] }}">
<meta property="og:type" content="{{ $type }}">
<meta property="og:locale" content="{{ $site['locale'] }}">
<meta property="og:title" content="{{ $fullTitle }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $image }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $fullTitle }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $image }}">

<script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
