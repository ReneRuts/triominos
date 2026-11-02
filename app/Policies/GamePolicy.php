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

    /** Players of a running spel enter scores. */
    public function play(User $user, Game $game): bool
    {
        return $game->isActive() && $game->hasPlayer($user);
    }

    /** Players and the creator may remove a score. */
    public function edit(User $user, Game $game): bool
    {
        return $game->isActive() && ($game->hasPlayer($user) || $game->isCreator($user));
    }

    /** Lock scores, reopen. */
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
