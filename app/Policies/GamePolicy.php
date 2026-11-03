<?php

namespace App\Policies;

use App\Models\Game;
use App\Models\User;

class GamePolicy
{
    public function view(User $user, Game $game): bool
    {
        return $game->canView($user);
    }

    /** Only the creator of the spel enters scores. */
    public function play(User $user, Game $game): bool
    {
        return $game->isCreator($user);
    }

    /** The creator may remove a score, as long as the spel is running. */
    public function edit(User $user, Game $game): bool
    {
        return $game->isActive() && $game->isCreator($user);
    }

    /** Lock scores, reopen, change visibility. */
    public function manage(User $user, Game $game): bool
    {
        return $game->isCreator($user);
    }

    /** Only standalone games are deleted on their own; a spel in a toernooi goes with its toernooi. */
    public function delete(User $user, Game $game): bool
    {
        return $game->isStandalone() && $game->isCreator($user);
    }
}
