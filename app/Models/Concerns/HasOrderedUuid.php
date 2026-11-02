<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Random-looking UUID as primary key (nothing to guess in a url), but still sortable on creation time:
 * UUIDv7 starts with a timestamp. Within one request the timestamp is pushed forward by a millisecond when
 * two ids are created in the same millisecond, so "order by id" always follows the order of creation.
 */
trait HasOrderedUuid
{
    use HasUuids;

    private static int $lastUuidMillis = 0;

    public function newUniqueId(): string
    {
        $now = (int) floor(microtime(true) * 1000);
        $millis = max($now, self::$lastUuidMillis + 1);
        self::$lastUuidMillis = $millis;

        return strtolower((string) Str::uuid7(Carbon::createFromTimestampMs($millis)));
    }
}