<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\ClaimService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Vul je e-mailadres of gebruikersnaam in.',
            'password.required' => 'Vul je wachtwoord in.',
        ]);

        $throttleKey = Str::lower($data['login']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'login' => "Te veel pogingen. Probeer opnieuw over {$seconds} seconden.",
            ]);
        }

        $login = trim($data['login']);
        $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL) !== false;

        $user = $isEmail
            ? User::query()->whereRaw('LOWER(email) = ?', [mb_strtolower($login)])->first()
            : User::findByUsername($login);

        if (! $user || ! Auth::attempt(['id' => $user->id, 'password' => $data['password']], $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'login' => 'Deze gegevens kloppen niet.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        $linked = ClaimService::apply($user, $request);

        return redirect()->intended(route('home'))->with($linked ? ['status' => $linked] : []);
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
            'username' => trim((string) $request->input('username')),
        ]);

        $data = $request->validate([
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[A-Za-z0-9_.-]+$/',
                // usernames are unique regardless of upper/lower case
                function ($attribute, $value, $fail) {
                    if (User::findByUsername($value)) {
                        $fail('Deze gebruikersnaam is al in gebruik.');
                    }
                },
            ],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'regex:/[A-Z]/', 'regex:/[^A-Za-z0-9]/', 'confirmed'],
        ], [
            'username.required' => 'Kies een gebruikersnaam.',
            'username.min' => 'Een gebruikersnaam heeft minstens 3 tekens.',
            'username.max' => 'Een gebruikersnaam heeft maximaal 30 tekens.',
            'username.regex' => 'Gebruik enkel letters, cijfers, punt, streepje en underscore.',
            'email.required' => 'Vul je e-mailadres in.',
            'email.email' => 'Dit is geen geldig e-mailadres.',
            'email.unique' => 'Dit e-mailadres is al in gebruik.',
            'password.required' => 'Kies een wachtwoord.',
            'password.min' => 'Je wachtwoord moet minstens 8 tekens bevatten.',
            'password.regex' => 'Je wachtwoord moet minstens één hoofdletter en één speciaal teken bevatten.',
            'password.confirmed' => 'De wachtwoorden komen niet overeen.',
        ]);

        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        $linked = ClaimService::apply($user, $request);

        return redirect()->route('home')->with($linked ? ['status' => $linked] : []);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
