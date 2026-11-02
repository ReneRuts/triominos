<?php

namespace Tests\Unit;

use App\Enums\GameEnding;
use App\Enums\ScoringMode;
use App\Scoring\ScoreCalculator as S;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ScoreCalculatorTest extends TestCase
{
    private const ADD = ScoringMode::Add;
    private const MUL = ScoringMode::Multiply;

    public function test_add_mode_sums_digits(): void
    {
        $this->assertSame(8, S::tileValue(3, 4, 1, self::ADD));
        $this->assertSame(8, S::tileScore([3, 4, 1], self::ADD)['points']);
    }

    public function test_multiply_mode_counts_zero_as_one(): void
    {
        $this->assertSame(12, S::tileValue(3, 4, 1, self::MUL));
        $this->assertSame(12, S::tileValue(3, 4, 0, self::MUL));
        $this->assertSame(1, S::tileValue(0, 0, 0, self::MUL));
        $this->assertSame(125, S::tileValue(5, 5, 5, self::MUL));
    }

    public function test_first_stone_of_zero_zero_zero_is_worth_forty_in_total(): void
    {
        $first = ['first_stone' => true, 'triple_available' => true];

        $this->assertSame(40, S::tileScore([0, 0, 0], self::ADD, $first)['points']);
        $this->assertSame(40, S::tileScore([0, 0, 0], self::MUL, $first)['points']);
        $this->assertSame([0, 10, 30], array_column(S::tileScore([0, 0, 0], self::ADD, $first)['breakdown'], 'points'));
    }

    public function test_zero_zero_zero_later_in_the_spel_is_worth_nothing_extra(): void
    {
        $this->assertSame(10, S::tileScore([0, 0, 0], self::ADD, ['first_stone' => false, 'triple_available' => true])['points']);
        $this->assertSame(0, S::tileScore([0, 0, 0], self::ADD, ['first_stone' => false, 'triple_available' => false])['points']);
        $this->assertSame(0, S::tileScore([0, 0, 0], self::ADD, ['first_stone' => true, 'triple_available' => false])['points']);
    }

    public function test_bridge_and_hexagon_bonuses_stack_on_the_tile_value(): void
    {
        $this->assertSame(6 + 40, S::tileScore([2, 2, 2], self::ADD, ['bridge' => true])['points']);
        $this->assertSame(8 + 50, S::tileScore([2, 2, 4], self::ADD, ['hexagons' => 1])['points']);
        $this->assertSame(8 + 70, S::tileScore([2, 2, 4], self::ADD, ['hexagons' => 3])['points']);
        $this->assertSame(8 + 40 + 50, S::tileScore([2, 2, 4], self::ADD, ['bridge' => true, 'hexagons' => 1])['points']);
    }

    public function test_only_the_first_triple_gets_the_bonus(): void
    {
        foreach ([0, 1, 2, 3, 4, 5] as $d) {
            $this->assertSame(3 * $d + 10, S::tileScore([$d, $d, $d], self::ADD, ['triple_available' => true])['points']);
        }

        $this->assertSame(3, S::tileScore([1, 1, 1], self::ADD, ['triple_available' => false])['points']);
        $this->assertSame(5, S::tileScore([1, 1, 3], self::ADD, ['triple_available' => true])['points']);
        $this->assertSame(135, S::tileScore([5, 5, 5], self::MUL, ['triple_available' => true])['points']);
    }

    public function test_digit_outside_range_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        S::tileScore([6, 1, 1], self::ADD);
    }

    public function test_draw_and_pass_penalties(): void
    {
        $this->assertSame(-5, S::draw()['points']);
        $this->assertSame(-10, S::pass()['points']);
        $this->assertSame(-25, 3 * S::draw()['points'] + S::pass()['points']);
    }

    public function test_game_end_when_a_player_empties_his_stones(): void
    {
        $this->assertSame(25 + 19, S::gameEnd(GameEnding::Emptied, 'a', ['a' => 0, 'b' => 12, 'c' => 7])['points']);
    }

    public function test_game_end_blocked(): void
    {
        $this->assertSame(24 - 4, S::gameEnd(GameEnding::Blocked, 'b', ['a' => 15, 'b' => 4, 'c' => 9])['points']);
    }

    public function test_blocked_winner_must_have_the_lowest_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        S::gameEnd(GameEnding::Blocked, 'a', ['a' => 15, 'b' => 4]);
    }
}
