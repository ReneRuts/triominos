<x-layout.app title="Geen toegang" width="max-w-2xl">
    <div class="py-16 text-center">
        <p class="text-sm font-semibold text-blue-700">403</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-stone-900">Je hebt hier geen toegang toe</h1>
        <p class="mt-3 text-stone-600">Lopende toernooien en spellen zijn alleen zichtbaar voor de spelers. Afgeronde openbare spellen kan iedereen met een account bekijken.</p>
        <x-ui.button class="mt-6" :href="route('home')">Naar de startpagina</x-ui.button>
    </div>
</x-layout.app>
