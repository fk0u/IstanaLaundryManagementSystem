<?php

namespace Tests\Feature;

use Tests\TestCase;

class DebugEnvTest extends TestCase
{
    public function test_dump_environment(): void
    {
        fwrite(STDERR, "\nappEnvironment=" . app()->environment() . "\n");
        fwrite(STDERR, "envHelper=" . env('APP_ENV') . "\n");
        fwrite(STDERR, "configAppEnv=" . config('app.env') . "\n");
        fwrite(STDERR, "getenv=" . var_export(getenv('APP_ENV'), true) . "\n");
        fwrite(STDERR, "SERVER=" . var_export($_SERVER['APP_ENV'] ?? null, true) . "\n");
        fwrite(STDERR, "ENV=" . var_export($_ENV['APP_ENV'] ?? null, true) . "\n");
        $this->assertTrue(true);
    }
}
