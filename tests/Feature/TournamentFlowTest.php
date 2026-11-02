<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Models\Friendship;
use App\Models\Game;
use App\Models\Player;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TournamentFlowTest extends TestCase
{
    use RefreshDatabase;

    /** Creates a toernooi; by default also starts spel 1 with the first seat as starter. */
    private function tournament(User $creator, User $friend, array $extra = [], bool $startGame = true): Tournament
    {
        Friendship::firstOrCreate(['requester_id' => $creator->id, 'addressee_id' => $friend->id], ['status' => 'accepted']);

        $this->actingAs($creator)->post('/tournaments', array_merge([
            'name' => 'Testtoernooi', 'mode' => 'add', 'target_score' => 400, 'is_public' => 1, 'players' => [$friend->id],
        ], $extra))->assertSessionHasNoErrors();

        $tournament = Tournament::latest('id')->first()->load('players');

        if ($startGame) {
            $this->startGame($creator, $tournament, $tournament->players->first(), $tournament->players->pluck('id')->all())
                ->assertSessionHasNoErrors();
        }

        return $tournament->fresh()->load('players', 'games');
    }

    private function startGame(User $by, Tournament $t, Player $starter, ?array $order = null)
    {
        return $this->actingAs($by)->post("/tournaments/{$t->public_id}/games", array_filter([
            'order' => $order, 'starting_player_id' => $starter->id,
        ]));
    }

    private function game(Tournament $t): Game
    {
        return $t->fresh()->load('games')->activeGame();
    }

    private function tile(User $by, Game $game, Player $player, array $digits, array $extra = [])
    {
        return $this->actingAs($by)->post("/games/{$game->public_id}/events", array_merge([
            'type' => 'tile', 'player_id' => $player->id,
            'digit_a' => $digits[0], 'digit_b' => $digits[1], 'digit_c' => $digits[2],
        ], $extra));
    }

    private function event(User $by, Game $game, Player $player, string $type)
    {
        return $this->actingAs($by)->post("/games/{$game->public_id}/events", ['type' => $type, 'player_id' => $player->id]);
    }

    private function endGame(User $by, Game $game, Player $winner, array $remaining, string $endedBy = 'emptied')
    {
        return $this->actingAs($by)->post("/games/{$game->public_id}/finish", [
            'ended_by' => $endedBy, 'winner_player_id' => $winner->id, 'remaining' => $remaining,
        ]);
    }

    private function total(Tournament $t, Player $p): int
    {
        return $t->fresh()->totals()[$p->id];
    }

    public function test_toernooi_needs_friends_as_players(): void
    {
        [$me, $stranger] = User::factory()->count(2)->create();

        $this->actingAs($me)->post('/tournaments', [
            'name' => 'X', 'mode' => 'add', 'target_score' => 400, 'players' => [$stranger->id],
        ])->assertSessionHasErrors('players');
    }

    public function test_first_spel_needs_seating_and_a_starting_player(): void
    {
        [$me, $friend] = User::factory()->count(2)->create();
        $t = $this->tournament($me, $friend, [], false);
        [$a, $b] = [$t->players[0], $t->players[1]];

        $this->actingAs($me)->post("/tournaments/{$t->public_id}/games", [])->assertSessionHasErrors(['starting_player_id', 'order']);

        // seated the other way round, second player starts
        $this->startGame($me, $t, $b, [$b->id, $a->id])->assertSessionHasNoErrors();

        $t = $t->fresh()->load('players', 'games');
        $this->assertSame([$b->id, $a->id], $t->players->pluck('id')->all());
        $this->assertSame($b->id, $this->game($t)->currentPlayer()->id);
    }

    public function test_every_next_spel_needs_a_starting_player_but_no_seating(): void
    {
        [$me, $friend] = User::factory()->count(2)->create();
        $t = $this->tournament($me, $friend);
        [$a, $b] = [$t->players[0], $t->players[1]];

        $this->endGame($me, $this->game($t), $a, [$a->id => 0, $b->id => 12])->assertSessionHasNoErrors();

        $this->actingAs($me)->post("/tournaments/{$t->public_id}/games", [])->assertSessionHasErrors('starting_player_id');
        $this->startGame($me, $t, $b)->assertSessionHasNoErrors();
        $this->assertSame(2, $this->game($t)->number);
    }

    public function test_bridge_and_hexagons(): void
    {
        [$me, $friend] = User::factory()->count(2)->create();
        $t = $this->tournament($me, $friend);
        $p = $t->players->first();

        $this->tile($me, $this->game($t), $p, [3, 4, 1]);                                       // 8
        $this->tile($me, $this->game($t), $p, [2, 2, 4], ['bridge' => 1, 'hexagons' => 2]);     // 8 + 40 + 60

        $this->assertSame(8 + 108, $this->total($t, $p));
    }

    public function test_triple_bonus_once_per_spel_and_again_in_the_next_spel(): void
    {
        [$me, $friend] = User::factory()->count(2)->create();
        $t = $this->tournament($me, $friend);
        [$a, $b] = [$t->players[0], $t->players[1]];

        $this->tile($me, $this->game($t), $a, [1, 1, 1]); // 3 + 10
        $this->tile($me, $this->game($t), $b, [2, 2, 2]); // 6, bonus already given
        $this->assertSame(13, $this->total($t, $a));
        $this->assertSame(6, $this->total($t, $b));

        $this->endGame($me, $this->game($t), $a, [$a->id => 0, $b->id => 0]);
        $this->startGame($me, $t, $b);
        $this->tile($me, $this->game($t), $b, [2, 2, 2]); // 6 + 10 in the new spel
        $this->assertSame(6 + 16, $this->total($t, $b));
    }

    public function test_zero_zero_zero_as_very_first_stone_is_worth_forty(): void
    {
        [$me, $friend] = User::factory()->count(2)->create();
        $t = $this->tournament($me, $friend);
        $p = $t->players->first();

        $this->tile($me, $this->game($t), $p, [0, 0, 0]);

        $this->assertSame(40, $this->total($t, $p));
    }

    public function test_removing_a_stone_recalculates_the_bonus_of_later_stones(): void
    {
        [$me, $friend] = User::factory()->count(2)->create();
        $t = $this->tournament($me, $friend);
        [$a, $b] = [$t->players[0], $t->players[1]];
        $game = $this->game($t);

        $this->tile($me, $game, $a, [1, 1, 1]); // 13
        $this->tile($me, $game, $b, [2, 2, 2]); // 6
        $first = $game->events()->first();

        $this->actingAs($me)->delete("/games/{$game->public_id}/events/{$first->id}")->assertSessionHasNoErrors();

        $this->assertSame(16, $this->total($t, $b)); // now the first triple: 6 + 10
        $this->assertSame(0, $this->total($t, $a));
    }

    public function test_multiply_mode_and_manual_total(): void
    {
        [$me, $friend] = User::factory()->count(2)->create();
        $t = $this->tournament($me, $friend, ['mode' => 'multiply']);
        $p = $t->players->first();

        $this->tile($me, $this->game($t), $p, [3, 4, 0]);
        $this->assertSame(12, $this->total($t, $p));

        $this->tile($me, $this->game($t), $p, [1, 2, 3], ['manual_points' => 99]);
        $this->assertSame(12 + 99, $this->total($t, $p));
    }

    public function test_draw_and_pass_rules(): void
    {
        [$me, $friend] = User::factory()->count(2)->create();
        $t = $this->tournament($me, $friend);
        [$a, $b] = [$t->players[0], $t->players[1]];
        $game = $this->game($t);

        $this->event($me, $game, $a, 'pass')->assertSessionHasErrors('game');          // not before 3 draws
        $this->event($me, $game, $b, 'draw')->assertSessionHasErrors('game');          // not his turn

        foreach ([1, 2, 3] as $i) {
            $this->event($me, $game, $a, 'draw')->assertSessionHasNoErrors();
        }
        $this->event($me, $game, $a, 'draw')->assertSessionHasErrors('game');          // a 4th is refused

        $this->event($me, $game, $a, 'pass')->assertSessionHasNoErrors();
        $this->assertSame(-25, $this->total($t, $a));

        $this->event($me, $game, $b, 'draw')->assertSessionHasNoErrors();               // pass ended the turn
        $this->assertSame(-5, $this->total($t, $b));
    }

    public function test_only_players_can_score(): void
    {
        [$me, $friend, $outsider] = User::factory()->count(3)->create();
        $t = $this->tournament($me, $friend);
        $game = $this->game($t);

        $this->event($friend, $game, $t->players->first(), 'draw')->assertRedirect();
        $this->assertSame(-5, $this->total($t, $t->players->first()));

        $this->event($outsider, $game, $t->players->first(), 'draw')->assertForbidden();
        $this->actingAs($outsider)->get("/games/{$game->public_id}")->assertForbidden();
    }

    public function test_creator_can_lock_a_score_and_players_cannot_delete_it(): void
    {
        [$me, $friend] = User::factory()->count(2)->create();
        $t = $this->tournament($me, $friend);
        $game = $this->game($t);
        $base = "/games/{$game->public_id}/events";

        $this->event($friend, $game, $t->players->first(), 'draw');
        $event = $game->events()->first();

        $this->actingAs($friend)->post("$base/{$event->id}/lock")->assertForbidden();
        $this->actingAs($me)->post("$base/{$event->id}/lock");
        $this->assertTrue($event->fresh()->locked);

        $this->actingAs($friend)->delete("$base/{$event->id}")->assertSessionHasErrors('game');
        $this->assertDatabaseHas('score_events', ['id' => $event->id]);

        $this->actingAs($me)->post("$base/{$event->id}/lock");
        $this->actingAs($friend)->delete("$base/{$event->id}");
        $this->assertDatabaseMissing('score_events', ['id' => $event->id]);
    }

    public function test_end_of_a_spel_scores_only_the_winner(): void
    {
        [$me, $friend] = User::factory()->count(2)->create();
        $t = $this->tournament($me, $friend);
        [$a, $b] = [$t->players[0], $t->players[1]];

        $this->endGame($me, $this->game($t), $a, [$a->id => 0, $b->id => 12])->assertSessionHasNoErrors();

        $this->assertSame(37, $this->total($t, $a)); // 25 + 12
        $this->assertNull($this->game($t));
    }

    public function test_reaching_the_target_stops_new_spellen_and_finishing_sets_the_winner(): void
    {
        [$me, $friend] = User::factory()->count(2)->create();
        $t = $this->tournament($me, $friend, ['target_score' => 50]);
        [$a, $b] = [$t->players[0], $t->players[1]];

        $this->tile($me, $this->game($t), $a, [1, 1, 1], ['hexagons' => 1]); // 3 + 10 + 50 = 63
        $this->assertNotNull($t->fresh()->target_reached_at);

        $this->endGame($me, $this->game($t), $b, [$a->id => 9, $b->id => 2], 'blocked'); // b gets 9 - 2 = 7

        $this->startGame($me, $t, $a)->assertSessionHasErrors('game');

        $this->actingAs($me)->post("/tournaments/{$t->public_id}/finish")->assertSessionHasNoErrors();
        $t = $t->fresh()->load('players');
        $this->assertSame(Status::Finished, $t->status);
        $this->assertTrue($t->players->firstWhere('id', $a->id)->is_winner);
        $this->assertFalse($t->players->firstWhere('id', $b->id)->is_winner);
    }

    public function test_target_score_can_be_changed_while_playing(): void
    {
        [$me, $friend] = User::factory()->count(2)->create();
        $t = $this->tournament($me, $friend, ['target_score' => 50]);
        $base = "/tournaments/{$t->public_id}";

        $this->tile($me, $this->game($t), $t->players->first(), [5, 5, 5], ['bridge' => 1]);
        $this->assertNotNull($t->fresh()->target_reached_at);

        $this->actingAs($me)->post("$base/target", ['target_score' => 400])->assertSessionHasNoErrors();
        $this->assertSame(400, $t->fresh()->target_score);
        $this->assertNull($t->fresh()->target_reached_at);

        $this->actingAs($me)->post("$base/target", ['target_score' => 20])->assertSessionHasErrors('target_score');
    }

    public function test_timeout_gives_warnings_first_then_penalty_and_draw(): void
    {
        [$me, $friend] = User::factory()->count(2)->create();
        $t = $this->tournament($me, $friend, [
            'time_limit_enabled' => 1, 'turn_time_limit' => 30, 'warnings_allowed' => 1,
            'timeout_penalty_points' => 5, 'timeout_draw_tile' => 1,
        ]);
        $p = $t->players->first();
        $url = "/games/{$this->game($t)->public_id}/timeout";

        $this->actingAs($me)->post($url, ['player_id' => $p->id]);
        $this->assertSame(0, $this->total($t, $p));
        $this->assertSame(1, $p->fresh()->warnings_used);

        $this->actingAs($me)->post($url, ['player_id' => $p->id]);
        $this->assertSame(-10, $this->total($t, $p)); // -5 penalty, -5 drawing a stone
    }

    public function test_live_data_for_a_toernooi_lists_turns_with_all_bonuses(): void
    {
        [$me, $friend] = User::factory()->count(2)->create();
        $t = $this->tournament($me, $friend);
        $p = $t->players->first();

        $this->event($me, $this->game($t), $p, 'draw');
        $this->tile($me, $this->game($t), $p, [1, 1, 1], ['bridge' => 1]);

        $json = $this->getJson("/live/{$t->share_token}/data")->assertOk()
            ->assertJsonPath('kind', 'tournament')->assertJsonPath('game.name', 'Testtoernooi');

        $turn = $json->json('recent.0');
        $this->assertSame(3 + 10 + 40 - 5, $turn['total']);
        $this->assertContains('Eerste steen met drie dezelfde cijfers van het spel', array_column($turn['lines'], 'text'));
        $this->get('/live/'.str_repeat('x', 40))->assertNotFound();
    }
}
