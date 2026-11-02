<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StandaloneGameTest extends TestCase
{
    use RefreshDatabase;

    private function create(User $me, array $extra = []): Game
    {
        $this->actingAs($me)->post('/games', array_merge([
            'name' => 'Los spel', 'mode' => 'add', 'is_public' => 1, 'guests' => ['Oma', '  ', 'Opa'],
        ], $extra))->assertSessionHasNoErrors();

        return Game::latest('id')->first()->load('ownPlayers');
    }

    private function begin(User $me, Game $game, string $starter = 'Oma')
    {
        return $this->actingAs($me)->post("/games/{$game->public_id}/start", [
            'order' => $game->ownPlayers->pluck('id')->all(),
            'starting_player_id' => $game->ownPlayers->firstWhere('guest_name', $starter)->id,
        ]);
    }

    public function test_a_spel_can_be_played_without_a_toernooi(): void
    {
        $me = User::factory()->create();
        $game = $this->create($me);

        $this->assertNull($game->tournament_id);
        $this->assertCount(3, $game->ownPlayers);
        $this->assertNotNull($game->share_token);
        $this->assertNotNull($game->join_code);

        // nothing can be scored before seating and starter are chosen
        $oma = $game->ownPlayers->firstWhere('guest_name', 'Oma');
        $this->actingAs($me)->post("/games/{$game->public_id}/events", ['type' => 'draw', 'player_id' => $oma->id])
            ->assertSessionHasErrors('game');

        $this->actingAs($me)->post("/games/{$game->public_id}/start", [])->assertSessionHasErrors(['starting_player_id', 'order']);
        $this->begin($me, $game)->assertSessionHasNoErrors();

        $this->actingAs($me)->post("/games/{$game->public_id}/events", [
            'type' => 'tile', 'player_id' => $oma->id, 'digit_a' => 3, 'digit_b' => 4, 'digit_c' => 1,
        ])->assertSessionHasNoErrors();
        $this->assertSame(8, $game->fresh()->totals()[$oma->id]);
    }

    public function test_finishing_stores_the_result_and_the_winner(): void
    {
        $me = User::factory()->create();
        $game = $this->create($me);
        $this->begin($me, $game);
        [$oma, $opa] = [$game->ownPlayers->firstWhere('guest_name', 'Oma'), $game->ownPlayers->firstWhere('guest_name', 'Opa')];
        $me_seat = $game->ownPlayers->firstWhere('user_id', $me->id);

        $this->actingAs($me)->post("/games/{$game->public_id}/finish", [
            'ended_by' => 'emptied', 'winner_player_id' => $oma->id,
            'remaining' => [$me_seat->id => 5, $oma->id => 0, $opa->id => 7],
        ])->assertSessionHasNoErrors();

        $game->refresh()->load('ownPlayers');
        $this->assertSame(Status::Finished, $game->status);
        $this->assertSame(25 + 12, $game->ownPlayers->firstWhere('id', $oma->id)->final_points);
        $this->assertTrue($game->ownPlayers->firstWhere('id', $oma->id)->is_winner);
        $this->assertFalse($game->ownPlayers->firstWhere('id', $opa->id)->is_winner);

        // scoring is closed, the creator can reopen it
        $this->actingAs($me)->post("/games/{$game->public_id}/events", ['type' => 'draw', 'player_id' => $oma->id])
            ->assertSessionHasErrors('game');
        $this->actingAs($me)->post("/games/{$game->public_id}/reopen")->assertSessionHasNoErrors();
        $this->assertSame(Status::Active, $game->fresh()->status);
        $this->assertNull($game->ownPlayers()->first()->fresh()->final_points);
    }

    public function test_needs_at_least_two_players_and_unique_guest_names(): void
    {
        $me = User::factory()->create();

        $this->actingAs($me)->post('/games', ['mode' => 'add'])->assertSessionHasErrors('players');
        $this->actingAs($me)->post('/games', ['mode' => 'add', 'guests' => ['Oma', 'oma']])->assertSessionHasErrors('guests');
    }

    public function test_only_the_creator_can_delete_a_standalone_spel(): void
    {
        [$me, $other] = User::factory()->count(2)->create();
        $game = $this->create($me);

        $this->actingAs($other)->delete("/games/{$game->public_id}")->assertForbidden();
        $this->actingAs($me)->delete("/games/{$game->public_id}")->assertRedirect('/games');
        $this->assertModelMissing($game);
    }

    public function test_live_link_and_game_id_work_for_a_standalone_spel(): void
    {
        $me = User::factory()->create();
        $game = $this->create($me);
        $live = route('live.show', $game->share_token);

        $this->getJson("/live/{$game->share_token}/data")->assertOk()
            ->assertJsonPath('kind', 'game')->assertJsonPath('game.name', 'Los spel');

        $this->post('/join', ['code' => $game->joinCodeLabel()])->assertRedirect($live);
        $this->post('/join', ['code' => strtolower($game->join_code)])->assertRedirect($live);
        $this->post('/join', ['code' => $live])->assertRedirect($live);
        $this->from('/')->post('/join', ['code' => 'AAAA-AAAA'])->assertSessionHasErrors('code');
    }

    public function test_a_guest_can_claim_a_finished_standalone_seat(): void
    {
        $me = User::factory()->create();
        $game = $this->create($me);
        $this->begin($me, $game);
        $oma = $game->ownPlayers->firstWhere('guest_name', 'Oma');
        $others = $game->ownPlayers->where('id', '!=', $oma->id)->mapWithKeys(fn ($p) => [$p->id => 0])->all();

        $this->actingAs($me)->post("/games/{$game->public_id}/finish", [
            'ended_by' => 'emptied', 'winner_player_id' => $oma->id, 'remaining' => $others + [$oma->id => 0],
        ])->assertSessionHasNoErrors();
        $game->refresh();

        $this->app['auth']->forgetGuards();
        $this->get("/claim/{$game->share_token}/{$oma->id}")->assertRedirect(route('register'));
        $this->post('/register', [
            'username' => 'oma', 'email' => 'oma@example.com',
            'password' => 'Wachtwoord!1', 'password_confirmation' => 'Wachtwoord!1',
        ])->assertRedirect(route('home'));

        $this->assertSame(User::where('username', 'oma')->first()->id, $oma->fresh()->user_id);
    }
}
