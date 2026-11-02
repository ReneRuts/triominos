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

class RankingTest extends TestCase
{
    use RefreshDatabase;

    /** @param  array<int,int>  $results  user id => points; highest wins */
    private function finishedTournament(array $results, bool $public = true): Tournament
    {
        $t = Tournament::create(['creator_id' => array_key_first($results), 'name' => 'T', 'status' => Status::Finished, 'finished_at' => now(), 'is_public' => $public]);
        $this->seat($results, 'tournament_id', $t->id);

        return $t;
    }

    private function finishedStandalone(array $results, bool $public = true): Game
    {
        $g = Game::create(['creator_id' => array_key_first($results), 'status' => Status::Finished, 'finished_at' => now(), 'is_public' => $public]);
        $this->seat($results, 'game_id', $g->id);

        return $g;
    }

    private function seat(array $results, string $column, int $id): void
    {
        $max = max($results);
        $pos = 0;
        foreach ($results as $userId => $points) {
            Player::create([$column => $id, 'user_id' => $userId, 'position' => $pos++, 'final_points' => $points, 'is_winner' => $points === $max]);
        }
    }

    public function test_tournament_ranking_orders_by_wins(): void
    {
        [$a, $b, $c] = [User::factory()->create(['username' => 'anna']), User::factory()->create(['username' => 'bram']), User::factory()->create(['username' => 'cora'])];

        $this->finishedTournament([$a->id => 400, $b->id => 300]);
        $this->finishedTournament([$b->id => 410, $a->id => 100]);
        $this->finishedTournament([$b->id => 420, $c->id => 100]);

        $response = $this->actingAs($a)->get('/ranking')->assertOk();
        $ranking = $response->viewData('tournamentRanking');

        $this->assertSame(['bram', 'anna', 'cora'], $ranking->pluck('username')->all());
        $this->assertEquals(2, $ranking->first()->wins);
        $this->assertEquals(3, $ranking->first()->games_played);
        $this->assertSame(0, $response->viewData('gameRanking')->count());
    }

    public function test_standalone_games_have_their_own_ranking(): void
    {
        [$a, $b] = User::factory()->count(2)->create();

        $this->finishedStandalone([$a->id => 45, $b->id => 0]);
        $this->finishedStandalone([$a->id => 30, $b->id => 60]);

        $response = $this->actingAs($a)->get('/ranking')->assertOk();
        $games = $response->viewData('gameRanking');

        $this->assertSame(0, $response->viewData('tournamentRanking')->count());
        $this->assertSame(2, $games->count());
        $this->assertEquals(1, $games->firstWhere('user_id', $a->id)->wins);
        $this->assertEquals(2, $games->firstWhere('user_id', $a->id)->games_played);
    }

    public function test_friend_filters_limit_who_is_listed(): void
    {
        [$me, $friend, $stranger] = User::factory()->count(3)->create();
        Friendship::create(['requester_id' => $me->id, 'addressee_id' => $friend->id, 'status' => 'accepted']);
        $this->finishedTournament([$me->id => 100, $friend->id => 200, $stranger->id => 300]);

        $friends = $this->actingAs($me)->get('/ranking?scope=friends')->viewData('tournamentRanking');
        $this->assertEqualsCanonicalizing([$me->id, $friend->id], $friends->pluck('user_id')->map(fn ($id) => (int) $id)->all());

        $selected = $this->actingAs($me)->get('/ranking?scope=selected&me=0&friends[]='.$friend->id)->viewData('tournamentRanking');
        $this->assertSame([$friend->id], $selected->pluck('user_id')->map(fn ($id) => (int) $id)->all());

        // somebody who is not a friend is ignored
        $none = $this->actingAs($me)->get('/ranking?scope=selected&me=0&friends[]='.$stranger->id)->viewData('tournamentRanking');
        $this->assertSame(0, $none->count());
    }

    public function test_private_tournaments_and_games_only_count_for_their_players(): void
    {
        [$a, $b, $c] = User::factory()->count(3)->create();
        $tournament = $this->finishedTournament([$a->id => 400, $b->id => 300], public: false);
        $game = $this->finishedStandalone([$a->id => 40, $b->id => 0], public: false);

        $outsider = $this->actingAs($c)->get('/ranking');
        $this->assertSame(0, $outsider->viewData('tournamentRanking')->count());
        $this->assertSame(0, $outsider->viewData('gameRanking')->count());
        $outsider->assertDontSee(route('tournaments.show', $tournament), false)->assertDontSee(route('games.show', $game), false);
        $this->actingAs($c)->get("/tournaments/{$tournament->public_id}")->assertForbidden();
        $this->actingAs($c)->get("/games/{$game->public_id}")->assertForbidden();

        $mine = $this->actingAs($a)->get('/ranking');
        $this->assertSame(2, $mine->viewData('tournamentRanking')->count());
        $this->assertSame(2, $mine->viewData('gameRanking')->count());
        $mine->assertSee(route('tournaments.show', $tournament), false)->assertSee(route('games.show', $game), false);
        $this->actingAs($b)->get("/tournaments/{$tournament->public_id}")->assertOk();
    }

    public function test_finished_public_ones_can_be_looked_back_at_by_anyone_with_an_account(): void
    {
        [$a, $b, $c] = User::factory()->count(3)->create();
        $tournament = $this->finishedTournament([$a->id => 400, $b->id => 300]);
        $game = $this->finishedStandalone([$a->id => 40, $b->id => 0]);

        $this->actingAs($c)->get("/tournaments/{$tournament->public_id}")->assertOk()->assertSee('Winnaar');
        $this->actingAs($c)->get("/games/{$game->public_id}")->assertOk();
    }
}
