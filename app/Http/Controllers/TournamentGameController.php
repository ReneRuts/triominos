<?php

namespace App\Http\Controllers;

use App\Actions\Tournaments\StartNextGame;
use App\Http\Requests\StartGameRequest;
use App\Models\Tournament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

/** Starting the next spel inside a toernooi. */
class TournamentGameController extends Controller
{
    public function store(StartGameRequest $request, Tournament $tournament, StartNextGame $start): RedirectResponse
    {
        Gate::authorize('play', $tournament);

        $game = $start->handle(
            $tournament,
            (int) $request->validated('starting_player_id'),
            $request->validated('order'),
        );

        return redirect()->route('games.show', $game);
    }
}
