<?php

namespace App\Http\Controllers;

use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FriendController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return view('friends.index', [
            'friends' => $user->friends(),
            'incoming' => $user->incomingRequests(),
            'outgoing' => $user->outgoingRequests(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:30'],
        ], ['username.required' => 'Vul een gebruikersnaam in.']);

        $me = $request->user();
        $target = User::findByUsername(trim($data['username']));

        if (! $target) {
            return back()->withInput()->withErrors(['username' => 'Er bestaat geen gebruiker met deze gebruikersnaam.']);
        }

        if ($target->id === $me->id) {
            return back()->withInput()->withErrors(['username' => 'Je kan jezelf niet als vriend toevoegen.']);
        }

        $existing = Friendship::between($me->id, $target->id);

        if ($existing) {
            if ($existing->status === 'accepted') {
                return back()->withErrors(['username' => "Je bent al bevriend met {$target->username}."]);
            }

            // They already asked me: sending a request back simply accepts theirs.
            if ($existing->addressee_id === $me->id) {
                $existing->update(['status' => 'accepted']);

                return back()->with('status', "Jij en {$target->username} zijn nu vrienden.");
            }

            return back()->withErrors(['username' => "Je hebt {$target->username} al een verzoek gestuurd."]);
        }

        Friendship::create(['requester_id' => $me->id, 'addressee_id' => $target->id, 'status' => 'pending']);

        return back()->with('status', "Verzoek verstuurd naar {$target->username}.");
    }

    public function accept(Request $request, Friendship $friendship): RedirectResponse
    {
        abort_unless($friendship->addressee_id === $request->user()->id && $friendship->status === 'pending', 403);

        $friendship->update(['status' => 'accepted']);

        return back()->with('status', "Jij en {$friendship->requester->username} zijn nu vrienden.");
    }

    /** Decline an incoming request, cancel an outgoing one, or remove a friend. */
    public function destroy(Request $request, Friendship $friendship): RedirectResponse
    {
        abort_unless($friendship->involves($request->user()), 403);

        $friendship->delete();

        return back()->with('status', 'Gedaan.');
    }
}
