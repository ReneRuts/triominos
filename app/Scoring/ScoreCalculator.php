<?php

namespace App\Scoring;

use App\Enums\GameEnding;
use App\Enums\ScoringMode;
use InvalidArgumentException;

/**
 * Pure scoring rules for Triominos. No framework dependencies, so it can be unit tested on its own.
 */
final class ScoreCalculator
{
    public const MAX_DIGIT = 5;
    public const TRIPLE_BONUS = 10;      // first stone with three equal digits in a spel
    public const START_000_TOTAL = 40;   // 0-0-0 as the very first stone of a spel is worth 40 in total
    public const BRIDGE_BONUS = 40;
    public const HEXAGON_BONUS = [1 => 50, 2 => 60, 3 => 70];
    public const EMPTIED_BONUS = 25;
    public const DRAW_PENALTY = 5;
    public const PASS_PENALTY = 10;

    /** When multiplying a 0 counts as this value, otherwise every stone with a 0 would be worth 0. */
    public const ZERO_MULTIPLY_VALUE = 1;

    public static function tileValue(int $a, int $b, int $c, ScoringMode $mode): int
    {
        if ($mode === ScoringMode::Multiply) {
            $z = fn (int $n) => $n === 0 ? self::ZERO_MULTIPLY_VALUE : $n;

            return $z($a) * $z($b) * $z($c);
        }

        return $a + $b + $c;
    }

    public static function isTriple(int $a, int $b, int $c): bool
    {
        return $a === $b && $b === $c;
    }

    /**
     * @param  array{0:int,1:int,2:int}  $digits
     * @param  array{first_stone?:bool,triple_available?:bool,bridge?:bool,hexagons?:int}  $opts
     *         first_stone: this is the first stone of the spel.
     *         triple_available: nobody laid a stone with three equal digits yet in this spel.
     * @return array{points:int, value:int, breakdown:array<int,array{label:string,points:int}>}
     */
    public static function tileScore(array $digits, ScoringMode $mode, array $opts = []): array
    {
        [$a, $b, $c] = array_values($digits);

        foreach ([$a, $b, $c] as $d) {
            if ($d < 0 || $d > self::MAX_DIGIT) {
                throw new InvalidArgumentException("Cijfer {$d} valt buiten 0-".self::MAX_DIGIT.'.');
            }
        }

        $hexagons = (int) ($opts['hexagons'] ?? 0);
        if ($hexagons < 0 || $hexagons > 3) {
            throw new InvalidArgumentException('Aantal zeshoeken moet 0 tot 3 zijn.');
        }

        $value = self::tileValue($a, $b, $c, $mode);

        $breakdown = [[
            'label' => sprintf('Steen %d-%d-%d', $a, $b, $c).($mode === ScoringMode::Multiply ? ' (vermenigvuldigd)' : ''),
            'points' => $value,
        ]];

        // House rule: the first stone with three equal digits of the spel gets a bonus (0-0-0 up to 5-5-5).
        if (self::isTriple($a, $b, $c) && ! empty($opts['triple_available'])) {
            $breakdown[] = ['label' => 'Eerste steen met drie dezelfde cijfers van het spel', 'points' => self::TRIPLE_BONUS];

            // 0-0-0 as the very first stone of the spel: the stone is worth 40 in total.
            if ($a === 0 && ! empty($opts['first_stone'])) {
                $breakdown[] = [
                    'label' => 'Start met 0-0-0: steen telt 40',
                    'points' => self::START_000_TOTAL - $value - self::TRIPLE_BONUS,
                ];
            }
        }

        if (! empty($opts['bridge'])) {
            $breakdown[] = ['label' => 'Brug gesloten', 'points' => self::BRIDGE_BONUS];
        }

        if ($hexagons > 0) {
            $breakdown[] = [
                'label' => $hexagons === 1 ? 'Zeshoek' : $hexagons.' zeshoeken',
                'points' => self::HEXAGON_BONUS[$hexagons],
            ];
        }

        return [
            'points' => array_sum(array_column($breakdown, 'points')),
            'value' => $value,
            'breakdown' => $breakdown,
        ];
    }

    /** @return array{points:int, breakdown:array<int,array{label:string,points:int}>} */
    public static function draw(): array
    {
        return ['points' => -self::DRAW_PENALTY, 'breakdown' => [['label' => 'Steen gepakt', 'points' => -self::DRAW_PENALTY]]];
    }

    /** @return array{points:int, breakdown:array<int,array{label:string,points:int}>} */
    public static function pass(): array
    {
        return ['points' => -self::PASS_PENALTY, 'breakdown' => [['label' => 'Gepast', 'points' => -self::PASS_PENALTY]]];
    }

    /**
     * Points for the winner of a spel. Only the winner scores.
     *
     * - Emptied: winner played all stones: 25 + the value of all stones left with the opponents.
     * - Blocked: nobody can play: the winner has the lowest remaining value and gets
     *   (opponents' remaining) minus (own remaining).
     *
     * @param  array<int|string,int>  $remaining  remaining stone value per player key
     * @return array{points:int, breakdown:array<int,array{label:string,points:int}>}
     */
    public static function gameEnd(GameEnding $type, int|string $winnerKey, array $remaining): array
    {
        if (! array_key_exists($winnerKey, $remaining)) {
            throw new InvalidArgumentException('Winnaar ontbreekt in de overgebleven stenen.');
        }

        foreach ($remaining as $value) {
            if ($value < 0) {
                throw new InvalidArgumentException('Overgebleven waarde kan niet negatief zijn.');
            }
        }

        $own = (int) $remaining[$winnerKey];
        $others = array_sum($remaining) - $own;

        if ($type === GameEnding::Emptied) {
            if ($own !== 0) {
                throw new InvalidArgumentException('De speler die al zijn stenen kwijt is heeft niets meer over.');
            }

            return [
                'points' => self::EMPTIED_BONUS + $others,
                'breakdown' => [
                    ['label' => 'Als eerste stenen kwijt', 'points' => self::EMPTIED_BONUS],
                    ['label' => 'Stenen van tegenstanders', 'points' => $others],
                ],
            ];
        }

        if ($own !== min($remaining)) {
            throw new InvalidArgumentException('Bij een geblokkeerd spel wint de speler met de laagste waarde.');
        }

        return [
            'points' => $others - $own,
            'breakdown' => [
                ['label' => 'Stenen van tegenstanders', 'points' => $others],
                ['label' => 'Eigen overgebleven stenen', 'points' => -$own],
            ],
        ];
    }
}
