<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\PlaysGames;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use PlaysGames, RefreshDatabase;

    public function test_games_still_need_an_account(): void
    {
        $this->get('/tournaments')->assertRedirect('/login');
        $this->get('/games')->assertRedirect('/login');
        $this->get('/ranking')->assertRedirect('/login');
    }

    public function test_game_and_tournament_get_a_short_unique_id(): void
    {
        [$me, $friend] = User::factory()->count(2)->create();
        $tournament = $this->makeTournament($me, $friend);
        $game = $this->makeStandalone($me);

        foreach ([$tournament, $game] as $model) {
            $this->assertMatchesRegularExpression('/^[A-HJ-NP-Z2-9]{8}$/', $model->join_code);
            $this->assertMatchesRegularExpression('/^[A-Z0-9]{4}-[A-Z0-9]{4}$/', $model->joinCodeLabel());
        }
        $this->assertNotSame($tournament->join_code, $game->join_code);
    }

    public function test_guest_can_join_a_toernooi_with_id_link_or_secret(): void
    {
        [$me, $friend] = User::factory()->count(2)->create();
        $tournament = $this->makeTournament($me, $friend);
        $live = route('live.show', $tournament->share_token);

        $this->post('/join', ['code' => $tournament->joinCodeLabel()])->assertRedirect($live);
        $this->post('/join', ['code' => strtolower($tournament->join_code)])->assertRedirect($live);
        $this->post('/join', ['code' => $live])->assertRedirect($live);
        $this->post('/join', ['code' => $tournament->share_token])->assertRedirect($live);
    }

    public function test_unknown_id_or_link_is_rejected(): void
    {
        $this->from('/')->post('/join', ['code' => 'AAAA-AAAA'])->assertRedirect('/')->assertSessionHasErrors('code');
        $this->from('/')->post('/join', ['code' => '1'])->assertSessionHasErrors('code');
        $this->from('/')->post('/join', ['code' => ''])->assertSessionHasErrors('code');
    }

    public function test_new_link_also_renews_the_game_id(): void
    {
        [$me, $friend] = User::factory()->count(2)->create();
        $tournament = $this->makeTournament($me, $friend);
        $old = $tournament->join_code;

        $this->actingAs($me)->post("/tournaments/{$tournament->public_id}/link")->assertSessionHasNoErrors();

        $this->assertNotSame($old, $tournament->fresh()->join_code);
    }
}
