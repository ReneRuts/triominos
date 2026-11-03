<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\PlaysGames;
use Tests\TestCase;

class GameLockingTest extends TestCase
{
    use PlaysGames, RefreshDatabase;

    public function test_a_finished_spel_accepts_no_more_scores_finishing_or_deleting(): void
    {
        [$me, $friend] = User::factory()->count(2)->create();
        $tournament = $this->makeTournament($me, $friend);
        $game = $tournament->activeGame();
        $player = $tournament->players->first();

        $this->postTile($me, $game, $player, [3, 4, 1])->assertSessionHasNoErrors();
        $event = $game->events()->first();
        $finish = [
            'ended_by' => 'emptied',
            'winner_player_id' => $player->id,
            'remaining' => $tournament->players->mapWithKeys(fn($p) => [$p->id => 0])->all(),
        ];

        $this->actingAs($me)->post("/games/{$game->public_id}/finish", $finish)->assertSessionHasNoErrors();

        $this->postTile($me, $game, $player, [1, 1, 1])->assertSessionHasErrors('game');
        $this->actingAs($me)->post("/games/{$game->public_id}/finish", $finish)->assertSessionHasErrors('game');
        $this->actingAs($me)->delete("/games/{$game->public_id}/events/{$event->id}")->assertSessionHasErrors('game');
        $this->assertDatabaseHas('score_events', ['id' => $event->id]);
    }

    public function test_the_finished_game_page_has_no_score_forms_but_a_reopen_button(): void
    {
        $me = User::factory()->create();
        $game = $this->makeStandalone($me);
        $winner = $game->ownPlayers->first();

        $this->actingAs($me)->post("/games/{$game->public_id}/finish", [
            'ended_by' => 'emptied',
            'winner_player_id' => $winner->id,
            'remaining' => $game->ownPlayers->mapWithKeys(fn($p) => [$p->id => 0])->all(),
        ])->assertSessionHasNoErrors();

        $this->actingAs($me)->get("/games/{$game->public_id}")->assertOk()
            ->assertSee('Spel heropenen')
            ->assertDontSee('Punten invullen')
            ->assertDontSee('Het spel is afgelopen');
    }

    public function test_only_the_creator_can_enter_scores(): void
    {
        [$me, $friend] = User::factory()->count(2)->create();
        $tournament = $this->makeTournament($me, $friend);
        $game = $tournament->activeGame();
        $player = $tournament->players->first();

        $this->postTile($friend, $game, $player, [1, 2, 3])->assertForbidden();
        $this->actingAs($friend)->post("/games/{$game->public_id}/events", ['type' => 'draw', 'player_id' => $player->id])->assertForbidden();
        $this->actingAs($friend)->get("/games/{$game->public_id}")->assertOk()->assertDontSee('Punten invullen');
        $this->postTile($me, $game, $player, [1, 2, 3])->assertSessionHasNoErrors();
    }

    public function test_the_creator_can_change_visibility_of_a_standalone_spel(): void
    {
        [$me, $other] = User::factory()->count(2)->create();
        $game = $this->makeStandalone($me);

        $this->actingAs($other)->post("/games/{$game->public_id}/visibility", ['is_public' => 0])->assertForbidden();

        $this->actingAs($me)->post("/games/{$game->public_id}/visibility", ['is_public' => 0])->assertSessionHasNoErrors();
        $this->assertFalse($game->fresh()->is_public);

        $this->actingAs($me)->post("/games/{$game->public_id}/visibility", ['is_public' => 1]);
        $this->assertTrue($game->fresh()->is_public);
    }

    public function test_visibility_of_a_toernooi_also_applies_to_its_spellen(): void
    {
        [$me, $friend] = User::factory()->count(2)->create();
        $tournament = $this->makeTournament($me, $friend);

        $this->actingAs($friend)->post("/tournaments/{$tournament->public_id}/visibility", ['is_public' => 0])->assertForbidden();

        $this->actingAs($me)->post("/tournaments/{$tournament->public_id}/visibility", ['is_public' => 0])->assertSessionHasNoErrors();
        $this->assertFalse($tournament->fresh()->is_public);
        $this->assertFalse($tournament->games()->first()->is_public);
    }
}