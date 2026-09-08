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

        if (empty($configuredToken)) {
            return response()->json(['error' => 'TxtFlow is not configured on this server.'], Response::HTTP_UNAUTHORIZED);
        }

        // Check ?token= query parameter, or Authorization Bearer header, or X-TxtFlow-Token header
        $providedToken = $request->query('token')
            ?? $request->bearerToken()
            ?? $request->header('X-TxtFlow-Token');

        if (! is_string($providedToken) || ! hash_equals($configuredToken, $providedToken)) {
            return response()->json(['error' => 'Unauthorized: Invalid or missing token.'], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
