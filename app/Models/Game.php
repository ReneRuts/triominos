<?php

namespace App\Models;

use App\Enums\GameEnding;
use App\Enums\ScoreEventType;
use App\Enums\ScoringMode;
use App\Enums\Status;
use App\Models\Concerns\HasPublicId;
use App\Models\Concerns\HasShareLink;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * A spel (was: ronde): one hand of Triominos. It is part of a toernooi, or played on its own.
 * It ends when somebody empties their tiles or the game is blocked; only the winner scores for that.
 *
 * Players: a spel in a toernooi uses the seats of the toernooi, a standalone spel has its own ($game->players).
 */
class Game extends Model
{
    use HasPublicId, HasShareLink;

    protected $fillable = [
        'tournament_id',
        'creator_id',
        'name',
        'number',
        'status',
        'mode',
        'is_public',
        'turn_time_limit',
        'warnings_allowed',
        'timeout_penalty_points',
        'timeout_draw_tile',
        'starting_player_id',
        'winner_player_id',
        'ended_by',
        'remaining',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => Status::class,
            'mode' => ScoringMode::class,
            'ended_by' => GameEnding::class,
            'is_public' => 'boolean',
            'timeout_draw_tile' => 'boolean',
            'remaining' => 'array',
            'finished_at' => 'datetime',
        ];
    }

    /** Only standalone games get their own live link; tournament games use the link of the toernooi. */
    public function hasShareLink(): bool
    {
        return $this->tournament_id === null;
    }

    // --- Relations -------------------------------------------------------------------------

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /** Seats of a standalone spel. For the seats you should show, use $game->players. */
    public function ownPlayers(): HasMany
    {
        return $this->hasMany(Player::class)->orderBy('position');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'winner_player_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(ScoreEvent::class)->orderBy('id');
    }

    /** @return Attribute<Collection<int,Player>,never> */
    protected function players(): Attribute
    {
        return Attribute::get(fn() => $this->tournament_id !== null
            ? $this->tournament->players
            : $this->ownPlayers);
    }

    // --- State -----------------------------------------------------------------------------

    public function isStandalone(): bool
    {
        return $this->tournament_id === null;
    }

    /** A spel is started once seating and the starting player are known. */
    public function isStarted(): bool
    {
        return $this->starting_player_id !== null;
    }

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

    /** Spel inside a toernooi: follows the toernooi. Standalone: same rule as for a toernooi. */
    public function canView(?User $user): bool
    {
        if ($this->tournament_id !== null) {
            return $this->tournament->canView($user);
        }

        return $this->hasPlayer($user) || $this->isCreator($user) || (! $this->isActive() && $this->is_public);
    }

    public function title(): string
    {
        return $this->name ?: 'Spel ' . $this->number;
    }

    // --- Turns -----------------------------------------------------------------------------

    /** Stones drawn since the last stone or pass: at most 3 per turn. */
    public function drawsInOpenTurn(): int
    {
        $lastEnd = (int) $this->events()->reorder()->whereIn('type', ScoreEventType::turnEnders())->max('id');

        return $this->events()->reorder()
            ->where('type', ScoreEventType::Draw->value)
            ->where('id', '>', $lastEnd)
            ->count();
    }

    /** The player whose turn it is: starting player + number of events that end a turn (tile or pass). */
    public function currentPlayer(): ?Player
    {
        $players = $this->players->values();

        if ($players->isEmpty() || ! $this->isActive()) {
            return null;
        }

        $start = $players->search(fn(Player $p) => (string) $p->id === (string) $this->starting_player_id);
        $start = $start === false ? 0 : $start;
        $turns = $this->events()->reorder()->whereIn('type', ScoreEventType::turnEnders())->count();

        return $players[($start + $turns) % $players->count()];
    }

    // --- Scores ----------------------------------------------------------------------------

    /** @return array<int|string,int> player_id => points in this spel */
    public function totals(): array
    {
        $sums = $this->events()->reorder()
            ->selectRaw('player_id, SUM(points) as total')
            ->groupBy('player_id')
            ->pluck('total', 'player_id');

        return $this->players->mapWithKeys(fn(Player $p) => [$p->id => (int) ($sums[$p->id] ?? 0)])->all();
    }

    /** Fingerprint used by open pages to notice that somebody else changed something. */
    public function version(): string
    {
        $events = $this->events()->reorder()
            ->selectRaw('COUNT(*) as c, COALESCE(MAX(id), 0) as m')
            ->first();

        return md5(implode('|', [
            $this->status->value,
            $events->c,
            $events->m,
            $this->events()->reorder()->where('locked', true)->count(),
            $this->share_token,
        ]));
    }
}