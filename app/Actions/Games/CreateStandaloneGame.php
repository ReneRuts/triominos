<?php

namespace App\Actions\Games;

use App\Actions\Seats;
use App\Models\Game;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** A spel played on its own, without a toernooi. Seating and starter are chosen on the spel page (BeginStandaloneGame). */
final class CreateStandaloneGame
{
    /**
     * @param  array<string,mixed>  $rules  see PlayRulesRequest::ruleAttributes()
     * @param  array<int,int>  $friendIds
     * @param  Collection<int,string>  $guests
     */
    public function handle(User $creator, ?string $name, array $rules, array $friendIds, Collection $guests): Game
    {
        return DB::transaction(function () use ($creator, $name, $rules, $friendIds, $guests) {
            $game = Game::create($rules + [
                'creator_id' => $creator->id,
                'name' => $name ?: null,
            ]);

            Seats::create(['game_id' => $game->id], $creator->id, $friendIds, $guests);

            return $game;
        });
    }
}
