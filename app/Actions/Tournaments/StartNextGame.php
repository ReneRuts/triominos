<?php

namespace App\Actions\Tournaments;

use App\Actions\Seats;
use App\Exceptions\RuleViolation;
use App\Models\Game;
use App\Models\Tournament;
use Illuminate\Support\Facades\DB;

/** Starts the next spel of a toernooi. */
final class StartNextGame
{
    /** @param  array<int,int>|null  $order  seating order, only for the first spel */
    public function handle(Tournament $tournament, int $startingPlayerId, ?array $order): Game
    {
        $tournament->load('players', 'games');

        if ($tournament->activeGame()) {
            throw new RuleViolation('Er loopt al een spel.');
        }

        if ($tournament->target_reached_at) {
            throw new RuleViolation("Het doel van {$tournament->target_score} punten is bereikt. Rond het toernooi af.");
        }

        return DB::transaction(function () use ($tournament, $startingPlayerId, $order) {
            if ($tournament->games->isEmpty() && $order) {
                Seats::reorder($tournament->players, $order);
            }

            return Game::create($tournament->playRules() + [
                'tournament_id' => $tournament->id,
                'creator_id' => $tournament->creator_id,
                'number' => ($tournament->lastGame()?->number ?? 0) + 1,
                'starting_player_id' => $startingPlayerId,
            ]);
        });
    }
}
