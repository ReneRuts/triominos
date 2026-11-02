<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Tournament;
use App\Snapshots\Snapshot;
use App\Support\ShareLinkFinder;
use Illuminate\Http\JsonResponse;

/** Read-only live view for guests, reachable with the secret link of a toernooi or standalone spel. No login needed. */
class LiveController extends Controller
{
    public function show(string $token)
    {
        return view('live.show', [
            'snapshot' => $this->snapshot($token),
            'token' => $token,
        ]);
    }

    public function data(string $token): JsonResponse
    {
        return response()->json($this->snapshot($token))->header('Cache-Control', 'no-store');
    }

    private function snapshot(string $token): array
    {
        $model = ShareLinkFinder::byToken($token);
        abort_unless($model, 404);

        return $model instanceof Tournament ? Snapshot::forTournament($model) : Snapshot::forGame($model);
    }
}
