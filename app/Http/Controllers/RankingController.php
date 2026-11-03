<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Models\Game;
use App\Models\Player;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/** Two rankings: toernooien, and "losse spellen" (standalone). Same filters for both. */
class RankingController extends Controller
{
    public function index(Request $request)
    {
        $me = $request->user();
        $friends = $me->friends();

        $scope = in_array($request->query('scope'), ['global', 'friends', 'selected'], true)
            ? $request->query('scope')
            : 'global';

        // null = everybody; otherwise only these users are listed
        $userIds = null;
        $selected = [];
        $includeMe = true;

        if ($scope === 'friends') {
            $userIds = [...$friends->pluck('id')->map(fn($id) => (string) $id)->all(), (string) $me->id];
        } elseif ($scope === 'selected') {
            $requestFriends = array_map('strval', (array) $request->query('friends', []));
            $friendIds = $friends->pluck('id')->map(fn($id) => (string) $id)->all();

            $selected = array_values(array_intersect($requestFriends, $friendIds));
            $includeMe = $request->boolean('me', true); // the form sends me=0 or me=1
            $userIds = $includeMe ? [...$selected, (string) $me->id] : $selected;
        }

        $tournaments = Tournament::query()
            ->with('players.user')
            ->where('status', Status::Finished->value)
            ->where(fn($q) => $q->where('is_public', true)
                ->orWhereIn('id', Player::query()->select('tournament_id')->where('user_id', $me->id)->whereNotNull('tournament_id')))
            ->when($userIds !== null, fn($q) => $q->whereHas('players', fn($p) => $p->whereIn('user_id', $userIds)))
            ->orderByDesc('finished_at')
            ->paginate(15, pageName: 'toernooien')
            ->withQueryString();

        $games = Game::query()
            ->with('ownPlayers.user')
            ->whereNull('tournament_id')
            ->where('status', Status::Finished->value)
            ->where(fn($q) => $q->where('is_public', true)
                ->orWhereIn('id', Player::query()->select('game_id')->where('user_id', $me->id)->whereNotNull('game_id')))
            ->when($userIds !== null, fn($q) => $q->whereHas('ownPlayers', fn($p) => $p->whereIn('user_id', $userIds)))
            ->orderByDesc('finished_at')
            ->paginate(15, pageName: 'spellen')
            ->withQueryString();

        return view('ranking.index', [
            'tournamentRanking' => $this->leaderboard('tournaments', 'tournament_id', $me, $userIds),
            'gameRanking' => $this->leaderboard('games', 'game_id', $me, $userIds),
            'tournaments' => $tournaments,
            'games' => $games,
            'friends' => $friends,
            'scope' => $scope,
            'selected' => $selected,
            'includeMe' => $includeMe,
        ]);
    }

    /**
     * Wins and points per account. $table is 'tournaments' or 'games' (standalone only), $key the matching players column.
     * Public ones count for everybody, private ones only for the players themselves.
     */
    private function leaderboard(string $table, string $key, User $me, ?array $userIds): Collection
    {
        return Player::query()
            ->join($table, "$table.id", '=', "players.$key")
            ->join('users', 'users.id', '=', 'players.user_id')
            ->where("$table.status", Status::Finished->value)
            ->when($table === 'games', fn($q) => $q->whereNull('games.tournament_id'))
            ->where(fn($q) => $q->where("$table.is_public", true)
                ->orWhereIn("$table.id", Player::query()->select($key)->where('user_id', $me->id)->whereNotNull($key)))
            ->when($userIds !== null, fn($q) => $q->whereIn('players.user_id', $userIds))
            ->groupBy('players.user_id', 'users.username')
            ->selectRaw('players.user_id, users.username, COUNT(*) as games_played, '
                . 'SUM(CASE WHEN players.is_winner THEN 1 ELSE 0 END) as wins, '
                . 'SUM(players.final_points) as total_points, AVG(players.final_points) as avg_points')
            ->orderByDesc('wins')
            ->orderByDesc('total_points')
            ->orderBy('users.username')
            ->get()
            ->map(function ($row) {
                $row->win_rate = $row->games_played ? round(($row->wins / $row->games_played) * 100) : 0;

                return $row;
            });
    }
}