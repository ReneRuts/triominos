@props([
    'title' => null,
    'description' => null,
    'robots' => 'noindex,follow',
    'canonical' => null,
    'image' => null,
    'type' => 'website',
])
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-site.seo :title="$title" :description="$description" :robots="$robots" :canonical="$canonical" :image="$image"
        :type="$type" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{ $head ?? '' }}
</head>

<body class="flex min-h-screen flex-col">
    {{ $slot }}

    <x-site.footer />
    <x-cookie.banner />
    <x-cookie.scripts />
</body>

</html>
