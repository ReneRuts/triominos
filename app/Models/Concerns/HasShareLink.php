<?php

namespace App\Models\Concerns;

use App\Models\Game;
use App\Models\Tournament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Secret live link + short game ID for guests. Used by tournaments and standalone games.
 * A model can opt out per row by overriding hasShareLink() (games inside a tournament use the tournament's link).
 */
trait HasShareLink
{
    public static function bootHasShareLink(): void
    {
        static::creating(function (Model $model) {
            if ($model->hasShareLink()) {
                $model->share_token ??= static::newToken();
                $model->join_code ??= static::newCode();
            }
        });
    }

    public function hasShareLink(): bool
    {
        return true;
    }

    public static function newToken(): string
    {
        return Str::random(40);
    }

    /** Short, easy to type ID (no 0/O/1/I). Unique across tournaments AND games, so one input field finds both. */
    public static function newCode(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
        } while (Tournament::query()->where('join_code', $code)->exists()
            || Game::query()->where('join_code', $code)->exists());

        return $code;
    }

    /** ABCD-2345 */
    public function joinCodeLabel(): string
    {
        return $this->join_code ? substr($this->join_code, 0, 4).'-'.substr($this->join_code, 4) : '';
    }

    public function regenerateShareLink(): void
    {
        $this->forceFill(['share_token' => static::newToken(), 'join_code' => static::newCode()])->save();
    }
}
