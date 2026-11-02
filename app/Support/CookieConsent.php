<?php

namespace App\Support;

/** Reads the visitor's cookie choice (set by resources/js/components/cookieConsent.js). */
final class CookieConsent
{
    public const ALL = 'all';
    public const NECESSARY = 'necessary';

    /** 'all', 'necessary', or null when nothing (valid) was chosen yet. Value in the cookie: "all:1" (level:version). */
    public static function level(): ?string
    {
        $raw = request()->cookie(config('cookies.name'));

        if (! is_string($raw)) {
            return null;
        }

        [$level, $version] = array_pad(explode(':', $raw, 2), 2, null);

        if ((string) $version !== (string) config('cookies.version')) {
            return null; // version bumped: ask again
        }

        return in_array($level, [self::ALL, self::NECESSARY], true) ? $level : null;
    }

    public static function decided(): bool
    {
        return self::level() !== null;
    }

    /** True when optional cookies / third-party scripts may be loaded. */
    public static function accepted(): bool
    {
        return self::level() === self::ALL;
    }
}
