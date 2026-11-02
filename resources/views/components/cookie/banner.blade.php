@use('App\Support\CookieConsent')
<div x-data="cookieConsent(@js([
        'name' => config('cookies.name'),
        'version' => config('cookies.version'),
        'days' => config('cookies.days'),
        'current' => CookieConsent::level(),
        'decided' => CookieConsent::decided(),
    ]))"
    x-show="open" x-cloak @cookie-settings.window="open = true"
    role="dialog" aria-labelledby="cookie-title" aria-describedby="cookie-text"
    class="fixed inset-x-0 bottom-0 z-50 p-4">
    <div class="mx-auto max-w-3xl rounded-2xl border border-stone-200 bg-white p-5 shadow-xl">
        <h2 id="cookie-title" class="font-semibold text-stone-900">Cookies</h2>
        <p id="cookie-text" class="mt-1 text-sm leading-relaxed text-stone-600">
            We gebruiken cookies die nodig zijn om de site te laten werken, zoals inloggen.
            Met jouw toestemming laden we ook optionele cookies. Lees meer in ons
            <a href="{{ route('pages.cookies') }}" class="font-medium text-blue-700 underline">cookiebeleid</a>.
        </p>
        <div class="mt-4 flex flex-wrap gap-2">
            <x-ui.button type="button" variant="secondary" @click="choose('necessary')">Weigeren</x-ui.button>
            <x-ui.button type="button" variant="secondary" @click="choose('necessary')">Alleen noodzakelijk</x-ui.button>
            <x-ui.button type="button" @click="choose('all')">Accepteren</x-ui.button>
        </div>
    </div>
</div>
