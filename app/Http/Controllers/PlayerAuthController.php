<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlayerAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('player.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($user->role !== 'player') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Access restricted to Player nodes only.',
                ]);
            }

            $request->session()->regenerate();

            return redirect()->intended(route('player.display'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }
}
