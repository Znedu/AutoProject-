<?php

namespace App\Http\Middleware;

use App\Services\Auth\DashboardRedirectService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->guest(route('login'));
        }

        if (! $user->hasRole($roles)) {
            return redirect(DashboardRedirectService::pathFor($user))
                ->with('error', 'You do not have permission to access that area.');
        }

        return $next($request);
    }
}
