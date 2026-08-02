<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GzipCompressionMiddleware
{
    /**
     * Handle an incoming request and compress the response using gzip if supported.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Check if server supports gzencode and client accepts gzip encoding
        if (function_exists('gzencode') && str_contains($request->header('Accept-Encoding', ''), 'gzip')) {
            $content = $response->getContent();

            // Only compress if payload is non-empty and not already compressed
            if (is_string($content) && strlen($content) > 1024 && !$response->headers->has('Content-Encoding')) {
                $compressed = gzencode($content, 5);

                if ($compressed !== false) {
                    $response->setContent($compressed);
                    $response->headers->set('Content-Encoding', 'gzip');
                    $response->headers->set('Vary', 'Accept-Encoding');
                    $response->headers->set('Content-Length', (string) strlen($compressed));
                }
            }
        }

        return $response;
    }
}
