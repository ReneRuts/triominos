<x-layout.app title="Inloggen">
    <main id="main" class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center px-4">
        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <h1 class="mb-4 text-lg font-semibold text-stone-900">Inloggen</h1>

            <form method="POST" action="{{ url('/login') }}" class="space-y-4">
                @csrf
                <x-ui.input name="login" label="E-mailadres of gebruikersnaam" required autofocus
                    autocomplete="username" />
                <x-ui.input name="password" type="password" label="Wachtwoord" required
                    autocomplete="current-password" />
                <x-ui.checkbox name="remember" label="Ingelogd blijven" />
                <x-ui.button class="w-full">Inloggen</x-ui.button>
            </form>

            <p class="mt-4 text-center text-sm text-stone-600">Nog geen account? <a href="{{ route('register') }}"
                    class="font-medium text-blue-700 hover:underline">Registreer je</a></p>
        </div>
    </main>
</x-layout.app>
