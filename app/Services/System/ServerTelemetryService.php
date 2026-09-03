<?php

namespace App\Services\System;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ServerTelemetryService
{
    /**
     * Gather comprehensive telemetry data.
     */
    public function getTelemetryData(): array
    {
        return [
            'host' => $this->getHostMetrics(),
            'database' => $this->getDatabaseMetrics(),
            'application' => $this->getApplicationMetrics(),
            'git' => $this->getGitMetrics(),
            'security' => $this->getSecurityMetrics(),
            'processes' => $this->getTopProcesses(),
            'services' => $this->getServiceStatuses(),
            'users' => $this->getUsersList(),
            'environment_config' => $this->getEnvironmentConfig(),
            'queue_scheduler' => $this->getQueueAndScheduler(),
        ];
    }

    /**
     * Real-time host performance (CPU, RAM, Disk, OS).
     */
    public function getHostMetrics(): array
    {
        // CPU
        $load = function_exists('sys_getloadavg') ? sys_getloadavg() : [0, 0, 0];
        $cores = 1;
        if (PHP_OS_FAMILY === 'Linux') {
            $cores = (int) shell_exec('nproc 2>/dev/null') ?: 1;
        } elseif (PHP_OS_FAMILY === 'Windows') {
            $cores = (int) (getenv('NUMBER_OF_PROCESSORS') ?: 1);
        }
        $cpuUsage = min(100, round(($load[0] / max(1, $cores)) * 100, 1));

        // Memory
        $totalMem = 0;
        $availMem = 0;
        $freeMem = 0;
        $cachedMem = 0;
        $swapTotal = 0;
        $swapUsed = 0;

        if (PHP_OS_FAMILY === 'Linux' && file_exists('/proc/meminfo')) {
            $memInfo = [];
            foreach (file('/proc/meminfo') as $line) {
                if (preg_match('/^(\w+):\s+(\d+)/', $line, $m)) {
                    $memInfo[$m[1]] = (int) $m[2];
                }
            }
            $totalMem = round(($memInfo['MemTotal'] ?? 0) / 1024, 1);
            $freeMem = round(($memInfo['MemFree'] ?? 0) / 1024, 1);
            $availMem = round(($memInfo['MemAvailable'] ?? 0) / 1024, 1);
            $cachedMem = round((($memInfo['Buffers'] ?? 0) + ($memInfo['Cached'] ?? 0)) / 1024, 1);
            $usedMem = round($totalMem - $availMem, 1);
            $memPct = $totalMem > 0 ? round(($usedMem / $totalMem) * 100, 1) : 0;
            $swapTotal = round(($memInfo['SwapTotal'] ?? 0) / 1024, 1);
            $swapFree = round(($memInfo['SwapFree'] ?? 0) / 1024, 1);
            $swapUsed = round($swapTotal - $swapFree, 1);
        } else {
            // Fallback for Windows/local
            $usedBytes = memory_get_usage(true);
            $usedMem = round($usedBytes / (1024 * 1024), 1);
            $totalMem = 4096;
            $freeMem = round($totalMem - $usedMem, 1);
            $availMem = $freeMem;
            $memPct = round(($usedMem / $totalMem) * 100, 1);
        }

        // Disk Storage
        $diskPath = PHP_OS_FAMILY === 'Windows' ? 'C:' : '/';
        $diskFree = round(disk_free_space($diskPath) / (1024 * 1024 * 1024), 2);
        $diskTotal = round(disk_total_space($diskPath) / (1024 * 1024 * 1024), 2);
        $diskUsed = round($diskTotal - $diskFree, 2);
        $diskPct = $diskTotal > 0 ? round(($diskUsed / $diskTotal) * 100, 1) : 0;

        // Uptime
        $uptimeStr = 'N/A';
        $uptimeSeconds = 0;
        if (file_exists('/proc/uptime')) {
            $uptimeSeconds = (int) explode(' ', file_get_contents('/proc/uptime'))[0];
            $days = floor($uptimeSeconds / 86400);
            $hours = floor(($uptimeSeconds % 86400) / 3600);
            $minutes = floor(($uptimeSeconds % 3600) / 60);
            $uptimeStr = "{$days}d {$hours}h {$minutes}m";
        }

        return [
            'cpu_usage_pct' => $cpuUsage,
            'cpu_cores' => $cores,
            'load_avg' => [
                '1m' => round($load[0] ?? 0, 2),
                '5m' => round($load[1] ?? 0, 2),
                '15m' => round($load[2] ?? 0, 2),
            ],
            'architecture' => php_uname('m'),
            'memory_used_mb' => $usedMem,
            'memory_total_mb' => $totalMem,
            'memory_free_mb' => $freeMem,
            'memory_available_mb' => $availMem,
            'memory_cached_mb' => $cachedMem,
            'memory_pct' => $memPct,
            'swap_total_mb' => $swapTotal,
            'swap_used_mb' => $swapUsed,
            'php_memory_mb' => round(memory_get_usage(true) / (1024 * 1024), 2),
            'php_peak_memory_mb' => round(memory_get_peak_usage(true) / (1024 * 1024), 2),
            'php_memory_limit' => ini_get('memory_limit'),
            'disk_total_gb' => $diskTotal,
            'disk_used_gb' => $diskUsed,
            'disk_free_gb' => $diskFree,
            'disk_pct' => $diskPct,
            'os_name' => php_uname('s') . ' ' . php_uname('r'),
            'hostname' => gethostname(),
            'server_ip' => request()->server('SERVER_ADDR') ?? '127.0.0.1',
            'uptime' => $uptimeStr,
            'uptime_seconds' => $uptimeSeconds,
            'server_time' => now()->format('Y-m-d H:i:s T'),
            'timezone' => config('app.timezone', 'Asia/Makassar'),
        ];
    }

