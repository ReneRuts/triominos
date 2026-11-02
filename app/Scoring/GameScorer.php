<?php

namespace App\Scoring;

use App\Enums\ScoreEventType;
use App\Models\Game;

/**
 * The bonus for the first triple (and the 0-0-0 start) depends on the stones laid before it.
 * When a stone is removed, the points of the stones after it are worked out again.
 * (Was: RoundScorer.)
 */
final class GameScorer
{
    public static function recalculate(Game $game): void
    {
        $tiles = $game->events()->where('type', ScoreEventType::Tile->value)->get();

        $first = true;
        $tripleDone = false;

        foreach ($tiles as $event) {
            $digits = [(int) $event->digit_a, (int) $event->digit_b, (int) $event->digit_c];

            // Manual totals and locked scores keep their points, but they still count as "already laid".
            if (! $event->is_manual && ! $event->locked) {
                $flags = $event->details['flags'] ?? [];
                $result = ScoreCalculator::tileScore($digits, $game->mode, [
                    'first_stone' => $first,
                    'triple_available' => ! $tripleDone,
                    'bridge' => ! empty($flags['bridge']),
                    'hexagons' => (int) ($flags['hexagons'] ?? 0),
                ]);

                $event->update([
                    'points' => $result['points'],
                    'details' => ['flags' => $flags, 'breakdown' => $result['breakdown'], 'calculated' => $result['points']],
                ]);
            }

            $first = false;
            if (ScoreCalculator::isTriple(...$digits)) {
                $tripleDone = true;
            }
        }
    }
}
