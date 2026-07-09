<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StagingPerformanceDiagnostics
{
    public function handle(Request $request, Closure $next)
    {
        if (!config('staging_performance_diagnostics.enabled')) {
            return $next($request);
        }

        $queries = [];
        $startedAt = microtime(true);
        $response = null;

        DB::listen(function (QueryExecuted $event) use (&$queries) {
            $fingerprint = $this->fingerprintSql($event->sql);

            $queries[] = [
                'fingerprint' => $fingerprint,
                'category' => $this->categorizeSql($fingerprint),
                'time_ms' => (float)$event->time,
                'source' => $this->sourceFromTrace(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 30)),
            ];
        });

        try {
            $response = $next($request);

            return $response;
        } finally {
            $this->logSummary($request, $response, $queries, $startedAt);
        }
    }

    protected function logSummary(Request $request, $response, array $queries, float $startedAt): void
    {
        $patterns = [];
        $totalQueryMs = 0.0;
        $slowQueryMs = (float)config('staging_performance_diagnostics.slow_query_ms', 100);

        foreach ($queries as $query) {
            $totalQueryMs += $query['time_ms'];
            $key = $query['fingerprint'];

            if (!isset($patterns[$key])) {
                $patterns[$key] = [
                    'fingerprint' => $key,
                    'category' => $query['category'],
                    'count' => 0,
                    'total_ms' => 0.0,
                    'max_ms' => 0.0,
                    'slow_count' => 0,
                    'sources' => [],
                ];
            }

            $patterns[$key]['count']++;
            $patterns[$key]['total_ms'] += $query['time_ms'];
            $patterns[$key]['max_ms'] = max($patterns[$key]['max_ms'], $query['time_ms']);

            if ($query['time_ms'] >= $slowQueryMs) {
                $patterns[$key]['slow_count']++;
            }

            $patterns[$key]['sources'][$query['source']] = ($patterns[$key]['sources'][$query['source']] ?? 0) + 1;
        }

        $topPatterns = array_values($patterns);

        usort($topPatterns, function (array $left, array $right) {
            return $right['total_ms'] <=> $left['total_ms'];
        });

        $topPatterns = array_slice($topPatterns, 0, (int)config('staging_performance_diagnostics.max_patterns', 12));

        foreach ($topPatterns as &$pattern) {
            arsort($pattern['sources']);
            $pattern['avg_ms'] = $pattern['count'] > 0
                ? round($pattern['total_ms'] / $pattern['count'], 2)
                : 0.0;
            $pattern['total_ms'] = round($pattern['total_ms'], 2);
            $pattern['max_ms'] = round($pattern['max_ms'], 2);
            $pattern['sources'] = array_slice($pattern['sources'], 0, 3, true);
        }
        unset($pattern);

        Log::channel(config('staging_performance_diagnostics.log_channel', config('logging.default')))->info(
            'staging_perf_diagnostics',
            [
                'method' => $request->method(),
                'path' => '/'.ltrim($request->path(), '/'),
                'route_name' => optional($request->route())->getName(),
                'status' => $response?->getStatusCode(),
                'duration_ms' => round((microtime(true) - $startedAt) * 1000, 2),
                'query_count' => count($queries),
                'query_total_ms' => round($totalQueryMs, 2),
                'query_max_ms' => round(empty($queries) ? 0.0 : max(array_column($queries, 'time_ms')), 2),
                'top_query_patterns' => $topPatterns,
            ]
        );
    }

    protected function fingerprintSql(string $sql): string
    {
        $sql = preg_replace("/'([^'\\\\]|\\\\.)*'/", "'?'", $sql);
        $sql = preg_replace('/"([^"\\\\]|\\\\.)*"/', '"?"', $sql);
        $sql = preg_replace('/\b[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\b/i', '?', $sql);
        $sql = preg_replace('/\b\d+(?:\.\d+)?\b/', '?', $sql);
        $sql = preg_replace('/\s+/', ' ', trim($sql));

        return mb_strtolower(mb_substr($sql, 0, 500));
    }

    protected function categorizeSql(string $fingerprint): string
    {
        return match (true) {
            str_contains($fingerprint, 'information_schema'),
            str_contains($fingerprint, 'show full tables'),
            str_contains($fingerprint, 'show columns') => 'schema',
            str_contains($fingerprint, 'setting') => 'settings',
            str_contains($fingerprint, 'extension') => 'extensions',
            str_contains($fingerprint, 'theme') => 'theme',
            str_contains($fingerprint, 'page') => 'pages',
            str_contains($fingerprint, 'menu'),
            str_contains($fingerprint, 'category') => 'menus',
            str_contains($fingerprint, 'cart') => 'cart',
            str_contains($fingerprint, 'reservation') => 'reservation',
            str_contains($fingerprint, 'user'),
            str_contains($fingerprint, 'staff') => 'users',
            default => 'other',
        };
    }

    protected function sourceFromTrace(array $trace): string
    {
        $basePath = str_replace('\\', '/', base_path()).'/';
        $skipPrefixes = [
            'vendor/laravel/framework/',
            'vendor/composer/',
            'app/Http/Middleware/StagingPerformanceDiagnostics.php',
        ];

        foreach ($trace as $frame) {
            if (empty($frame['file'])) {
                continue;
            }

            $file = str_replace('\\', '/', $frame['file']);
            $relative = str_starts_with($file, $basePath)
                ? substr($file, strlen($basePath))
                : $file;

            foreach ($skipPrefixes as $prefix) {
                if (str_starts_with($relative, $prefix)) {
                    continue 2;
                }
            }

            if (
                str_starts_with($relative, 'app/')
                || str_starts_with($relative, 'extensions/')
                || str_starts_with($relative, 'themes/')
                || str_starts_with($relative, 'vendor/tastyigniter/')
            ) {
                return $relative.':'.($frame['line'] ?? 0);
            }
        }

        return 'unknown';
    }
}
