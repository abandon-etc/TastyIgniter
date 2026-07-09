<?php

$isProduction = strtolower((string)env('APP_ENV', 'production')) === 'production';
$isEnabled = filter_var(env('ENABLE_STAGING_PERF_DIAGNOSTICS', false), FILTER_VALIDATE_BOOLEAN);

return [
    'enabled' => !$isProduction && $isEnabled,

    'log_channel' => env('STAGING_PERF_DIAGNOSTICS_LOG_CHANNEL', env('LOG_CHANNEL', 'stack')),

    'slow_query_ms' => (float)env('STAGING_PERF_DIAGNOSTICS_SLOW_QUERY_MS', 100),

    'max_patterns' => (int)env('STAGING_PERF_DIAGNOSTICS_MAX_PATTERNS', 12),
];
