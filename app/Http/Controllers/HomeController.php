<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Models\Game;
use App\Models\Tournament;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $mine = fn ($relation) => fn ($q) => $q->where('creator_id', $user->id)
            ->orWhereHas($relation, fn ($p) => $p->where('user_id', $user->id));

        return view('home', [
            'activeTournaments' => $user
                ? Tournament::query()->with('players.user')->where('status', Status::Active->value)
                    ->where($mine('players'))->latest('id')->limit(3)->get()
                : collect(),
            'activeGames' => $user
                ? Game::query()->with('ownPlayers.user')->whereNull('tournament_id')->where('status', Status::Active->value)
                    ->where($mine('ownPlayers'))->latest('id')->limit(3)->get()
                : collect(),
        ]);
    }
}
