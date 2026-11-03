<?php

namespace App\Http\Controllers;

use App\Actions\Games\BeginStandaloneGame;
use App\Actions\Games\ChangeVisibility;
use App\Actions\Games\CreateStandaloneGame;
use App\Actions\Games\FinishGame;
use App\Actions\Games\ReopenGame;
use App\Enums\GameEnding;
use App\Enums\Status;
use App\Http\Requests\FinishGameRequest;
use App\Http\Requests\StartGameRequest;
use App\Http\Requests\StoreGameRequest;
use App\Models\Game;
use App\Scoring\Turns;
use App\Snapshots\Snapshot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/** A spel: the page where scores are entered (inside a toernooi or standalone), plus standalone lifecycle. */
class GameController extends Controller
{
    /** My standalone games. Spellen of a toernooi are listed on the toernooi page. */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $games = Game::query()
            ->whereNull('tournament_id')
            ->with('ownPlayers.user', 'creator')
            ->where(fn($q) => $q->where('creator_id', $userId)
                ->orWhereHas('ownPlayers', fn($p) => $p->where('user_id', $userId)))
            ->latest('id')
            ->get();

        return view('games.index', [
            'active' => $games->where('status', Status::Active),
            'finished' => $games->where('status', Status::Finished),
        ]);
    }

    public function create(Request $request)
    {
        return view('games.create', ['friends' => $request->user()->friends()]);
    }

    public function store(StoreGameRequest $request, CreateStandaloneGame $create): RedirectResponse
    {
        $game = $create->handle(
            $request->user(),
            $request->validated('name'),
            $request->ruleAttributes(),
            $request->friendIds(),
            $request->guestNames(),
        );

        return redirect()->route('games.show', $game);
    }

    public function show(Request $request, Game $game)
    {
        Gate::authorize('view', $game);
        $game->loadMissing('tournament.players.user', 'ownPlayers.user', 'creator');

        $events = $game->events()->with('player.user', 'author')->get();
        $tournament = $game->tournament;

        return view('games.show', [
            'game' => $game,
            'tournament' => $tournament,
            'snapshot' => $tournament ? Snapshot::forTournament($tournament) : Snapshot::forGame($game),
            'events' => $events,
            // Newest turn first: draws and the stone or pass that ended the turn belong together.
            'turns' => array_reverse(Turns::group($events)),
            'drawsInTurn' => $game->drawsInOpenTurn(),
            'canScore' => Gate::allows('play', $game),
            'isCreator' => $game->isCreator($request->user()),
            'needsSetup' => $game->isActive() && ! $game->isStarted(),
        ]);
    }

    public function version(Game $game): JsonResponse
    {
        Gate::authorize('view', $game);

        return response()->json(['version' => $game->version()]);
    }

    /** Standalone spel only: seating order and who starts. */
    public function start(StartGameRequest $request, Game $game, BeginStandaloneGame $begin): RedirectResponse
    {
        Gate::authorize('play', $game);

        $begin->handle($game, (int) $request->validated('starting_player_id'), $request->validated('order'));

        return back();
    }

    /** The spel is over: somebody emptied their stones, or it is blocked. */
    public function finish(FinishGameRequest $request, Game $game, FinishGame $finish): RedirectResponse
    {
        Gate::authorize('play', $game);

        $finish->handle(
            $game,
            GameEnding::from($request->validated('ended_by')),
            (int) $request->validated('winner_player_id'),
            $request->validated('remaining'),
            $request->user(),
        );

        return back();
    }

    public function reopen(Game $game, ReopenGame $reopen): RedirectResponse
    {
        Gate::authorize('manage', $game);

        $reopen->handle($game);

        return back();
    }

    /** Standalone spel: private or public in the ranking. Spellen of a toernooi follow the toernooi. */
    public function updateVisibility(Request $request, Game $game, ChangeVisibility $change): RedirectResponse
    {
        Gate::authorize('manage', $game);
        abort_unless($game->isStandalone(), 404);

        $isPublic = $request->boolean('is_public');
        $change->handle($game, $isPublic);

        return back()->with('status', $isPublic ? 'Het spel is nu openbaar.' : 'Het spel is nu privé.');
    }

    public function regenerateLink(Game $game): RedirectResponse
    {
        Gate::authorize('delete', $game); // standalone + creator

        $game->regenerateShareLink();

        return back()->with('status', 'Er is een nieuwe link en een nieuw spel-ID aangemaakt. De oude werken niet meer.');
    }

    public function destroy(Game $game): RedirectResponse
    {
        Gate::authorize('delete', $game);

        $game->delete();

        return redirect()->route('games.index')->with('status', 'Spel verwijderd.');
    }
}