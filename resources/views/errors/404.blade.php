<x-layout.app title="Pagina niet gevonden" width="max-w-2xl">
    <div class="py-16 text-center">
        <p class="text-sm font-semibold text-blue-700">404</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-stone-900">Deze pagina bestaat niet</h1>
        <p class="mt-3 text-stone-600">De link klopt niet meer, of het toernooi of spel is verwijderd.</p>
        <x-ui.button class="mt-6" :href="route('home')">Naar de startpagina</x-ui.button>
    </div>
</x-layout.app>
