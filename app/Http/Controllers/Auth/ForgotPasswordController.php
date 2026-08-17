<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Models\User;
use App\Services\Auth\DashboardRedirectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    /**
     * Display the forgot password view.
     */
    public function show(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect(DashboardRedirectService::pathFor(Auth::user()));
        }

        return view('auth.forgot-password');
    }

    /**
     * Handle sending a password reset link to a user.
     */
    public function store(ForgotPasswordRequest $request): RedirectResponse
    {
        if (Auth::check()) {
            return redirect(DashboardRedirectService::pathFor(Auth::user()));
        }

        $status = Password::sendResetLink([
            'email'  => $request->email,
            'status' => User::STATUS_ACTIVE,
        ]);

        if ($status === Password::RESET_THROTTLED) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Please wait before retrying your password reset request.');
        }

        $message = 'If an account exists with that email address, we have emailed your password reset link.';

        // Return same generic success message whether email exists/active or not
        return back()
            ->with('status', $message)
            ->with('success', $message);
    }
}
