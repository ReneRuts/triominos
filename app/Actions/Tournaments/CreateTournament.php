<?php

namespace App\Actions\Tournaments;

use App\Actions\Seats;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class CreateTournament
{
    /**
     * @param  array<string,mixed>  $rules  see PlayRulesRequest::ruleAttributes()
     * @param  array<int,int>  $friendIds
     * @param  Collection<int,string>  $guests
     */
    public function handle(User $creator, string $name, int $targetScore, array $rules, array $friendIds, Collection $guests): Tournament
    {
        return DB::transaction(function () use ($creator, $name, $targetScore, $rules, $friendIds, $guests) {
            $tournament = Tournament::create($rules + [
                'creator_id' => $creator->id,
                'name' => $name,
                'target_score' => $targetScore,
            ]);

            Seats::create(['tournament_id' => $tournament->id], $creator->id, $friendIds, $guests);

            return $tournament;
        });
    }
}
