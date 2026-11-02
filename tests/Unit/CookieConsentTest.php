<?php

namespace Tests\Unit;

use App\Support\CookieConsent;
use Illuminate\Http\Request;
use Tests\TestCase;

class CookieConsentTest extends TestCase
{
    private function withCookie(?string $value): void
    {
        $cookies = $value === null ? [] : [config('cookies.name') => $value];
        $this->app->instance('request', Request::create('/', 'GET', [], $cookies));
    }

    public function test_no_cookie_means_undecided_and_no_optional_scripts(): void
    {
        $this->withCookie(null);

        $this->assertNull(CookieConsent::level());
        $this->assertFalse(CookieConsent::decided());
        $this->assertFalse(CookieConsent::accepted());
    }

    public function test_all_allows_optional_scripts_necessary_does_not(): void
    {
        $v = config('cookies.version');

        $this->withCookie("all:$v");
        $this->assertTrue(CookieConsent::accepted());

        $this->withCookie("necessary:$v");
        $this->assertTrue(CookieConsent::decided());
        $this->assertFalse(CookieConsent::accepted());
    }

    public function test_old_version_or_garbage_asks_again(): void
    {
        $this->withCookie('all:'.(config('cookies.version') + 1));
        $this->assertNull(CookieConsent::level());

        $this->withCookie('whatever');
        $this->assertNull(CookieConsent::level());
    }
}
