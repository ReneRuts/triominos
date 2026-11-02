<?php

namespace Tests\Concerns;

use App\Models\Friendship;
use App\Models\Game;
use App\Models\Player;
use App\Models\Tournament;
use App\Models\User;

/** Helpers to set up toernooien and losse spellen through the real HTTP endpoints. */
trait PlaysGames
{
    protected function makeTournament(User $creator, User $friend, array $extra = [], bool $startGame = true): Tournament
    {
        Friendship::firstOrCreate(['requester_id' => $creator->id, 'addressee_id' => $friend->id], ['status' => 'accepted']);

        $this->actingAs($creator)->post('/tournaments', array_merge([
            'name' => 'Testtoernooi', 'mode' => 'add', 'target_score' => 400, 'is_public' => 1, 'players' => [$friend->id],
        ], $extra))->assertSessionHasNoErrors();

        $tournament = Tournament::latest('id')->first()->load('players');

        if ($startGame) {
            $this->actingAs($creator)->post("/tournaments/{$tournament->public_id}/games", [
                'order' => $tournament->players->pluck('id')->all(),
                'starting_player_id' => $tournament->players->first()->id,
            ])->assertSessionHasNoErrors();
        }

        return $tournament->fresh()->load('players', 'games');
    }

    protected function makeStandalone(User $me, bool $start = true): Game
    {
        $this->actingAs($me)->post('/games', [
            'name' => 'Los spel', 'mode' => 'add', 'is_public' => 1, 'guests' => ['Oma', 'Opa'],
        ])->assertSessionHasNoErrors();

        $game = Game::latest('id')->first()->load('ownPlayers');

        if ($start) {
            $this->actingAs($me)->post("/games/{$game->public_id}/start", [
                'order' => $game->ownPlayers->pluck('id')->all(),
                'starting_player_id' => $game->ownPlayers->firstWhere('guest_name', 'Oma')->id,
            ])->assertSessionHasNoErrors();
        }

        return $game->fresh()->load('ownPlayers');
    }

    protected function postTile(User $by, Game $game, Player $player, array $digits)
    {
        return $this->actingAs($by)->post("/games/{$game->public_id}/events", [
            'type' => 'tile', 'player_id' => $player->id,
            'digit_a' => $digits[0], 'digit_b' => $digits[1], 'digit_c' => $digits[2],
        ]);
    }
}
