<?php

namespace App\Http\Controllers;

use App\Actions\Tournaments\CreateTournament;
use App\Actions\Tournaments\FinishTournament;
use App\Enums\Status;
use App\Http\Requests\StoreTournamentRequest;
use App\Models\Tournament;
use App\Snapshots\Snapshot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TournamentController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $tournaments = Tournament::query()
            ->with('players.user', 'creator')
            ->where(fn ($q) => $q->where('creator_id', $userId)
                ->orWhereHas('players', fn ($p) => $p->where('user_id', $userId)))
            ->latest('id')
            ->get();

        return view('tournaments.index', [
            'active' => $tournaments->where('status', Status::Active),
            'finished' => $tournaments->where('status', Status::Finished),
        ]);
    }

    public function create(Request $request)
    {
        // "Toernooi 3" = the third toernooi this player started today.
        $today = Tournament::query()
            ->where('creator_id', $request->user()->id)
            ->where('created_at', '>=', now()->startOfDay())
            ->count();

        return view('tournaments.create', [
            'friends' => $request->user()->friends(),
            'defaultName' => 'Toernooi '.($today + 1),
        ]);
    }

    public function store(StoreTournamentRequest $request, CreateTournament $create): RedirectResponse
    {
        $tournament = $create->handle(
            $request->user(),
            $request->validated('name'),
            (int) $request->validated('target_score'),
            $request->ruleAttributes(),
            $request->friendIds(),
            $request->guestNames(),
        );

        return redirect()->route('tournaments.show', $tournament);
    }

    public function show(Request $request, Tournament $tournament)
    {
        Gate::authorize('view', $tournament);
        $tournament->load('players.user', 'games', 'creator');

        $activeGame = $tournament->activeGame();

        return view('tournaments.show', [
            'tournament' => $tournament,
            'snapshot' => Snapshot::forTournament($tournament),
            'activeGame' => $activeGame,
            'lastGame' => $tournament->lastGame(),
            'firstGame' => $tournament->games->isEmpty(),
            'canPlay' => Gate::allows('play', $tournament),
            'isCreator' => $tournament->isCreator($request->user()),
        ]);
    }

    /** Lightweight check so open pages can notice changes made by other players. */
    public function version(Tournament $tournament): JsonResponse
    {
        Gate::authorize('view', $tournament);

        return response()->json(['version' => $tournament->version()]);
    }

    public function finish(Tournament $tournament, FinishTournament $finish): RedirectResponse
    {
        Gate::authorize('finish', $tournament);

        $finish->handle($tournament);

        return redirect()->route('tournaments.show', $tournament)->with('status', 'Het toernooi is afgerond.');
    }

    /** The target score can be changed while playing. */
    public function updateTarget(Request $request, Tournament $tournament): RedirectResponse
    {
        Gate::authorize('play', $tournament);

        $data = $request->validate([
            'target_score' => ['required', 'integer', 'min:50', 'max:5000'],
        ], [
            'target_score.required' => 'Vul een doelscore in.',
            'target_score.integer' => 'De doelscore moet een getal zijn.',
            'target_score.min' => 'De doelscore is minstens 50.',
            'target_score.max' => 'De doelscore is maximaal 5000.',
        ]);

        // Recalculate from scratch: a higher target can reopen a toernooi, a lower one can end it.
        $tournament->update(['target_score' => (int) $data['target_score'], 'target_reached_at' => null]);
        $tournament->refreshTargetReached();

        return back()->with('status', 'Doelscore aangepast naar '.$data['target_score'].'.');
    }

    public function regenerateLink(Tournament $tournament): RedirectResponse
    {
        Gate::authorize('manage', $tournament);

        $tournament->regenerateShareLink();

        return back()->with('status', 'Er is een nieuwe link en een nieuw spel-ID aangemaakt. De oude werken niet meer.');
    }

    public function destroy(Tournament $tournament): RedirectResponse
    {
        Gate::authorize('manage', $tournament);

        $tournament->delete();

        return redirect()->route('tournaments.index')->with('status', 'Toernooi verwijderd.');
    }
}
