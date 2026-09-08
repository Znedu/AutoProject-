<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TxtFlowTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $configuredToken = config('services.txtflow.token');

        // If no token is configured in .env, permit open communication with mobile app
        if (empty($configuredToken)) {
            return $next($request);
        }

        // Check path parameter (/api/txtflow/{token}/...), query parameter (?token=...),
        // Bearer token, or X-TxtFlow-Token header
        $providedToken = $request->route('token')
            ?? $request->query('token')
            ?? $request->bearerToken()
            ?? $request->header('X-TxtFlow-Token');

        if (empty($providedToken) || ! is_string($providedToken) || ! hash_equals($configuredToken, $providedToken)) {
            return response()->json([
                'error' => 'Unauthorized: Invalid or missing token. If you set TXTFLOW_TOKEN in .env, provide it in the URL path: /api/txtflow/{your_token}/messages or leave TXTFLOW_TOKEN empty in .env to disable token verification.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
