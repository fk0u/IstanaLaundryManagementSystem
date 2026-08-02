<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PerformanceMiddleware
{
    /**
     * Handle an incoming request and measure server execution performance.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        $response = $next($request);

        $executionTimeMs = round((microtime(true) - $startTime) * 1000, 2);
        $memoryUsageMb = round((memory_get_peak_usage() - $startMemory) / 1024 / 1024, 2);

        // Inject latency and memory performance headers
        $response->headers->set('X-Response-Time', $executionTimeMs . 'ms');
        $response->headers->set('X-Peak-Memory', $memoryUsageMb . 'MB');

        // Log slow queries/requests taking > 500ms
        if ($executionTimeMs > 500) {
            Log::warning('Slow Request Detected', [
                'uri' => $request->fullUrl(),
                'method' => $request->method(),
                'duration_ms' => $executionTimeMs,
                'memory_mb' => $memoryUsageMb,
                'user_id' => auth()->id(),
            ]);
        }

        return $response;
    }
}
