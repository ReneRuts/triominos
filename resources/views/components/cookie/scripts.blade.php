@use('App\Support\CookieConsent')
{{--
|--------------------------------------------------------------------------
| YOUR COOKIE / TRACKING SCRIPTS GO IN THIS FILE
|--------------------------------------------------------------------------
| This component is rendered at the end of <body> on every page.
|
|  1. Scripts that need consent (analytics, ads, embeds that set cookies):
|     paste them inside the @if block below. They are only output after the
|     visitor pressed "Accepteren".
|  2. Scripts that do NOT need consent (strictly necessary): paste them above the @if.
|  3. Add every optional cookie to config/cookies.php ('optional') so the
|     cookie policy page stays correct. Bump 'version' there to ask everybody again.
|
| Example, analytics script:
|     <script async src="https://example.com/analytics.js"></script>
--}}

{{-- Always loaded (strictly necessary only): --}}

@if (CookieConsent::accepted())
    {{-- Only loaded after "Accepteren": paste your scripts here. --}}
@endif
