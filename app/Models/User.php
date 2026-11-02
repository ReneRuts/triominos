<?php

namespace App\Models;

use App\Models\Concerns\HasOrderedUuid;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, HasOrderedUuid, Notifiable;

    protected $fillable = ['username', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    /** Every seat this account has had, in tournaments and in standalone games. */
    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    /** IDs of everybody with an accepted friendship with this user. */
    public function friendIds(): array
    {
        return Friendship::query()
            ->where('status', 'accepted')
            ->where(fn ($q) => $q->where('requester_id', $this->id)->orWhere('addressee_id', $this->id))
            ->get(['requester_id', 'addressee_id'])
            ->map(fn ($f) => (int) ((int) $f->requester_id === (int) $this->id ? $f->addressee_id : $f->requester_id))
            ->unique()
            ->values()
            ->all();
    }

    /** @return Collection<int, User> */
    public function friends(): Collection
    {
        return static::query()->whereIn('id', $this->friendIds())->orderBy('username')->get();
    }

    public function isFriendWith(User $other): bool
    {
        return in_array($other->id, $this->friendIds(), true);
    }

    public function incomingRequests()
    {
        return Friendship::query()->with('requester')
            ->where('addressee_id', $this->id)->where('status', 'pending')->latest()->get();
    }

    public function outgoingRequests()
    {
        return Friendship::query()->with('addressee')
            ->where('requester_id', $this->id)->where('status', 'pending')->latest()->get();
    }

    /** Case-insensitive lookup on username. */
    public static function findByUsername(string $username): ?self
    {
        return static::query()->whereRaw('LOWER(username) = ?', [mb_strtolower($username)])->first();
    }
}