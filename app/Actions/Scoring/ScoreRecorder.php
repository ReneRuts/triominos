<?php

namespace App\Actions\Scoring;

use App\Enums\ScoreEventType;
use App\Exceptions\RuleViolation;
use App\Models\Game;
use App\Models\Player;
use App\Models\ScoreEvent;
use App\Models\User;
use App\Scoring\GameScorer;
use App\Scoring\ScoreCalculator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/** Everything that adds, removes or locks a score in a running spel. */
final class ScoreRecorder
{
    /** @param  array{0:int,1:int,2:int}  $digits */
    public function tile(Game $game, Player $player, array $digits, array $flags, ?int $manualPoints, User $by): ScoreEvent
    {
        $this->assertPlayable($game);

        // The bonuses depend on what was laid earlier in this spel.
        $laid = $game->events()->reorder()->where('type', ScoreEventType::Tile->value)->get();
        $flags = ['bridge' => (bool) ($flags['bridge'] ?? false), 'hexagons' => (int) ($flags['hexagons'] ?? 0)];

        try {
            $result = ScoreCalculator::tileScore($digits, $game->mode, $flags + [
                'first_stone' => $laid->isEmpty(),
                'triple_available' => ! $laid->contains(
                    fn($e) => ScoreCalculator::isTriple((int) $e->digit_a, (int) $e->digit_b, (int) $e->digit_c)
                ),
            ]);
        } catch (InvalidArgumentException $e) {
            throw new RuleViolation($e->getMessage(), 'digit_a');
        }

        return $this->create($game, $player, $by, ScoreEventType::Tile, [
            'digit_a' => $digits[0],
            'digit_b' => $digits[1],
            'digit_c' => $digits[2],
            'points' => $manualPoints ?? $result['points'],
            'is_manual' => $manualPoints !== null,
            'details' => ['flags' => $flags, 'breakdown' => $result['breakdown'], 'calculated' => $result['points']],
        ]);
    }

    public function draw(Game $game, Player $player, User $by): ScoreEvent
    {
        $this->assertTurnOf($game, $player);

        if ($game->drawsInOpenTurn() >= 3) {
            throw new RuleViolation('Je kan maximaal 3 keer een steen bijnemen per beurt.');
        }

        $result = ScoreCalculator::draw();

        return $this->create($game, $player, $by, ScoreEventType::Draw, [
            'points' => $result['points'],
            'details' => ['breakdown' => $result['breakdown']],
        ]);
    }

    public function pass(Game $game, Player $player, User $by): ScoreEvent
    {
        $this->assertTurnOf($game, $player);

        if ($game->drawsInOpenTurn() < 3) {
            throw new RuleViolation('Passen kan pas nadat er 3 keer een steen is bijgenomen.');
        }

        $result = ScoreCalculator::pass();

        return $this->create($game, $player, $by, ScoreEventType::Pass, [
            'points' => $result['points'],
            'details' => ['breakdown' => $result['breakdown']],
        ]);
    }

    /** The turn timer ran out: first a warning (if allowed), then the consequences set for this game. */
    public function timeout(Game $game, Player $player, User $by): void
    {
        $this->assertPlayable($game);

        if (! $game->turn_time_limit) {
            throw new RuleViolation('Dit spel heeft geen tijdslimiet.');
        }

        DB::transaction(function () use ($game, $player, $by) {
            if ($player->warnings_used < $game->warnings_allowed) {
                $player->increment('warnings_used');
                $left = $game->warnings_allowed - $player->warnings_used;

                $this->create($game, $player, $by, ScoreEventType::Warning, [
                    'points' => 0,
                    'details' => ['breakdown' => [['label' => "Waarschuwing ({$left} over)", 'points' => 0]]],
                ]);

                return;
            }

            $penalty = (int) $game->timeout_penalty_points;
            $this->create($game, $player, $by, ScoreEventType::Timeout, [
                'points' => -$penalty,
                'details' => ['breakdown' => [['label' => 'Tijd om', 'points' => -$penalty]], 'must_draw' => $game->timeout_draw_tile],
            ]);

            if ($game->timeout_draw_tile) {
                $draw = ScoreCalculator::draw();
                $this->create($game, $player, $by, ScoreEventType::Draw, [
                    'points' => $draw['points'],
                    'details' => ['breakdown' => $draw['breakdown'], 'reason' => 'timeout'],
                ]);
            }
        });
    }

    public function delete(Game $game, ScoreEvent $event): void
    {
        if (! $game->isActive()) {
            throw new RuleViolation('Dit spel is afgesloten.');
        }

        if ($event->locked) {
            throw new RuleViolation('Deze score is vastgezet. De spelmaker kan ze eerst losmaken.');
        }

        if ($event->type === ScoreEventType::GameEnd) {
            throw new RuleViolation('Het einde van een spel haal je weg via "Spel heropenen".');
        }

        $event->delete();

        // Bonuses of the stones after this one depend on it (first triple, 0-0-0 start).
        if ($event->type === ScoreEventType::Tile) {
            GameScorer::recalculate($game);
        }
    }

    public function toggleLock(ScoreEvent $event): void
    {
        $event->update(['locked' => ! $event->locked]);
    }

    // --- internals -------------------------------------------------------------------------

    private function assertPlayable(Game $game): void
    {
        if (! $game->isActive()) {
            throw new RuleViolation('Dit spel is afgerond.');
        }

        if (! $game->isStarted()) {
            throw new RuleViolation('Dit spel is nog niet gestart.');
        }
    }

    /** Drawing and passing only happen in the turn of the current player. */
    private function assertTurnOf(Game $game, Player $player): void
    {
        $this->assertPlayable($game);

        $current = $game->currentPlayer();
        if ($current && (int) $current->id !== (int) $player->id) {
            throw new RuleViolation('Het is niet de beurt van deze speler. Bijnemen en passen kan enkel in de beurt van ' . $current->name() . '.');
        }
    }

    private function create(Game $game, Player $player, User $by, ScoreEventType $type, array $attributes): ScoreEvent
    {
        $event = ScoreEvent::create($attributes + [
            'game_id' => $game->id,
            'player_id' => $player->id,
            'type' => $type,
            'created_by' => $by->id,
        ]);

        $game->tournament?->refreshTargetReached();

        return $event;
    }
}