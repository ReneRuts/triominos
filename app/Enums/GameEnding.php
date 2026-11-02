<?php

namespace App\Enums;

/** How a spel ended. */
enum GameEnding: string
{
    case Emptied = 'emptied';
    case Blocked = 'blocked';

    public function label(): string
    {
        return match ($this) {
            self::Emptied => 'Stenen kwijt',
            self::Blocked => 'Geblokkeerd',
        };
    }
}
