<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_are_open_and_indexable(): void
    {
        foreach (['/', '/spelregels', '/handleiding', '/privacy', '/cookies'] as $url) {
            $this->get($url)->assertOk()->assertSee('content="index,follow"', false);
        }
    }

    public function test_home_for_guests_explains_and_offers_login_and_live_follow(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('Het scorebord voor je Triominos-avond')
            ->assertSee('Een spel live volgen')
            ->assertSee('Gratis account maken');
    }

    public function test_rulebook_guide_and_policies_have_their_content(): void
    {
        $this->get('/spelregels')->assertSee('Spelregels van Triominos')->assertSee('Einde van een spel');
        $this->get('/handleiding')->assertSee('Toernooi of los spel')->assertSee('Zitvolgorde en beginspeler');
        $this->get('/privacy')->assertSee('Privacybeleid')->assertSee(config('site.owner.email'));
        $this->get('/cookies')->assertSee('Noodzakelijke cookies')->assertSee('cookie_consent');
    }

    public function test_every_page_has_the_footer_and_the_cookie_banner(): void
    {
        foreach (['/', '/spelregels', '/login', '/register', '/privacy'] as $url) {
            $this->get($url)->assertOk()
                ->assertSee('Cookiebeleid')->assertSee('Privacybeleid')
                ->assertSee('Weigeren')->assertSee('Alleen noodzakelijk')->assertSee('Accepteren');
        }
    }

    public function test_seo_meta_is_present(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('<link rel="canonical"', false)
            ->assertSee('property="og:title"', false)
            ->assertSee('name="twitter:card"', false)
            ->assertSee('application/ld+json', false)
            ->assertSee('name="description"', false);
    }

    public function test_app_pages_are_not_indexed(): void
    {
        $this->get('/login')->assertSee('content="noindex,follow"', false);
    }

    public function test_sitemap_and_robots(): void
    {
        $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml')
            ->assertSee(route('pages.rules'), false);

        $this->get('/robots.txt')->assertOk()->assertSee('Sitemap: '.route('sitemap'), false)->assertSee('Disallow: /live/');
    }

    public function test_unknown_page_renders_the_404_page_with_footer(): void
    {
        $this->get('/bestaat-niet')->assertNotFound()->assertSee('Deze pagina bestaat niet')->assertSee('Cookiebeleid');
    }
}
