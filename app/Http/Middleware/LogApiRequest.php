<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class LogApiRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = hrtime(true);
        $traceId = (string) Str::uuid();
        $request->attributes->set('trace_id', $traceId);

        try {
            $response = $next($request);
        } catch (Throwable $exception) {
            $status = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;
            $this->logRequest($request, $traceId, $status, $startedAt);
            throw $exception;
        }

        $response->headers->set('X-Trace-Id', $traceId);
        $this->logRequest($request, $traceId, $response->getStatusCode(), $startedAt);

        return $response;
    }

    private function logRequest(Request $request, string $traceId, int $status, int $startedAt): void
    {
        Log::info('http_request', [
            'trace_id' => $traceId,
            'method' => $request->method(),
            'path' => $request->path(),
            'status' => $status,
            'duration_ms' => round((hrtime(true) - $startedAt) / 1_000_000, 2),
            'ip' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 120),
        ]);
    }
}