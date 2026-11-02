<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\PlaysGames;
use Tests\TestCase;

/** Smoke tests: every page renders (Blade/component errors show up here) with the right content. */
class ViewsTest extends TestCase
{
    use PlaysGames, RefreshDatabase;

    public function test_home_for_a_logged_in_user(): void
    {
        $me = User::factory()->create(['username' => 'anna']);

        $this->actingAs($me)->get('/')->assertOk()
            ->assertSee('Hoi anna')->assertSee('Toernooi starten')->assertSee('Los spel spelen')
            ->assertSee(route('tournaments.create'), false)->assertSee(route('games.create'), false);
    }

    public function test_app_pages_need_a_login(): void
    {
        foreach (['/tournaments', '/tournaments/create', '/games', '/games/create', '/ranking', '/friends'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }

    public function test_tournament_pages_render(): void
    {
        [$me, $friend] = User::factory()->count(2)->create();
        $tournament = $this->makeTournament($me, $friend, [], false);

        $this->actingAs($me)->get('/tournaments')->assertOk()->assertSee('Testtoernooi');
        $this->actingAs($me)->get('/tournaments/create')->assertOk()->assertSee('Toernooi eindigt bij');

        // before the first spel: seating form
        $this->actingAs($me)->get("/tournaments/{$tournament->public_id}")->assertOk()
            ->assertSee('Zitvolgorde en beginspeler')->assertSee('Start spel 1');

        $this->actingAs($me)->post("/tournaments/{$tournament->public_id}/games", [
            'order' => $tournament->players->pluck('id')->all(), 'starting_player_id' => $tournament->players->first()->id,
        ]);

        $this->actingAs($me)->get("/tournaments/{$tournament->public_id}")->assertOk()
            ->assertSee('Spel 1 loopt')->assertSee('Live meekijken voor gasten');
    }

    public function test_game_page_inside_a_tournament_lets_players_score(): void
    {
        [$me, $friend, $outsider] = User::factory()->count(3)->create();
        $tournament = $this->makeTournament($me, $friend);
        $game = $tournament->fresh()->load('games')->activeGame();
        $player = $tournament->players->first();

        $this->postTile($me, $game, $player, [3, 4, 1]);

        $this->actingAs($me)->get("/games/{$game->public_id}")->assertOk()
            ->assertSee('Punten invullen')->assertSee('Spel afsluiten')->assertSee('Steen 3-4-1')->assertSee('Verloop van Spel 1');

        $this->actingAs($outsider)->get("/games/{$game->public_id}")->assertForbidden()->assertSee('Geen toegang');
    }

    public function test_standalone_game_setup_scoring_and_result_pages(): void
    {
        $me = User::factory()->create();
        $game = $this->makeStandalone($me, false);

        $this->actingAs($me)->get('/games')->assertOk()->assertSee('Los spel');
        $this->actingAs($me)->get('/games/create')->assertOk()->assertSee('Nieuw los spel');
        $this->actingAs($me)->get("/games/{$game->public_id}")->assertOk()->assertSee('Zitvolgorde en beginspeler')->assertDontSee('Punten invullen');

        $this->actingAs($me)->post("/games/{$game->public_id}/start", [
            'order' => $game->ownPlayers->pluck('id')->all(), 'starting_player_id' => $game->ownPlayers->first()->id,
        ])->assertSessionHasNoErrors();
        $this->actingAs($me)->get("/games/{$game->public_id}")->assertOk()->assertSee('Punten invullen')->assertSee('Live meekijken voor gasten');

        $winner = $game->ownPlayers->first();
        $this->actingAs($me)->post("/games/{$game->public_id}/finish", [
            'ended_by' => 'emptied', 'winner_player_id' => $winner->id,
            'remaining' => $game->ownPlayers->mapWithKeys(fn ($p) => [$p->id => 0])->all(),
        ])->assertSessionHasNoErrors();

        $this->actingAs($me)->get("/games/{$game->public_id}")->assertOk()->assertSee('Winnaar')->assertSee('Spel heropenen');
    }

    public function test_live_page_works_for_tournament_and_standalone(): void
    {
        [$me, $friend] = User::factory()->count(2)->create();
        $tournament = $this->makeTournament($me, $friend);
        $standalone = $this->makeStandalone($me);

        $this->app['auth']->forgetGuards();
        $this->get("/live/{$tournament->share_token}")->assertOk()->assertSee('Testtoernooi')->assertSee('noindex,nofollow', false);
        $this->get("/live/{$standalone->share_token}")->assertOk()->assertSee('Los spel');
    }

    public function test_friends_and_ranking_pages_render(): void
    {
        $me = User::factory()->create();

        $this->actingAs($me)->get('/friends')->assertOk()->assertSee('Vriend toevoegen');
        $this->actingAs($me)->get('/ranking')->assertOk()->assertSee('Toernooien')->assertSee('Losse spellen');
    }
}
