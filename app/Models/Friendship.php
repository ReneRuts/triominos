<?php

namespace App\Models;

use App\Models\Concerns\HasOrderedUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Friendship extends Model
{
    use HasOrderedUuid;

    protected $fillable = ['requester_id', 'addressee_id', 'status'];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function addressee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'addressee_id');
    }

    /** The row between two users, in either direction. */
    public static function between(string $a, string $b): ?self
    {
        return static::query()
            ->where(fn($q) => $q->where('requester_id', $a)->where('addressee_id', $b))
            ->orWhere(fn($q) => $q->where('requester_id', $b)->where('addressee_id', $a))
            ->first();
    }

    public function involves(User $user): bool
    {
        return in_array($user->id, [$this->requester_id, $this->addressee_id], true);
    }

    public function other(User $user): User
    {
        return $this->requester_id === $user->id ? $this->addressee : $this->requester;
    }
}