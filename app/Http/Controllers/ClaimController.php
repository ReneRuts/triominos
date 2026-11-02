<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Models\Player;
use App\Models\Tournament;
use App\Support\ClaimService;
use App\Support\ShareLinkFinder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Step one of linking a guest seat to an account: remember which seat, then register or log in. */
class ClaimController extends Controller
{
    public function start(Request $request, string $token, int $player): RedirectResponse
    {
        $model = ShareLinkFinder::byToken($token);
        abort_unless($model && $model->status === Status::Finished, 404);

        $isTournament = $model instanceof Tournament;
        $column = $isTournament ? 'tournament_id' : 'game_id';

        $seat = Player::query()->where($column, $model->id)->where('id', $player)->whereNull('user_id')->firstOrFail();

        $request->session()->put('claim', [
            'type' => $isTournament ? 'tournament' : 'game',
            'id' => $model->id,
            'player' => $seat->id,
            'name' => $seat->guest_name,
        ]);

        if ($user = $request->user()) {
            $message = ClaimService::apply($user, $request);

            return redirect()->route('home')->with('status', $message ?? 'Dit kon niet gekoppeld worden aan je account.');
        }

        return redirect()->route('register');
    }
}
