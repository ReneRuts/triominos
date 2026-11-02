<?php

namespace App\Actions\Games;

use App\Actions\Seats;
use App\Exceptions\RuleViolation;
use App\Models\Game;
use Illuminate\Support\Facades\DB;

final class BeginStandaloneGame
{
    /** @param  array<int,int>  $order */
    public function handle(Game $game, int $startingPlayerId, array $order): void
    {
        if (! $game->isStandalone() || $game->isStarted()) {
            throw new RuleViolation('Dit spel is al gestart.');
        }

        DB::transaction(function () use ($game, $startingPlayerId, $order) {
            Seats::reorder($game->ownPlayers, $order);
            $game->update(['starting_player_id' => $startingPlayerId]);
        });
    }
}
