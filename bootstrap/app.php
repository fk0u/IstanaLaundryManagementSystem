<?php

use App\Http\Middleware\BranchScopeMiddleware;
use App\Http\Middleware\GzipCompressionMiddleware;
use App\Http\Middleware\PerformanceMiddleware;
use App\Http\Middleware\RedirectBasedOnRole;
use App\Http\Middleware\SecurityAnomalyDetectorMiddleware;
use App\Http\Middleware\SecurityHeadersMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global Middleware Pipeline for HTTP Security & Performance
        $middleware->append([
            SecurityHeadersMiddleware::class,
            SecurityAnomalyDetectorMiddleware::class,
            PerformanceMiddleware::class,
            GzipCompressionMiddleware::class,
        ]);

        $middleware->alias([
            'branch.scope' => BranchScopeMiddleware::class,
            'role.redirect' => RedirectBasedOnRole::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'security.headers' => SecurityHeadersMiddleware::class,
            'security.anomaly' => SecurityAnomalyDetectorMiddleware::class,
            'performance' => PerformanceMiddleware::class,
            'gzip' => GzipCompressionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
