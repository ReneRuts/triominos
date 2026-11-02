<?php

namespace App\Actions\Tournaments;

use App\Enums\Status;
use App\Exceptions\RuleViolation;
use App\Models\Tournament;
use Illuminate\Support\Facades\DB;

/** Ends the toernooi: the player with the most points over all spellen wins. */
final class FinishTournament
{
    public function handle(Tournament $tournament): void
    {
        $tournament->load('players', 'games');
        $active = $tournament->activeGame();

        if ($active?->events()->exists()) {
            throw new RuleViolation('Sluit eerst het lopende spel af voordat je het toernooi beëindigt.');
        }

        if (! $tournament->scoreEvents()->exists()) {
            throw new RuleViolation('Er zijn nog geen punten ingevuld. Verwijder het toernooi als je het niet wil bewaren.');
        }

        DB::transaction(function () use ($tournament, $active) {
            $active?->delete(); // an empty spel that was never played

            $totals = $tournament->totals();
            $max = max($totals);

            foreach ($tournament->players as $player) {
                $player->update([
                    'final_points' => $totals[$player->id],
                    'is_winner' => $totals[$player->id] === $max,
                ]);
            }

            $tournament->update(['status' => Status::Finished, 'finished_at' => now()]);
        });
    }
}
