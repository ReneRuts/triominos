<?php

namespace App\Http\Controllers;

use App\Support\ShareLinkFinder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Lets anybody (no account needed) open the live view with a link or the short game ID. */
class JoinController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:300'],
        ], [
            'code.required' => 'Vul de link of het spel-ID in.',
        ]);

        $model = ShareLinkFinder::byInput($data['code']);

        if (! $model) {
            return back()->withInput()->withErrors([
                'code' => 'Niets gevonden. Plak de volledige link of typ het spel-ID (bijvoorbeeld ABCD-2345).',
            ]);
        }

        return redirect()->route('live.show', $model->share_token);
    }
}
