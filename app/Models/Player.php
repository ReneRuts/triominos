<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A seat at the table: an account or a guest. Belongs to a toernooi or to one standalone spel. */
class Player extends Model
{
    protected $fillable = [
        'tournament_id', 'game_id', 'user_id', 'guest_name', 'position',
        'warnings_used', 'final_points', 'is_winner',
    ];

    protected function casts(): array
    {
        return ['is_winner' => 'boolean'];
    }

    /** Username of the account, or the typed name of a guest. */
    public function name(): string
    {
        return $this->user?->username ?? (string) $this->guest_name;
    }

    public function isGuest(): bool
    {
        return $this->user_id === null;
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ScoreEvent::class);
    }
}
