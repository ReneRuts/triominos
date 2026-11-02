<?php

namespace App\Enums;

/** Shared by tournaments and games. */
enum Status: string
{
    case Active = 'active';
    case Finished = 'finished';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Lopend',
            self::Finished => 'Afgerond',
        };
    }
}
