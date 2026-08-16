<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResendVerificationRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Models\User;
use App\Services\Auth\DashboardRedirectService;
use App\Services\Auth\EmailVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    public function __construct(
        protected EmailVerificationService $verificationService
    ) {}

    /**
     * Display the email verification prompt.
     */
    public function show(Request $request): View|RedirectResponse
    {
        $user = $this->resolvePendingUser($request);

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->hasVerifiedEmail()) {
            if (Auth::guest()) {
                Auth::login($user);
            }
            return redirect(DashboardRedirectService::pathFor($user));
        }

        $cooldown = $this->verificationService->getCooldownSecondsRemaining($user);

        return view('auth.verify-email', [
            'email'    => $user->email,
            'cooldown' => $cooldown,
        ]);
    }

    /**
     * Verify the 6-digit OTP code entered by the user.
     */
    public function verify(VerifyEmailRequest $request): RedirectResponse
    {
        $user = $this->resolvePendingUser($request);

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->hasVerifiedEmail()) {
            if (Auth::guest()) {
                Auth::login($user);
            }
            return redirect(DashboardRedirectService::pathFor($user));
        }

        $result = $this->verificationService->verify($user, $request->code);

        if (! $result['success']) {
            return back()
                ->withInput()
                ->withErrors(['code' => $result['message']]);
        }

        // Mark verified & log user in
        Auth::login($user);
        session()->forget('verification_email');

        return redirect(DashboardRedirectService::pathFor($user))
            ->with('success', 'Your email address has been verified! Welcome to AutoProject+.');
    }

    /**
     * Resend a new verification OTP code.
     */
    public function resend(ResendVerificationRequest $request): RedirectResponse
    {
        $user = $this->resolvePendingUser($request);

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->hasVerifiedEmail()) {
            if (Auth::guest()) {
                Auth::login($user);
            }
            return redirect(DashboardRedirectService::pathFor($user));
        }

        if (! $this->verificationService->canResend($user)) {
            $remaining = $this->verificationService->getCooldownSecondsRemaining($user);

            return back()->with('error', "Please wait {$remaining} seconds before requesting a new code.");
        }

        $this->verificationService->sendCode($user);

        return back()->with('success', 'A new 6-digit verification code has been sent to your email address.');
    }

    /**
     * Cancel the verification process and return to the home page.
     */
    public function cancel(Request $request): RedirectResponse
    {
        session()->forget('verification_email');

        if (Auth::check() && ! Auth::user()->hasVerifiedEmail()) {
            Auth::logout();
        }

        return redirect('/');
    }

    /**
     * Resolve the pending user from Auth session or pending verification_email session key.
     */
    protected function resolvePendingUser(Request $request): ?User
    {
        if ($request->user()) {
            return $request->user();
        }

        $email = session('verification_email');

        if ($email) {
            return User::where('email', $email)->first();
        }

        return null;
    }
}
