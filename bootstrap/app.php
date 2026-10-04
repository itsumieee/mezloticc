<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append([
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\LogApiRequest::class,
        ]);
        $middleware->alias([
            'throttle.roblox' => \App\Http\Middleware\ThrottleRobloxApi::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (Response $response): Response {
            \App\Http\Middleware\SecurityHeaders::apply($response);

            $traceId = request()->attributes->get('trace_id');
            if (is_string($traceId)) {
                $response->headers->set('X-Trace-Id', $traceId);
            }

            return $response;
        });
    })->create();
