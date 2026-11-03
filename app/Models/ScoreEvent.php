<?php

namespace App\Models;

use App\Enums\ScoreEventType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScoreEvent extends Model
{
    protected $fillable = [
        'game_id', 'player_id', 'type', 'digit_a', 'digit_b', 'digit_c',
        'points', 'is_manual', 'details', 'locked', 'created_by',
    ];

    protected $touches = ['game'];

    protected function casts(): array
    {
        return [
            'type' => ScoreEventType::class,
            'is_manual' => 'boolean',
            'locked' => 'boolean',
            'details' => 'array',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function label(): string
    {
        return match ($this->type) {
            ScoreEventType::Tile => sprintf('Steen %d-%d-%d', $this->digit_a, $this->digit_b, $this->digit_c),
            ScoreEventType::Draw => 'Steen gepakt',
            ScoreEventType::Pass => 'Gepast',
            ScoreEventType::Warning => 'Waarschuwing (tijd om)',
            ScoreEventType::Timeout => 'Tijd om',
            ScoreEventType::GameEnd => 'Einde spel',
        };
    }

    /** Short text describing how the points came about. */
    public function summary(): string
    {
        $parts = collect($this->details['breakdown'] ?? [])
            ->map(fn ($b) => $b['label'].' '.($b['points'] >= 0 ? '+' : '').$b['points']);

        if ($this->is_manual) {
            $parts = $parts->prepend('handmatig totaal');
        }

        return $parts->implode(' · ');
    }
}