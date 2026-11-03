<?php

namespace App\Models;

use App\Enums\ScoringMode;
use App\Enums\Status;
use App\Models\Concerns\HasPublicId;
use App\Models\Concerns\HasShareLink;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * A toernooi: a fixed group of players who play several spellen.
 * The points of all spellen add up; once somebody reaches the target the toernooi ends after the current spel.
 */
class Tournament extends Model
{
    use HasPublicId, HasShareLink;

    protected $fillable = [
        'creator_id',
        'name',
        'status',
        'mode',
        'target_score',
        'is_public',
        'turn_time_limit',
        'warnings_allowed',
        'timeout_penalty_points',
        'timeout_draw_tile',
        'target_reached_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => Status::class,
            'mode' => ScoringMode::class,
            'is_public' => 'boolean',
            'timeout_draw_tile' => 'boolean',
            'target_reached_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    // --- Relations -------------------------------------------------------------------------

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class)->orderBy('position');
    }

    public function games(): HasMany
    {
        return $this->hasMany(Game::class)->orderBy('number');
    }

    public function scoreEvents(): HasManyThrough
    {
        return $this->hasManyThrough(ScoreEvent::class, Game::class);
    }

    // --- State -----------------------------------------------------------------------------

    public function isActive(): bool
    {
        return $this->status === Status::Active;
    }

    public function isCreator(?User $user): bool
    {
        return $user !== null && (string) $user->id === (string) $this->creator_id;
    }

    public function hasPlayer(?User $user): bool
    {
        return $user !== null && $this->players->contains(fn($p) => (string) $p->user_id === (string) $user->id);
    }

    /** Active toernooi: players only. Finished: players, and everybody with an account when it is public. */
    public function canView(?User $user): bool
    {
        return $this->hasPlayer($user) || $this->isCreator($user) || (! $this->isActive() && $this->is_public);
    }

    public function activeGame(): ?Game
    {
        return $this->games->firstWhere('status', Status::Active);
    }

    public function lastGame(): ?Game
    {
        return $this->games->sortByDesc('number')->first();
    }

    /** The rules every spel of this toernooi starts with. */
    public function playRules(): array
    {
        return $this->only(['mode', 'is_public', 'turn_time_limit', 'warnings_allowed', 'timeout_penalty_points', 'timeout_draw_tile']);
    }

    // --- Scores ----------------------------------------------------------------------------

    /** @return array<int|string,int> player_id => total points over all spellen */
    public function totals(): array
    {
        $sums = $this->scoreEvents()
            ->selectRaw('score_events.player_id as player_id, SUM(score_events.points) as total')
            ->groupBy('score_events.player_id')
            ->pluck('total', 'player_id');

        return $this->players->mapWithKeys(fn(Player $p) => [$p->id => (int) ($sums[$p->id] ?? 0)])->all();
    }

    /**
     * Once somebody reaches the target the toernooi ends after the current spel, even if that
     * player drops back below the target before the spel is over. So this is remembered.
     */
    public function refreshTargetReached(): void
    {
        if (
            $this->target_reached_at === null
            && $this->scoreEvents()->exists()
            && max($this->totals() ?: [0]) >= $this->target_score
        ) {
            $this->update(['target_reached_at' => now()]);
        }
    }

    /** Fingerprint used by open pages to notice that somebody else changed something. */
    public function version(): string
    {
        $events = $this->scoreEvents()
            ->selectRaw('COUNT(*) as c, COALESCE(MAX(score_events.id), 0) as m')
            ->first();

        return md5(implode('|', [
            $this->status->value,
            $this->target_score,
            $events->c,
            $events->m,
            $this->scoreEvents()->where('score_events.locked', true)->count(),
            $this->games()->count(),
            $this->games()->where('status', Status::Finished)->count(),
            $this->share_token,
        ]));
    }
}