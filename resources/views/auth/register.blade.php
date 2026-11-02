<x-layout.app title="Registreren">
    <main id="main" class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center px-4">
        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <h1 class="mb-4 text-lg font-semibold text-stone-900">Account maken</h1>

            @if (session('claim'))
                <x-ui.alert class="mb-4">
                    Je speelde mee als <span class="font-semibold">{{ session('claim')['name'] }}</span>. Maak een
                    account en dit spel komt in je ranking.
                    Heb je al een account? <a href="{{ route('login') }}" class="font-medium underline">Log dan in</a>,
                    ook dan koppelen we het.
                </x-ui.alert>
            @endif

            <form method="POST" action="{{ url('/register') }}" class="space-y-4" x-data="{ pw: '' }">
                @csrf
                <x-ui.input name="username" label="Gebruikersnaam" required autofocus autocomplete="username"
                    hint="3 tot 30 tekens: letters, cijfers, punt, streepje of underscore. Vrienden vinden je hiermee." />
                <x-ui.input name="email" type="email" label="E-mailadres" required autocomplete="email" />

                <div>
                    <x-ui.input name="password" type="password" label="Wachtwoord" required autocomplete="new-password"
                        x-model="pw" />
                    <ul class="mt-2 space-y-0.5 text-xs">
                        <li :class="pw.length >= 8 ? 'text-blue-700' : 'text-stone-500'">Minstens 8 tekens</li>
                        <li :class="/[A-Z]/.test(pw) ? 'text-blue-700' : 'text-stone-500'">Minstens één hoofdletter
                        </li>
                        <li :class="/[^A-Za-z0-9]/.test(pw) ? 'text-blue-700' : 'text-stone-500'">Minstens één speciaal
                            teken (bv. ! ? # @)</li>
                    </ul>
                </div>

                <x-ui.input name="password_confirmation" type="password" label="Herhaal wachtwoord" required
                    autocomplete="new-password" />

                <p class="text-xs text-stone-500">Met registreren ga je akkoord met ons <a
                        href="{{ route('pages.privacy') }}" class="underline">privacybeleid</a>.</p>
                <x-ui.button class="w-full">Registreren</x-ui.button>
            </form>

            <p class="mt-4 text-center text-sm text-stone-600">Al een account? <a href="{{ route('login') }}"
                    class="font-medium text-blue-700 hover:underline">Log in</a></p>
        </div>
    </main>
</x-layout.app>
