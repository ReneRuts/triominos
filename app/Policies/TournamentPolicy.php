<?php

namespace App\Policies;

use App\Models\Tournament;
use App\Models\User;

class TournamentPolicy
{
    public function view(User $user, Tournament $tournament): bool
    {
        return $tournament->canView($user);
    }

    /** Players of a running toernooi: start spellen, change the target. */
    public function play(User $user, Tournament $tournament): bool
    {
        return $tournament->isActive() && $tournament->hasPlayer($user);
    }

    public function finish(User $user, Tournament $tournament): bool
    {
        return $tournament->isActive() && $tournament->isCreator($user);
    }

    public function manage(User $user, Tournament $tournament): bool
    {
        return $tournament->isCreator($user);
    }
}
