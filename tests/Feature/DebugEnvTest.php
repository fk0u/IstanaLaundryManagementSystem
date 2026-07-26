<?php

namespace Tests\Feature;

use Tests\TestCase;

class DebugEnvTest extends TestCase
{
    public function test_dump_environment(): void
    {
        fwrite(STDERR, "\nAPP_ENV=" . app()->environment() . "\n");
        fwrite(STDERR, "runningUnitTests=" . var_export(app()->runningUnitTests(), true) . "\n");
        fwrite(STDERR, "SESSION_DRIVER=" . config('session.driver') . "\n");
        fwrite(STDERR, "DB_CONNECTION=" . config('database.default') . "\n");
        fwrite(STDERR, "configCached=" . var_export(app()->configurationIsCached(), true) . "\n");
        $this->assertTrue(true);
    }
}
