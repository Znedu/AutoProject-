<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Services\Auth\DashboardRedirectService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ResetPasswordController extends Controller
{
    /**
     * Display the password reset view for the given token.
     */
    public function show(Request $request, string $token): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect(DashboardRedirectService::pathFor(Auth::user()));
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    /**
     * Reset the given user's password.
     */
    public function store(ResetPasswordRequest $request): RedirectResponse
    {
        if (Auth::check()) {
            return redirect(DashboardRedirectService::pathFor(Auth::user()));
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ]);

                if (is_null($user->email_verified_at)) {
                    $user->email_verified_at = now();
                }

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            $message = 'Your password has been reset successfully! You can now log in.';

            return redirect()
                ->route('login')
                ->with('status', $message)
                ->with('success', $message);
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}
