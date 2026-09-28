<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleRobloxApi
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'roblox-search:'.$request->ip();
        $maxAttempts = 20;
        $decaySeconds = 60;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            return response()
                ->view('errors.429', ['seconds' => $seconds], 429)
                ->header('Retry-After', (string) $seconds);
        }

        RateLimiter::hit($key, $decaySeconds);

        return $next($request);
    }
}
