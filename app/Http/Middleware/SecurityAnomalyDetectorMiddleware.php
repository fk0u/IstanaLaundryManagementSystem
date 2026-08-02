<?php

namespace App\Http\Middleware;

use App\Services\AuditLogService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SecurityAnomalyDetectorMiddleware
{
    /**
     * Common attack pattern signatures for SQLi, XSS, and Path Traversal.
     */
    protected array $maliciousPatterns = [
        // SQL Injection patterns
        '/(\b(SELECT|INSERT|UPDATE|DELETE|DROP|ALTER|TRUNCATE|UNION|EXEC|DECLARE)\b.*(FROM|INTO|TABLE|DATABASE|WHERE))/i',
        '/(\bOR\b\s+[\'"]?1[\'"]?\s*=\s*[\'"]?1)/i',
        '/(\bAND\b\s+[\'"]?1[\'"]?\s*=\s*[\'"]?1)/i',
        '/(\bSLEEP\s*\(\s*\d+\s*\))/i',
        '/(\bBENCHMARK\s*\()/i',

        // Cross-Site Scripting (XSS) patterns
        '/<script\b[^>]*>(.*?)<\/script>/i',
        '/javascript\s*:/i',
        '/onerror\s*=/i',
        '/onload\s*=/i',
        '/eval\s*\(/i',

        // Path Traversal patterns
        '/\.\.[\/\\\\]/',
    ];

    /**
     * Handle an incoming request and detect security anomalies.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $inputString = json_encode($request->all()) . ' ' . urldecode($request->getRequestUri());

        foreach ($this->maliciousPatterns as $pattern) {
            if (preg_match($pattern, $inputString)) {
                $clientIp = $request->ip();
                $uri = $request->fullUrl();

                Log::warning('Security Anomaly Blocked', [
                    'ip' => $clientIp,
                    'uri' => $uri,
                    'user_agent' => $request->userAgent(),
                    'pattern' => $pattern,
                ]);

                try {
                    $auditService = app(AuditLogService::class);
                    $auditService->log(
                        'SECURITY_ANOMALY_BLOCKED',
                        "Upaya akses mencurigakan terdeteksi dan diblokir dari IP {$clientIp} pada URL {$uri}",
                        [
                            'pattern' => $pattern,
                            'ip' => $clientIp,
                            'user_agent' => $request->userAgent(),
                        ]
                    );
                } catch (\Throwable $e) {
                    // Fail gracefully
                }

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Akses ditolak karena terdeteksi potensi ancaman keamanan.',
                        'error_code' => 'SECURITY_POLICY_VIOLATION',
                    ], 403);
                }

                abort(403, 'Akses ditolak karena terdeteksi ancaman keamanan.');
            }
        }

        return $next($request);
    }
}
