<?php

namespace App\Enums;

enum ScoringMode: string
{
    case Add = 'add';
    case Multiply = 'multiply';

    public function label(): string
    {
        return match ($this) {
            self::Add => 'Optellen',
            self::Multiply => 'Vermenigvuldigen',
        };
    }
}
