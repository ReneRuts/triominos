<?php

namespace App\Actions;

use App\Models\Player;
use Illuminate\Support\Collection;

/** Creating and ordering the seats (players) of a toernooi or standalone spel. */
final class Seats
{
    /**
     * @param  array{tournament_id?:int,game_id?:int}  $owner  who the seats belong to
     * @param  array<int,string>  $friendIds
     * @param  Collection<int,string>  $guests
     */
    public static function create(array $owner, string $creatorId, array $friendIds, Collection $guests): void
    {
        $position = 0;

        foreach ([$creatorId, ...$friendIds] as $userId) {
            Player::create($owner + ['user_id' => $userId, 'position' => $position++]);
        }

        foreach ($guests as $name) {
            Player::create($owner + ['guest_name' => $name, 'position' => $position++]);
        }
    }

    /** Seating order around the table (clockwise), set once before the first spel. */
    public static function reorder(Collection $players, array $order): void
    {
        foreach (array_values($order) as $position => $playerId) {
            $players->firstWhere('id', (int) $playerId)->update(['position' => $position]);
        }
    }
}
