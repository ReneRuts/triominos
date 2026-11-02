<?php

namespace App\Support;

use App\Enums\Status;
use App\Models\Game;
use App\Models\Player;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Http\Request;

/** A guest who registers afterwards can take over the guest seat of a finished toernooi or standalone spel. */
final class ClaimService
{
    /** @return string|null a status message when a seat was linked */
    public static function apply(User $user, Request $request): ?string
    {
        $claim = $request->session()->pull('claim');
        if (! $claim) {
            return null;
        }

        $isTournament = $claim['type'] === 'tournament';
        $column = $isTournament ? 'tournament_id' : 'game_id';
        $model = $isTournament ? Tournament::query()->find($claim['id']) : Game::query()->find($claim['id']);

        $seat = Player::query()->where('id', $claim['player'])->where($column, $claim['id'])->whereNull('user_id')->first();

        if (! $seat || ! $model || $model->status !== Status::Finished) {
            return null;
        }

        // The account already has a seat here: nothing to link.
        if (Player::query()->where($column, $model->id)->where('user_id', $user->id)->exists()) {
            return null;
        }

        $seat->update(['user_id' => $user->id]);

        $title = $isTournament ? $model->name : $model->title();

        return ($isTournament ? 'Het toernooi' : 'Het spel').' "'.$title.'" is gekoppeld aan je account.';
    }
}
