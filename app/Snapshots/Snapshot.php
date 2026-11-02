<?php

namespace App\Snapshots;

use App\Models\Game;
use App\Models\Player;
use App\Models\ScoreEvent;
use App\Models\Tournament;
use App\Scoring\Turns;
use Illuminate\Support\Collection;

/**
 * Read-only view of a toernooi or a standalone spel, in ONE shape.
 * Used by the game pages, the live link for guests and the live JSON endpoint.
 *
 * Shape: kind ('tournament'|'game'), game{meta}, players[], leaders[], games[] (rows of the score table),
 *        current_game, current_player, recent[] (latest turns), version.
 */
final class Snapshot
{
    public static function forTournament(Tournament $tournament, int $recent = 15): array
    {
        $tournament->loadMissing('players.user', 'games', 'creator');

        $sums = $tournament->scoreEvents()
            ->selectRaw('score_events.game_id as game_id, score_events.player_id as player_id, SUM(score_events.points) as total')
            ->groupBy('score_events.game_id', 'score_events.player_id')
            ->get()
            ->groupBy('game_id');

        $rows = $tournament->games->map(fn (Game $g) => self::gameRow($g, $tournament->players->mapWithKeys(
            fn (Player $p) => [$p->id => (int) ($sums->get($g->id)?->firstWhere('player_id', $p->id)?->total ?? 0)]
        )->all()))->values()->all();

        $active = $tournament->activeGame()?->setRelation('tournament', $tournament);

        return self::assemble(
            'tournament',
            [
                'id' => $tournament->public_id,
                'name' => $tournament->name,
                'status' => $tournament->status->value,
                'mode' => $tournament->mode->value,
                'target_score' => $tournament->target_score,
                'target_reached' => $tournament->target_reached_at !== null,
                'turn_time_limit' => $tournament->turn_time_limit,
                'creator' => $tournament->creator?->username,
                'public' => $tournament->is_public,
            ],
            $tournament->players,
            $tournament->totals(),
            $active?->currentPlayer(),
            $rows,
            $active?->title(),
            $tournament->scoreEvents()->with('player.user')->orderBy('score_events.id')->get(),
            $tournament->games->mapWithKeys(fn (Game $g) => [$g->id => $g->title()])->all(),
            $tournament->version(),
            $recent,
        );
    }

    public static function forGame(Game $game, int $recent = 15): array
    {
        $game->loadMissing('tournament.players.user', 'ownPlayers.user', 'creator');

        return self::assemble(
            'game',
            [
                'id' => $game->public_id,
                'name' => $game->title(),
                'status' => $game->status->value,
                'mode' => $game->mode->value,
                'target_score' => null,
                'target_reached' => false,
                'turn_time_limit' => $game->turn_time_limit,
                'creator' => $game->creator?->username,
                'public' => $game->is_public,
            ],
            $game->players,
            $game->totals(),
            $game->currentPlayer(),
            [self::gameRow($game, $game->totals())],
            $game->isActive() ? $game->title() : null,
            $game->events()->with('player.user')->get(),
            [$game->id => $game->title()],
            $game->version(),
            $recent,
        );
    }

    // --- internals -------------------------------------------------------------------------

    /** @param  array<int,int>  $totals  player_id => points in this spel */
    private static function gameRow(Game $game, array $totals): array
    {
        return [
            'id' => $game->id,
            'number' => $game->number ?? 1,
            'title' => $game->title(),
            'status' => $game->status->value,
            'ended_by' => $game->ended_by?->value,
            'totals' => $totals,
            'winner_player_id' => $game->winner_player_id,
        ];
    }

    /**
     * @param  array<int,int>  $totals
     * @param  array<int,string>  $titles  game_id => title
     */
    private static function assemble(
        string $kind, array $meta, Collection $players, array $totals, ?Player $current,
        array $games, ?string $currentGame, Collection $events, array $titles, string $version, int $recent,
    ): array {
        $rows = $players->map(fn (Player $p) => [
            'id' => $p->id,
            'username' => $p->name(),
            'is_guest' => $p->isGuest(),
            'total' => $totals[$p->id] ?? 0,
            'warnings_used' => $p->warnings_used,
            'is_current' => $current?->id === $p->id,
            'is_winner' => (bool) $p->is_winner,
        ])->values()->all();

        $max = $rows ? max(array_column($rows, 'total')) : 0;

        // Latest turns: draws, the stone or pass, and every bonus on its own line.
        $turns = array_slice(Turns::forGames($events), -$recent);

        $latest = array_map(function (array $t) use ($titles) {
            $last = end($t['events']);

            return [
                'id' => $t['id'],
                'game' => $titles[$t['game_id']] ?? null,
                'player' => $t['player']->name(),
                'total' => $t['total'],
                'open' => $t['open'],
                'lines' => collect($t['events'])->flatMap(
                    fn (ScoreEvent $e) => $e->is_manual
                        ? [['text' => $e->label().' (handmatig totaal)', 'points' => $e->points]]
                        : collect($e->details['breakdown'] ?? [['label' => $e->label(), 'points' => $e->points]])
                            ->map(fn ($b) => ['text' => $b['label'], 'points' => $b['points']])
                )->values()->all(),
                'at' => $last->created_at?->timezone(config('app.timezone'))->format('H:i'),
            ];
        }, array_reverse($turns));

        return [
            'kind' => $kind,
            'game' => $meta,
            'players' => $rows,
            'leaders' => $events->isNotEmpty()
                ? array_values(array_map(fn ($p) => $p['username'], array_filter($rows, fn ($p) => $p['total'] === $max)))
                : [],
            'games' => $games,
            'current_game' => $currentGame,
            'current_player' => $current?->name(),
            'recent' => $latest,
            'version' => $version,
        ];
    }
}
