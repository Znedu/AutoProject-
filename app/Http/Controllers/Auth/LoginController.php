<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\DashboardRedirectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect(DashboardRedirectService::pathFor(Auth::user()));
        }

        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials)) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        $user = Auth::user();

        if (! $user instanceof User || ! $user->isActive()) {
            Auth::logout();

            return back()->withErrors([
                'email' => 'Your account has been deactivated. Please contact support.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()
            ->intended(DashboardRedirectService::pathFor($user))
            ->with('success', 'Welcome back, '.$user->name.'!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Logged out successfully.');
    }
}
