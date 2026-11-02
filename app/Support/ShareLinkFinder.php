<?php

namespace App\Support;

use App\Models\Game;
use App\Models\Tournament;

/** Finds the toernooi or standalone spel behind a live link or a short game ID. */
final class ShareLinkFinder
{
    public static function byToken(string $token): Tournament|Game|null
    {
        if (strlen($token) !== 40) {
            return null;
        }

        return Tournament::query()->where('share_token', $token)->first()
            ?? Game::query()->where('share_token', $token)->first();
    }

    /** A pasted live link, the bare 40-character secret, or the short ID like ABCD-2345. */
    public static function byInput(string $input): Tournament|Game|null
    {
        $input = trim($input);

        if (preg_match('~/live/([A-Za-z0-9]{40})~', $input, $m) || preg_match('/^([A-Za-z0-9]{40})$/', $input, $m)) {
            return self::byToken($m[1]);
        }

        $code = preg_replace('/[^A-Za-z0-9]/', '', $input);
        if (strlen($code) !== 8) {
            return null;
        }

        $found = Tournament::query()->where('join_code', $code)->first()
            ?? Game::query()->where('join_code', $code)->first();
        if ($found) {
            return $found;
        }

        // Typed in another case: accept it when exactly one match exists.
        $lower = strtolower($code);
        $matches = Tournament::query()->whereRaw('LOWER(join_code) = ?', [$lower])->limit(2)->get()
            ->concat(Game::query()->whereRaw('LOWER(join_code) = ?', [$lower])->limit(2)->get());

        return $matches->count() === 1 ? $matches->first() : null;
    }
}
