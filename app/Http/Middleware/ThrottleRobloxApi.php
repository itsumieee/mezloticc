<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleRobloxApi
{
    public function handle(Request $request, Closure $next): Response
    {
        $ipKey = 'roblox-search:ip:'.$request->ip();
        $globalKey = 'roblox-search:global';
        $decaySeconds = 60;

        if (RateLimiter::tooManyAttempts($ipKey, 30)) {
            $seconds = RateLimiter::availableIn($ipKey);

            $response = $request->expectsJson()
                ? response()->json(['message' => 'Too many requests.', 'retry_after' => $seconds], 429)
                : Inertia::render('Errors/ErrorPage', [
                    'status' => 429,
                    'seconds' => $seconds,
                ])->toResponse($request)->setStatusCode(429);

            return $response->header('Retry-After', (string) $seconds);
        }

        if (RateLimiter::tooManyAttempts($globalKey, 500)) {
            $seconds = RateLimiter::availableIn($globalKey);

            return response('Service is busy. Please try again shortly.', 503)
                ->header('Retry-After', (string) $seconds);
        }

        RateLimiter::hit($ipKey, $decaySeconds);
        RateLimiter::hit($globalKey, $decaySeconds);

        return $next($request);
    }
}