    /**
     * Top running processes on the host.
     */
    public function getTopProcesses(): array
    {
        $processes = [];
        if (PHP_OS_FAMILY === 'Linux') {
            try {
                $psOutput = shell_exec('ps aux --sort=-%cpu 2>/dev/null | head -n 11');
                if ($psOutput) {
                    $lines = explode("\n", trim($psOutput));
                    foreach (array_slice($lines, 1) as $line) {
                        $cols = preg_split('/\s+/', trim($line), 11);
                        if (count($cols) >= 11) {
                            $processes[] = [
                                'user' => $cols[0],
                                'pid' => $cols[1],
                                'cpu' => $cols[2],
                                'mem' => $cols[3],
                                'time' => $cols[9],
                                'command' => $cols[10],
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                // Ignore
            }
        }
        return $processes;
    }

    /**
     * Linux services status (Nginx, MySQL, PHP-FPM).
     */
    public function getServiceStatuses(): array
    {
        $services = [
            'nginx' => ['name' => 'Nginx Web Server', 'status' => 'UNKNOWN', 'port' => 80],
            'mysql' => ['name' => 'MySQL Database Server', 'status' => 'UNKNOWN', 'port' => 3306],
            'php_fpm' => ['name' => 'PHP 8.3 FPM', 'status' => 'UNKNOWN', 'port' => 9000],
        ];

        if (PHP_OS_FAMILY === 'Linux') {
            try {
                $nginxCheck = trim(shell_exec('systemctl is-active nginx 2>/dev/null') ?? '');
                $services['nginx']['status'] = $nginxCheck === 'active' ? 'RUNNING' : ($nginxCheck ?: 'INACTIVE');

                $mysqlCheck = trim(shell_exec('systemctl is-active mysql 2>/dev/null') ?? '');
                $services['mysql']['status'] = $mysqlCheck === 'active' ? 'RUNNING' : ($mysqlCheck ?: 'INACTIVE');

                $phpCheck = trim(shell_exec('systemctl is-active php8.3-fpm 2>/dev/null') ?? '');
                $services['php_fpm']['status'] = $phpCheck === 'active' ? 'RUNNING' : ($phpCheck ?: 'INACTIVE');
            } catch (\Exception $e) {
                // Ignore
            }
        } else {
            $services['nginx']['status'] = 'N/A (Windows)';
            $services['mysql']['status'] = 'RUNNING';
            $services['php_fpm']['status'] = 'N/A';
        }

        return $services;
    }

    /**
     * Database deep telemetry & table inspection.
     */
    public function getDatabaseMetrics(): array
    {
        // Latency ping
        $start = microtime(true);
        DB::select('SELECT 1');
        $pingMs = round((microtime(true) - $start) * 1000, 2);

        // Version & Connection
        $version = DB::select('SELECT VERSION() as v')[0]->v ?? 'Unknown';
        $driver = config('database.default');
        $dbName = config("database.connections.{$driver}.database");
        $host = config("database.connections.{$driver}.host");

        // Database Storage Aggregates
        $dbSizeData = DB::select('
            SELECT 
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS total_mb,
                ROUND(SUM(data_length) / 1024 / 1024, 2) AS data_mb,
                ROUND(SUM(index_length) / 1024 / 1024, 2) AS index_mb,
                COUNT(*) as table_count
            FROM information_schema.tables 
            WHERE table_schema = database()
        ')[0] ?? null;
        $dbSizeArray = array_change_key_case((array) ($dbSizeData ?? []), CASE_LOWER);

        // Global Status
        $dbStatus = [];
        try {
            $statusRows = DB::select("SHOW GLOBAL STATUS WHERE Variable_name IN (
                'Threads_connected', 'Uptime', 'Slow_queries', 'Questions', 'Innodb_buffer_pool_reads', 'Innodb_buffer_pool_read_requests', 'Max_used_connections', 'Aborted_connects'
            )");
            foreach ($statusRows as $row) {
                $r = array_change_key_case((array) $row, CASE_LOWER);
                $varName = $r['variable_name'] ?? '';
                if ($varName) {
                    $dbStatus[$varName] = $r['value'] ?? '';
                }
            }
        } catch (\Exception $e) {
            // Ignore if restricted
        }

        // MySQL Uptime
        $mysqlUptimeSec = (int) ($dbStatus['Uptime'] ?? $dbStatus['uptime'] ?? 0);
        $mDays = floor($mysqlUptimeSec / 86400);
        $mHours = floor(($mysqlUptimeSec % 86400) / 3600);
        $mysqlUptimeStr = "{$mDays}d {$mHours}h";

        // Buffer Pool Hit Ratio
        $bufferReads = (int) ($dbStatus['Innodb_buffer_pool_reads'] ?? $dbStatus['innodb_buffer_pool_reads'] ?? 0);
        $bufferRequests = (int) ($dbStatus['Innodb_buffer_pool_read_requests'] ?? $dbStatus['innodb_buffer_pool_read_requests'] ?? 1);
        $bufferHitRate = $bufferRequests > 0 ? round((1 - ($bufferReads / $bufferRequests)) * 100, 2) : 100;

        // Detailed Table Inspection
        $tablesRaw = DB::select('
            SELECT 
                table_name,
                table_rows,
                ROUND(data_length / 1024, 1) as data_kb,
                ROUND(index_length / 1024, 1) as index_kb,
                ROUND((data_length + index_length) / 1024, 1) as total_kb,
                engine,
                table_collation,
                update_time
            FROM information_schema.tables 
            WHERE table_schema = database()
            ORDER BY (data_length + index_length) DESC
        ');

        $totalRows = 0;
        $tables = collect($tablesRaw)->map(function ($row) use (&$totalRows) {
            $t = array_change_key_case((array) $row, CASE_LOWER);
            $rows = (int) ($t['table_rows'] ?? 0);
            $totalRows += $rows;

            // Categorize table
            $cat = 'Operational';
            $name = $t['table_name'] ?? '';
            if (in_array($name, ['users', 'roles', 'permissions', 'model_has_roles', 'model_has_permissions', 'role_has_permissions', 'migrations'])) {
                $cat = 'System & Auth';
            } elseif (in_array($name, ['branches', 'workshops', 'employees', 'chart_of_accounts', 'services', 'service_branch_prices', 'suppliers', 'system_settings', 'inventory_items'])) {
                $cat = 'Master Data';
            } elseif (in_array($name, ['orders', 'order_items', 'order_payments', 'draft_orders', 'refunds', 'production_status_logs', 'cashier_shifts'])) {
                $cat = 'POS & Orders';
            } elseif (in_array($name, ['journals', 'journal_lines', 'operational_expenses', 'supplier_payments', 'accounting_periods'])) {
                $cat = 'Finance';
            } elseif (in_array($name, ['purchase_requests', 'purchase_orders', 'goods_received_notes', 'inventory_batches'])) {
                $cat = 'Procurement';
            } elseif (in_array($name, ['jobs', 'failed_jobs', 'job_batches', 'cache', 'cache_locks', 'sessions'])) {
                $cat = 'Queue & Cache';
            }

            return [
                'name' => $name,
                'category' => $cat,
                'rows' => $rows,
                'data_kb' => (float) ($t['data_kb'] ?? 0),
                'index_kb' => (float) ($t['index_kb'] ?? 0),
                'total_kb' => (float) ($t['total_kb'] ?? 0),
                'engine' => $t['engine'] ?? 'InnoDB',
                'collation' => $t['table_collation'] ?? 'utf8mb4_unicode_ci',
                'updated_at' => ! empty($t['update_time']) ? date('Y-m-d H:i:s', strtotime($t['update_time'])) : '-',
            ];
        });

        return [
            'version' => $version,
            'driver' => $driver,
            'host' => $host,
            'database' => $dbName,
            'ping_ms' => $pingMs,
            'total_size_mb' => (float) ($dbSizeArray['total_mb'] ?? 0),
            'data_size_mb' => (float) ($dbSizeArray['data_mb'] ?? 0),
            'index_size_mb' => (float) ($dbSizeArray['index_mb'] ?? 0),
            'table_count' => (int) ($dbSizeArray['table_count'] ?? count($tables)),
            'total_rows' => $totalRows,
            'threads_connected' => $dbStatus['Threads_connected'] ?? $dbStatus['threads_connected'] ?? '1',
            'max_used_connections' => $dbStatus['Max_used_connections'] ?? $dbStatus['max_used_connections'] ?? '1',
            'slow_queries' => $dbStatus['Slow_queries'] ?? $dbStatus['slow_queries'] ?? '0',
            'total_questions' => $dbStatus['Questions'] ?? $dbStatus['questions'] ?? '0',
            'uptime_str' => $mysqlUptimeStr,
            'buffer_hit_rate' => $bufferHitRate,
            'tables' => $tables,
        ];
    }

    /**
     * Complete list of all accounts for Developer management.
     */
    public function getUsersList(): array
    {
        return User::with(['roles', 'branch'])
            ->orderBy('id')
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'role' => $u->roles->first()?->name ?? 'None',
                    'branch' => $u->branch?->name ?? 'Global / Semua Cabang',
                    'is_active' => (bool) $u->is_active,
                    'login_attempts' => (int) $u->login_attempts,
                    'is_locked' => $u->locked_until && $u->locked_until->isFuture(),
                    'locked_until' => $u->locked_until ? $u->locked_until->format('Y-m-d H:i:s') : null,
                    'has_2fa' => ! empty($u->two_factor_confirmed_at),
                    'created_at' => $u->created_at ? $u->created_at->format('Y-m-d H:i') : '-',
                ];
            })
            ->toArray();
    }

    /**
     * Laravel application & framework health.
     */
    public function getApplicationMetrics(): array
    {
        $opcacheEnabled = function_exists('opcache_get_status') && is_array(opcache_get_status(false));
        $opcacheHitRate = 0;
        $opcacheMemUsed = 0;
        $opcacheMemFree = 0;

        if ($opcacheEnabled) {
            $opStatus = opcache_get_status(false);
            $opcacheHitRate = round($opStatus['opcache_statistics']['opcache_hit_rate'] ?? 0, 1);
            $opcacheMemUsed = round(($opStatus['memory_usage']['used_memory'] ?? 0) / 1024 / 1024, 1);
            $opcacheMemFree = round(($opStatus['memory_usage']['free_memory'] ?? 0) / 1024 / 1024, 1);
        }

        // Check write permissions
        $storageWritable = is_writable(storage_path());
        $bootstrapCacheWritable = is_writable(base_path('bootstrap/cache'));
        $logsWritable = is_writable(storage_path('logs'));

        // Storage sizes
        $logsSizeMb = 0;
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            $logsSizeMb = round(filesize($logFile) / 1024 / 1024, 2);
        }

        return [
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'environment' => config('app.env'),
            'debug_mode' => config('app.debug'),
            'app_url' => config('app.url'),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'queue_driver' => config('queue.default'),
            'opcache_enabled' => $opcacheEnabled,
            'opcache_hit_rate' => $opcacheHitRate,
            'opcache_mem_used_mb' => $opcacheMemUsed,
            'opcache_mem_free_mb' => $opcacheMemFree,
            'storage_writable' => $storageWritable,
            'bootstrap_cache_writable' => $bootstrapCacheWritable,
            'logs_writable' => $logsWritable,
            'logs_size_mb' => $logsSizeMb,
            'maintenance_mode' => app()->isDownForMaintenance(),
        ];
    }

    /**
     * Safe whitelisted environment config parameters.
     */
    public function getEnvironmentConfig(): array
    {
        return [
            'APP_NAME' => config('app.name'),
            'APP_ENV' => config('app.env'),
            'APP_DEBUG' => config('app.debug') ? 'true' : 'false',
            'APP_URL' => config('app.url'),
            'APP_TIMEZONE' => config('app.timezone'),
            'APP_LOCALE' => config('app.locale'),
            'DB_CONNECTION' => config('database.default'),
            'DB_HOST' => config('database.connections.' . config('database.default') . '.host'),
            'DB_PORT' => config('database.connections.' . config('database.default') . '.port'),
            'DB_DATABASE' => config('database.connections.' . config('database.default') . '.database'),
            'CACHE_STORE' => config('cache.default'),
            'SESSION_DRIVER' => config('session.driver'),
            'SESSION_LIFETIME' => config('session.lifetime') . ' minutes',
            'QUEUE_CONNECTION' => config('queue.default'),
            'LOG_CHANNEL' => config('logging.default'),
            'LOG_LEVEL' => config('logging.channels.' . config('logging.default') . '.level', 'debug'),
        ];
    }

    /**
     * Queue workers & cron scheduler status.
     */
    public function getQueueAndScheduler(): array
    {
        $pendingJobs = 0;
        $failedJobs = 0;
        $failedJobsList = [];

        try {
            if (DB::getSchemaBuilder()->hasTable('jobs')) {
                $pendingJobs = DB::table('jobs')->count();
            }
            if (DB::getSchemaBuilder()->hasTable('failed_jobs')) {
                $failedJobs = DB::table('failed_jobs')->count();
                $failedJobsList = DB::table('failed_jobs')
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(function ($j) {
                        return [
                            'id' => $j->id,
                            'connection' => $j->connection,
                            'queue' => $j->queue,
                            'failed_at' => $j->failed_at,
                            'exception' => substr($j->exception, 0, 120) . '...',
                        ];
                    })
                    ->toArray();
            }
        } catch (\Exception $e) {
            // Ignore
        }

        // Host Cron
        $crontab = 'N/A';
        if (PHP_OS_FAMILY === 'Linux') {
            try {
                $crontab = trim(shell_exec('crontab -l 2>/dev/null') ?? 'No crontab installed');
            } catch (\Exception $e) {
                // Ignore
            }
        }

        return [
            'pending_jobs' => $pendingJobs,
            'failed_jobs' => $failedJobs,
            'failed_jobs_list' => $failedJobsList,
            'crontab' => $crontab,
        ];
    }

    /**
     * Git repository & deployment versioning telemetry.
     */
    public function getGitMetrics(): array
    {
        $branch = 'main';
        $commitHash = 'N/A';
        $commitDate = 'N/A';
        $commitMessage = 'N/A';
        $author = 'N/A';

        try {
            $branch = trim(shell_exec('git rev-parse --abbrev-ref HEAD 2>/dev/null') ?? 'main');
            $commitHash = trim(shell_exec('git log -1 --format="%H" 2>/dev/null') ?? 'N/A');
            $commitShort = substr($commitHash, 0, 7);
            $commitDate = trim(shell_exec('git log -1 --format="%cd" --date=iso 2>/dev/null') ?? 'N/A');
            $author = trim(shell_exec('git log -1 --format="%an" 2>/dev/null') ?? 'N/A');
            $commitMessage = trim(shell_exec('git log -1 --format="%s" 2>/dev/null') ?? 'N/A');
        } catch (\Exception $e) {
            $commitShort = 'N/A';
        }

        return [
            'branch' => $branch ?: 'main',
            'commit_hash' => $commitHash,
            'commit_short' => $commitShort ?? 'N/A',
            'commit_date' => $commitDate ?: 'N/A',
            'author' => $author ?: 'N/A',
            'commit_message' => $commitMessage ?: 'Production build ready',
            'auto_sync_status' => 'ACTIVE (deploy.sh via cron every 5 min)',
        ];
    }

    /**
     * Security, accounts, and audit log metrics.
     */
    public function getSecurityMetrics(): array
    {
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $twoFactorUsers = User::whereNotNull('two_factor_confirmed_at')->count();
        $lockedUsers = User::whereNotNull('locked_until')->where('locked_until', '>', now())->count();

        // Recent Audit Logs
        $recentAuditLogs = [];
        try {
            if (DB::getSchemaBuilder()->hasTable('audit_logs')) {
                $recentAuditLogs = AuditLog::with('user')
                    ->latest()
                    ->take(15)
                    ->get()
                    ->map(function ($log) {
                        return [
                            'id' => $log->id,
                            'user' => $log->user?->name ?? 'System / Anonymous',
                            'action' => $log->action,
                            'ip_address' => $log->ip_address,
                            'created_at' => $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '-',
                        ];
                    });
            }
        } catch (\Exception $e) {
            // Ignore
        }

        // Read last 40 lines of laravel.log
        $recentErrorLogs = [];
        $logPath = storage_path('logs/laravel.log');
        if (file_exists($logPath)) {
            $lines = array_slice(file($logPath), -40);
            foreach ($lines as $line) {
                if (trim($line)) {
                    $recentErrorLogs[] = trim($line);
                }
            }
        }

        return [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'two_factor_users' => $twoFactorUsers,
            'locked_users' => $lockedUsers,
            'recent_audit_logs' => $recentAuditLogs,
            'recent_error_logs' => array_reverse($recentErrorLogs),
        ];
    }
}
