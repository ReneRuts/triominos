<?php

namespace App\Http\Controllers;

use App\Actions\Scoring\ScoreRecorder;
use App\Http\Requests\DestroyScoreEventRequest;
use App\Http\Requests\StoreScoreEventRequest;
use App\Models\Game;
use App\Models\ScoreEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ScoreEventController extends Controller
{
    public function store(StoreScoreEventRequest $request, Game $game, ScoreRecorder $scores): RedirectResponse
    {
        Gate::authorize('play', $game);

        $player = $request->player();
        $by = $request->user();

        match ($request->validated('type')) {
            'tile' => $scores->tile(
                $game,
                $player,
                $request->digits(),
                ['bridge' => $request->boolean('bridge'), 'hexagons' => $request->validated('hexagons')],
                $request->filled('manual_points') ? (int) $request->validated('manual_points') : null,
                $by,
            ),
            'draw' => $scores->draw($game, $player, $by),
            'pass' => $scores->pass($game, $player, $by),
        };

        return back();
    }

    public function timeout(Request $request, Game $game, ScoreRecorder $scores): RedirectResponse
    {
        Gate::authorize('play', $game);

        $data = $request->validate([
            'player_id' => ['required', Rule::in($game->players->pluck('id')->all())],
        ]);

        $scores->timeout($game, $game->players->firstWhere('id', $data['player_id']), $request->user());

        return back();
    }

    public function destroy(DestroyScoreEventRequest $request, Game $game, ScoreEvent $event, ScoreRecorder $scores): RedirectResponse
    {
        Gate::authorize('edit', $game);

        $scores->delete($game, $event);

        return back();
    }

    public function toggleLock(Game $game, ScoreEvent $event, ScoreRecorder $scores): RedirectResponse
    {
        Gate::authorize('manage', $game);
        abort_unless($game->isActive(), 404);

        $scores->toggleLock($event);

        return back();
    }
}
