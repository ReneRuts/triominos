<?php

namespace App\Enums;

enum ScoreEventType: string
{
    case Tile = 'tile';
    case Draw = 'draw';
    case Pass = 'pass';
    case Warning = 'warning';
    case Timeout = 'timeout';
    case GameEnd = 'game_end';

    /** A turn is over once a stone is laid or the player passes. */
    public function endsTurn(): bool
    {
        return $this === self::Tile || $this === self::Pass;
    }

    /** @return array<int,string> database values of the types that end a turn */
    public static function turnEnders(): array
    {
        return [self::Tile->value, self::Pass->value];
    }
}
