<?php

return [
    'enabled' => (bool)env('ENABLE_STAGING_PERF_DIAGNOSTICS', false),

    'log_channel' => env('STAGING_PERF_DIAGNOSTICS_LOG_CHANNEL', env('LOG_CHANNEL', 'stack')),

    'slow_query_ms' => (float)env('STAGING_PERF_DIAGNOSTICS_SLOW_QUERY_MS', 100),

    'max_patterns' => (int)env('STAGING_PERF_DIAGNOSTICS_MAX_PATTERNS', 12),
];
