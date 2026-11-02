<?php

namespace App\Actions\Games;

use App\Enums\ScoreEventType;
use App\Enums\Status;
use App\Exceptions\RuleViolation;
use App\Models\Game;
use Illuminate\Support\Facades\DB;

final class ReopenGame
{
    public function handle(Game $game): void
    {
        if ($game->isActive()) {
            throw new RuleViolation('Dit spel loopt nog.');
        }

        $end = $game->events()->reorder()->where('type', ScoreEventType::GameEnd->value);

        if ((clone $end)->where('locked', true)->exists()) {
            throw new RuleViolation('De einde-spel score is vastgezet. Maak ze eerst los.');
        }

        if (! $game->isStandalone()) {
            $tournament = $game->tournament->load('games');

            if (! $tournament->isActive() || $tournament->games->max('number') !== $game->number || $tournament->activeGame()) {
                throw new RuleViolation('Enkel het laatste afgesloten spel kan heropend worden.');
            }
        }

        DB::transaction(function () use ($game, $end) {
            $end->delete();

            $game->update([
                'status' => Status::Active,
                'ended_by' => null,
                'winner_player_id' => null,
                'remaining' => null,
                'finished_at' => null,
            ]);

            if ($game->isStandalone()) {
                $game->ownPlayers()->update(['final_points' => null, 'is_winner' => false]);
            }
        });
    }
}
