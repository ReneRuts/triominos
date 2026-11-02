<?php

namespace App\Actions\Games;

use App\Enums\GameEnding;
use App\Enums\ScoreEventType;
use App\Enums\Status;
use App\Exceptions\RuleViolation;
use App\Models\Game;
use App\Models\ScoreEvent;
use App\Models\User;
use App\Scoring\ScoreCalculator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/** Ends a spel: somebody emptied their stones, or the game is blocked. Only the winner scores. */
final class FinishGame
{
    /** @param  array<int|string,mixed>  $remainingInput  remaining stone value per player id */
    public function handle(Game $game, GameEnding $ending, int $winnerId, array $remainingInput, User $by): void
    {
        $remaining = [];
        foreach ($game->players->pluck('id') as $id) {
            $remaining[$id] = (int) ($remainingInput[$id] ?? 0);
        }

        if ($ending === GameEnding::Emptied) {
            $remaining[$winnerId] = 0;
        }

        try {
            $result = ScoreCalculator::gameEnd($ending, $winnerId, $remaining);
        } catch (InvalidArgumentException $e) {
            throw new RuleViolation($e->getMessage());
        }

        DB::transaction(function () use ($game, $ending, $winnerId, $remaining, $result, $by) {
            ScoreEvent::create([
                'game_id' => $game->id,
                'player_id' => $winnerId,
                'type' => ScoreEventType::GameEnd,
                'points' => $result['points'],
                'details' => ['breakdown' => $result['breakdown'], 'ended_by' => $ending->value],
                'created_by' => $by->id,
            ]);

            $game->update([
                'status' => Status::Finished,
                'ended_by' => $ending,
                'winner_player_id' => $winnerId,
                'remaining' => $remaining,
                'finished_at' => now(),
            ]);

            // A standalone spel is the whole match: store the result on the seats for the ranking.
            if ($game->isStandalone()) {
                $totals = $game->totals();
                foreach ($game->players as $player) {
                    $player->update(['final_points' => $totals[$player->id], 'is_winner' => $player->id === $winnerId]);
                }
            }
        });

        $game->tournament?->refreshTargetReached();
    }
}
