<?php

namespace App\Actions\Games;

use App\Models\Game;
use App\Models\Tournament;
use Illuminate\Support\Facades\DB;

/** Makes a standalone spel or a whole toernooi (with its spellen) public or private. */
final class ChangeVisibility
{
    public function handle(Game|Tournament $subject, bool $isPublic): void
    {
        DB::transaction(function () use ($subject, $isPublic) {
            $subject->update(['is_public' => $isPublic]);

            if ($subject instanceof Tournament) {
                $subject->games()->reorder()->update(['is_public' => $isPublic]);
            }
        });
    }
}