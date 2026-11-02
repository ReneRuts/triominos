<?php

namespace App\Scoring;

use App\Enums\ScoreEventType;
use App\Models\Player;
use App\Models\ScoreEvent;
use Illuminate\Support\Collection;

/**
 * Groups score events into turns: the stones a player drew, followed by the stone laid or the pass that ended the turn.
 */
final class Turns
{
    /**
     * @param  iterable<int,ScoreEvent>  $events  oldest first, all from the same spel
     * @return array<int,array{id:int,events:array<int,ScoreEvent>,player:Player,total:int,open:bool,game_end:bool}>
     */
    public static function group(iterable $events): array
    {
        $turns = [];
        $current = null;

        $close = function (bool $open) use (&$turns, &$current) {
            if ($current === null) {
                return;
            }
            $list = $current['events'];
            $last = end($list);
            $turns[] = [
                'id' => (int) $last->id,
                'events' => $list,
                // the player of the stone/pass that ended the turn, or of the first draw while the turn is still going
                'player' => $open ? $list[0]->player : $last->player,
                'total' => (int) array_sum(array_map(fn ($e) => $e->points, $list)),
                'open' => $open,
                'game_end' => false,
            ];
            $current = null;
        };

        foreach ($events as $event) {
            if ($event->type === ScoreEventType::GameEnd) {
                $close(true);
                $turns[] = [
                    'id' => (int) $event->id,
                    'events' => [$event],
                    'player' => $event->player,
                    'total' => (int) $event->points,
                    'open' => false,
                    'game_end' => true,
                ];

                continue;
            }

            $current ??= ['events' => []];
            $current['events'][] = $event;

            if ($event->type->endsTurn()) {
                $close(false);
            }
        }

        $close(true);

        return $turns;
    }

    /** Same, but for several spellen: groups per spel and returns the turns in the order they happened. */
    public static function forGames(Collection $events): array
    {
        $all = [];
        foreach ($events->groupBy('game_id') as $gameEvents) {
            foreach (self::group($gameEvents) as $turn) {
                $all[] = $turn + ['game_id' => $gameEvents->first()->game_id];
            }
        }
        usort($all, fn ($a, $b) => $a['id'] <=> $b['id']);

        return $all;
    }
}
