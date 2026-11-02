@props(['minimal' => false])
@php
    $links = auth()->check()
        ? [
            ['home', 'Start', 'home'],
            ['tournaments.index', 'Toernooien', 'tournaments.*'],
            ['games.index', 'Losse spellen', 'games.*'],
            ['ranking.index', 'Ranking', 'ranking.*'],
            ['friends.index', 'Vrienden', 'friends.*'],
        ]
        : [['pages.rules', 'Spelregels', 'pages.rules'], ['pages.guide', 'Handleiding', 'pages.guide']];

    $pending = auth()->check()
        ? \App\Models\Friendship::where('addressee_id', auth()->id())->where('status', 'pending')->count()
        : 0;
@endphp

<header class="border-b border-stone-200 bg-white" x-data="{ open: false }">
    <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4">
        <a href="{{ route('home') }}" aria-label="{{ config('site.name') }}"><x-site.logo size="14" /></a>

        @unless ($minimal)
            <nav class="hidden items-center gap-1 md:flex" aria-label="Hoofdmenu">
                @foreach ($links as [$route, $label, $pattern])
                    <a href="{{ route($route) }}" @class([
                        'rounded-lg px-3 py-2 text-sm font-medium',
                        'bg-blue-50 text-blue-800' => request()->routeIs($pattern),
                        'text-stone-600 hover:bg-stone-100' => !request()->routeIs($pattern),
                    ])>
                        {{ $label }}
                        @if ($route === 'friends.index' && $pending)
                            <span
                                class="ml-1 rounded-full bg-rose-500 px-1.5 py-0.5 text-xs text-white">{{ $pending }}</span>
                        @endif
                    </a>
                @endforeach

                <span class="mx-2 h-5 w-px bg-stone-200"></span>

                @auth
                    <span class="text-sm text-stone-500">{{ auth()->user()->username }}</span>
                    <form method="POST" action="{{ route('logout') }}">@csrf
                        <x-ui.button variant="ghost" size="sm">Uitloggen</x-ui.button>
                    </form>
                @else
                    <x-ui.button variant="ghost" size="sm" :href="route('login')">Inloggen</x-ui.button>
                    <x-ui.button size="sm" :href="route('register')">Registreren</x-ui.button>
                @endauth
            </nav>

            <button type="button" class="rounded-lg p-2 text-stone-600 hover:bg-stone-100 md:hidden" @click="open = !open"
                :aria-expanded="open" aria-label="Menu">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        @else
            <x-ui.button variant="secondary" size="sm" :href="route('home')">Startpagina</x-ui.button>
        @endunless
    </div>

    @unless ($minimal)
        <nav class="space-y-1 border-t border-stone-100 px-4 py-3 md:hidden" x-show="open" x-cloak
            aria-label="Mobiel menu">
            @foreach ($links as [$route, $label, $pattern])
                <a href="{{ route($route) }}" @class([
                    'block rounded-lg px-3 py-2 text-sm font-medium',
                    'bg-blue-50 text-blue-800' => request()->routeIs($pattern),
                    'text-stone-600' => !request()->routeIs($pattern),
                ])>{{ $label }}@if ($route === 'friends.index' && $pending)
                        ({{ $pending }})
                    @endif
                </a>
            @endforeach

            @auth
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button class="block w-full rounded-lg px-3 py-2 text-left text-sm font-medium text-stone-600">Uitloggen
                        ({{ auth()->user()->username }})
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}"
                    class="block rounded-lg px-3 py-2 text-sm font-medium text-stone-600">Inloggen</a>
                <a href="{{ route('register') }}"
                    class="block rounded-lg px-3 py-2 text-sm font-medium text-stone-600">Registreren</a>
            @endauth
        </nav>
    @endunless
</header>
