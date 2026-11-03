<?php

namespace App\Console\Commands;

use App\Enums\ScoreEventType;
use App\Enums\Status;
use App\Models\Game;
use Illuminate\Console\Command;

class CleanupAbandonedGames extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'games:cleanup';

    /**
     * The console command description.
     */
    protected $description = 'Sluit spellen met een einde af en verwijder oude open spellen zonder einde.';

    public function handle()
    {
        // Zoek alle spellen die nog 'active' zijn, maar meer dan 24 uur oud zijn
        $threshold = now()->subDay();

        $games = Game::where('status', Status::Active)
            ->where('updated_at', '<', $threshold)
            ->get();

        $closed = 0;
        $deleted = 0;

        foreach ($games as $game) {
            // Controleer of er een "Einde spel" event in de beurten zit
            $hasEndEvent = $game->events()->where('type', ScoreEventType::GameEnd)->exists();

            if ($hasEndEvent) {
                // Er is een eindscore, dus we sluiten het spel netjes af
                $game->update([
                    'status' => Status::Finished,
                    'finished_at' => $game->updated_at,
                ]);
                $closed++;
            } else {
                // Geen eindscore aanwezig? Dan verwijderen we het spel
                $game->delete();
                $deleted++;
            }
        }

        $this->info("Klaar! {$closed} spellen afgesloten, {$deleted} spellen verwijderd.");
    }
}